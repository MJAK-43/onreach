import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  CalendarClock,
  ChevronLeft,
  ChevronRight,
  Loader2,
  Power,
  User,
} from 'lucide-react'
import {
  closeAppointmentSlots,
  createAppointmentSlot,
  createAppointmentSlots,
  deleteAppointmentSlot,
  fetchCalendarSettings,
  fetchCounselorSchedule,
  updateCalendarSettings,
  type AppointmentSlot,
} from '@/lib/api'
import { AFTERNOON_TIMES, ALL_DAY_TIMES, MORNING_TIMES } from '@/lib/appointment-times'
import { Button } from '@/components/ui/button'

const DAY_SHORT = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']

function startOfWeek(date: Date): Date {
  const d = new Date(date)
  const day = d.getDay()
  const diff = day === 0 ? -6 : 1 - day
  d.setDate(d.getDate() + diff)
  d.setHours(0, 0, 0, 0)
  return d
}

function addDays(date: Date, days: number): Date {
  const d = new Date(date)
  d.setDate(d.getDate() + days)
  return d
}

function toLocalDateKey(date: Date): string {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

function slotTimeKey(iso: string): string {
  const d = new Date(iso)
  return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`
}

function isSameDay(a: Date, b: Date): boolean {
  return toLocalDateKey(a) === toLocalDateKey(b)
}

function isPastDay(date: Date): boolean {
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return date < today
}

function isPastSlot(date: Date, time: string): boolean {
  const [h, m] = time.split(':').map(Number)
  const slotDate = new Date(date)
  slotDate.setHours(h, m, 0, 0)
  return slotDate <= new Date()
}

type SlotCell = AppointmentSlot | null

export function CounselorAvailabilityPage() {
  const queryClient = useQueryClient()
  const today = useMemo(() => {
    const d = new Date()
    d.setHours(0, 0, 0, 0)
    return d
  }, [])

  const [weekStart, setWeekStart] = useState(() => startOfWeek(new Date()))
  const [pendingKey, setPendingKey] = useState<string | null>(null)
  const [feedback, setFeedback] = useState<string | null>(null)

  const weekEnd = useMemo(() => addDays(weekStart, 7), [weekStart])
  const weekDays = useMemo(
    () => Array.from({ length: 7 }, (_, i) => addDays(weekStart, i)),
    [weekStart],
  )

  const weekLabel = useMemo(() => {
    const end = addDays(weekStart, 6)
    const startFmt = weekStart.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
    const endFmt = end.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
    return `${startFmt} — ${endFmt}`
  }, [weekStart])

  const calendarQuery = useQuery({
    queryKey: ['calendar-settings'],
    queryFn: fetchCalendarSettings,
  })

  const scheduleQuery = useQuery({
    queryKey: ['counselor-schedule', toLocalDateKey(weekStart), toLocalDateKey(weekEnd)],
    queryFn: () => fetchCounselorSchedule(toLocalDateKey(weekStart), toLocalDateKey(weekEnd)),
  })

  const slotMap = useMemo(() => {
    const map = new Map<string, AppointmentSlot>()
    for (const slot of scheduleQuery.data ?? []) {
      const dateKey = toLocalDateKey(new Date(slot.startsAt))
      map.set(`${dateKey}-${slotTimeKey(slot.startsAt)}`, slot)
    }
    return map
  }, [scheduleQuery.data])

  const invalidate = () => {
    void queryClient.invalidateQueries({ queryKey: ['counselor-schedule'] })
    void queryClient.invalidateQueries({ queryKey: ['appointments-available'] })
  }

  const calendarToggleMutation = useMutation({
    mutationFn: (enabled: boolean) => updateCalendarSettings(enabled),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['calendar-settings'] })
      void queryClient.invalidateQueries({ queryKey: ['appointments-available'] })
      void queryClient.invalidateQueries({ queryKey: ['current-user'] })
      setFeedback(null)
    },
    onError: (error: Error) => setFeedback(error.message),
  })

  const toggleMutation = useMutation({
    mutationFn: async ({ dateKey, time, slot }: { dateKey: string; time: string; slot: SlotCell }) => {
      if (slot?.status === 'booked') {
        throw new Error('Ce créneau est réservé par un candidat.')
      }
      if (slot?.status === 'available') {
        await deleteAppointmentSlot(slot.id)
        return
      }
      await createAppointmentSlot(dateKey, time)
    },
    onMutate: ({ dateKey, time }) => setPendingKey(`${dateKey}-${time}`),
    onSuccess: () => {
      setFeedback(null)
      invalidate()
    },
    onError: (error: Error) => setFeedback(error.message),
    onSettled: () => setPendingKey(null),
  })

  const periodMutation = useMutation({
    mutationFn: async ({ dateKey, date, times }: { dateKey: string; date: Date; times: string[] }) => {
      const actionable = times.filter((t) => !isPastSlot(date, t))
      const nonBooked = actionable.filter((t) => slotMap.get(`${dateKey}-${t}`)?.status !== 'booked')
      const allOpen =
        nonBooked.length > 0 &&
        nonBooked.every((t) => slotMap.get(`${dateKey}-${t}`)?.status === 'available')

      if (allOpen) {
        await closeAppointmentSlots(dateKey, nonBooked)
      } else {
        const toOpen = nonBooked.filter((t) => !slotMap.has(`${dateKey}-${t}`))
        if (toOpen.length > 0) {
          await createAppointmentSlots(dateKey, toOpen)
        }
      }
    },
    onSuccess: () => {
      setFeedback(null)
      invalidate()
    },
    onError: (error: Error) => setFeedback(error.message),
  })

  const stats = useMemo(() => {
    let available = 0
    let booked = 0
    for (const slot of scheduleQuery.data ?? []) {
      if (slot.status === 'available') available++
      if (slot.status === 'booked') booked++
    }
    return { available, booked }
  }, [scheduleQuery.data])

  const calendarEnabled = calendarQuery.data?.enabled ?? true

  function getCell(date: Date, time: string): SlotCell {
    return slotMap.get(`${toLocalDateKey(date)}-${time}`) ?? null
  }

  function isPeriodFullyOpen(dateKey: string, times: string[], date: Date): boolean {
    const nonBooked = times.filter(
      (t) => !isPastSlot(date, t) && slotMap.get(`${dateKey}-${t}`)?.status !== 'booked',
    )
    return nonBooked.length > 0 && nonBooked.every((t) => slotMap.get(`${dateKey}-${t}`)?.status === 'available')
  }

  function renderSlotButton(date: Date, time: string) {
    const dateKey = toLocalDateKey(date)
    const cellKey = `${dateKey}-${time}`
    const slot = getCell(date, time)
    const disabled = isPastDay(date) || isPastSlot(date, time) || slot?.status === 'booked'
    const isPending = pendingKey === cellKey
    const isAvailable = slot?.status === 'available'
    const isBooked = slot?.status === 'booked'

    return (
      <button
        key={cellKey}
        type="button"
        disabled={disabled || toggleMutation.isPending || !calendarEnabled}
        title={
          isBooked
            ? `${slot?.candidate?.firstName ?? ''} ${slot?.candidate?.lastName ?? ''} — ${slot?.subject ?? 'RDV'}`
            : isAvailable
              ? 'Cliquer pour fermer ce créneau'
              : 'Cliquer pour ouvrir ce créneau'
        }
        onClick={() => {
          if (disabled || !calendarEnabled) return
          toggleMutation.mutate({ dateKey, time, slot })
        }}
        className={`relative flex w-full items-center justify-center gap-0.5 rounded-md px-0.5 py-1 text-[10px] font-semibold transition-all ${
          isBooked
            ? 'cursor-default bg-blue-600 text-white'
            : isAvailable
              ? 'bg-emerald-500 text-white hover:bg-emerald-600'
              : disabled
                ? 'cursor-not-allowed bg-slate-100 text-slate-300'
                : 'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-emerald-50 hover:text-emerald-700'
        }`}
      >
        {isPending ? <Loader2 className="h-3 w-3 animate-spin" /> : time}
        {isBooked && slot?.candidate && (
          <User className="absolute -right-0.5 -top-0.5 h-2.5 w-2.5 rounded-full bg-white p-0.5 text-blue-600" />
        )}
      </button>
    )
  }

  return (
    <div className="mx-auto max-w-[1600px] space-y-6">
      <div className="overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-800 via-teal-800 to-cyan-900 p-6 text-white shadow-lg sm:p-8">
        <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <div className="mb-2 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-emerald-100 ring-1 ring-white/10">
              <CalendarClock className="h-3.5 w-3.5" />
              Gestion des disponibilités
            </div>
            <h1 className="text-2xl font-bold tracking-tight sm:text-3xl">Mon calendrier</h1>
            <p className="mt-2 max-w-2xl text-sm leading-relaxed text-emerald-100/90">
              Horaires de 00h à 23h30 (créneaux de 30 min). Cliquez sur un horaire ou utilisez Matin /
              Après-midi pour tout ouvrir ou fermer. Les créneaux réservés ne sont jamais modifiés.
            </p>
          </div>
          <div className="flex flex-wrap items-start gap-3">
            <Button
              type="button"
              variant={calendarEnabled ? 'default' : 'outline'}
              className={
                calendarEnabled
                  ? 'bg-white text-emerald-900 hover:bg-emerald-50'
                  : 'border-white/40 bg-transparent text-white hover:bg-white/10'
              }
              disabled={calendarToggleMutation.isPending}
              onClick={() => calendarToggleMutation.mutate(!calendarEnabled)}
            >
              <Power className="mr-2 h-4 w-4" />
              {calendarToggleMutation.isPending
                ? 'Mise à jour...'
                : calendarEnabled
                  ? 'Calendrier actif'
                  : 'Calendrier désactivé'}
            </Button>
            <div className="rounded-xl bg-white/10 px-4 py-3 text-center ring-1 ring-white/15">
              <p className="text-2xl font-bold">{stats.available}</p>
              <p className="text-[10px] uppercase tracking-wide text-emerald-100">Disponibles</p>
            </div>
            <div className="rounded-xl bg-white/10 px-4 py-3 text-center ring-1 ring-white/15">
              <p className="text-2xl font-bold">{stats.booked}</p>
              <p className="text-[10px] uppercase tracking-wide text-emerald-100">Réservés</p>
            </div>
          </div>
        </div>
      </div>

      {!calendarEnabled && (
        <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
          Votre calendrier est désactivé : les candidats ne peuvent pas prendre de rendez-vous tant que vous ne
          le réactivez pas.
        </div>
      )}

      {feedback && (
        <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
          {feedback}
        </div>
      )}

      <section className="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
          <div>
            <h2 className="text-base font-semibold text-slate-900">Semaine en cours</h2>
            <p className="mt-0.5 text-xs text-slate-500">{weekLabel} · 00h — 23h30</p>
          </div>
          <div className="flex items-center gap-1 rounded-xl bg-slate-100 p-1">
            <Button type="button" size="sm" variant="ghost" className="h-8 w-8 p-0 hover:bg-white" onClick={() => setWeekStart((d) => addDays(d, -7))}>
              <ChevronLeft className="h-4 w-4" />
            </Button>
            <button type="button" onClick={() => setWeekStart(startOfWeek(new Date()))} className="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-white">
              Aujourd&apos;hui
            </button>
            <Button type="button" size="sm" variant="ghost" className="h-8 w-8 p-0 hover:bg-white" onClick={() => setWeekStart((d) => addDays(d, 7))}>
              <ChevronRight className="h-4 w-4" />
            </Button>
          </div>
        </div>

        <div className="flex flex-wrap gap-4 border-b border-slate-100 px-5 py-3 text-xs text-slate-500 sm:px-6">
          <span className="flex items-center gap-2"><span className="h-3 w-3 rounded bg-emerald-500" /> Disponible</span>
          <span className="flex items-center gap-2"><span className="h-3 w-3 rounded bg-blue-600" /> Réservé</span>
          <span className="flex items-center gap-2"><span className="h-3 w-3 rounded ring-1 ring-slate-300 bg-white" /> Fermé</span>
        </div>

        {scheduleQuery.isLoading ? (
          <div className="flex min-h-[360px] items-center justify-center">
            <Loader2 className="h-8 w-8 animate-spin text-emerald-600" />
          </div>
        ) : (
          <div className="overflow-x-auto p-4 sm:p-5">
            <div className="grid min-w-[1100px] grid-cols-7 gap-2">
              {weekDays.map((day, index) => {
                const dateKey = toLocalDateKey(day)
                const isToday = isSameDay(day, today)
                const past = isPastDay(day)
                const morningOpen = isPeriodFullyOpen(dateKey, MORNING_TIMES, day)
                const afternoonOpen = isPeriodFullyOpen(dateKey, AFTERNOON_TIMES, day)

                return (
                  <div
                    key={dateKey}
                    className={`flex flex-col rounded-xl border ${
                      isToday
                        ? 'border-emerald-200 bg-emerald-50/30 ring-2 ring-emerald-100'
                        : past
                          ? 'border-slate-100 bg-slate-50/80 opacity-70'
                          : 'border-slate-100 bg-white'
                    }`}
                  >
                    <div className="shrink-0 border-b border-slate-100 px-2 py-2 text-center">
                      <p className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">{DAY_SHORT[index]}</p>
                      <p className={`text-lg font-bold tabular-nums ${isToday ? 'text-emerald-700' : 'text-slate-800'}`}>{day.getDate()}</p>
                      {!past && (
                        <div className="mt-2 flex flex-wrap justify-center gap-1">
                          <button
                            type="button"
                            disabled={periodMutation.isPending || !calendarEnabled}
                            onClick={() => periodMutation.mutate({ dateKey, date: day, times: MORNING_TIMES })}
                            className={`rounded-md px-1.5 py-0.5 text-[9px] font-medium ${
                              morningOpen
                                ? 'bg-emerald-500 text-white'
                                : 'bg-slate-100 text-slate-600 hover:bg-emerald-100 hover:text-emerald-800'
                            }`}
                          >
                            Matin
                          </button>
                          <button
                            type="button"
                            disabled={periodMutation.isPending || !calendarEnabled}
                            onClick={() => periodMutation.mutate({ dateKey, date: day, times: AFTERNOON_TIMES })}
                            className={`rounded-md px-1.5 py-0.5 text-[9px] font-medium ${
                              afternoonOpen
                                ? 'bg-emerald-500 text-white'
                                : 'bg-slate-100 text-slate-600 hover:bg-emerald-100 hover:text-emerald-800'
                            }`}
                          >
                            Après-midi
                          </button>
                        </div>
                      )}
                    </div>

                    <div className="max-h-[420px] space-y-0.5 overflow-y-auto p-1.5">
                      {ALL_DAY_TIMES.map((time) => renderSlotButton(day, time))}
                    </div>
                  </div>
                )
              })}
            </div>
          </div>
        )}
      </section>
    </div>
  )
}
