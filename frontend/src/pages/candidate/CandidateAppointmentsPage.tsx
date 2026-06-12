import { useMemo, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  CalendarCheck,
  CalendarDays,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Clock,
  Mail,
  Sparkles,
  Video,
} from 'lucide-react'
import {
  bookAppointment,
  fetchAvailableAppointments,
  fetchMyAppointments,
  type AppointmentSlot,
} from '@/lib/api'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

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

function slotLocalDateKey(iso: string): string {
  const d = new Date(iso)
  return toLocalDateKey(d)
}

function isSameDay(a: Date, b: Date): boolean {
  return toLocalDateKey(a) === toLocalDateKey(b)
}

function formatTime(iso: string): string {
  return new Date(iso).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })
}

function formatDateTime(iso: string): string {
  return new Date(iso).toLocaleString('fr-FR', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatShortDate(iso: string): string {
  return new Date(iso).toLocaleDateString('fr-FR', {
    weekday: 'short',
    day: 'numeric',
    month: 'short',
  })
}

function counselorInitials(firstName: string, lastName: string): string {
  return `${firstName[0] ?? ''}${lastName[0] ?? ''}`.toUpperCase()
}

export function CandidateAppointmentsPage() {
  const queryClient = useQueryClient()
  const today = useMemo(() => {
    const d = new Date()
    d.setHours(0, 0, 0, 0)
    return d
  }, [])

  const [weekStart, setWeekStart] = useState(() => startOfWeek(new Date()))
  const [selectedSlotId, setSelectedSlotId] = useState<string | null>(null)
  const [subject, setSubject] = useState('Entretien de suivi')
  const [successMessage, setSuccessMessage] = useState<string | null>(null)

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

  const availableQuery = useQuery({
    queryKey: ['appointments-available', toLocalDateKey(weekStart), toLocalDateKey(weekEnd)],
    queryFn: () => fetchAvailableAppointments(toLocalDateKey(weekStart), toLocalDateKey(weekEnd)),
  })

  const mineQuery = useQuery({
    queryKey: ['appointments-mine'],
    queryFn: fetchMyAppointments,
  })

  const bookMutation = useMutation({
    mutationFn: () => bookAppointment(selectedSlotId!, subject),
    onSuccess: (slot) => {
      setSuccessMessage(`Rendez-vous confirmé le ${formatDateTime(slot.startsAt)}. Votre conseillère a été notifiée par e-mail.`)
      setSelectedSlotId(null)
      void queryClient.invalidateQueries({ queryKey: ['appointments-available'] })
      void queryClient.invalidateQueries({ queryKey: ['appointments-mine'] })
    },
  })

  const slotsByDay = useMemo(() => {
    const map = new Map<string, AppointmentSlot[]>()
    for (const day of weekDays) {
      map.set(toLocalDateKey(day), [])
    }
    for (const slot of availableQuery.data?.slots ?? []) {
      const key = slotLocalDateKey(slot.startsAt)
      if (map.has(key)) {
        map.get(key)!.push(slot)
      }
    }
    for (const [, slots] of map) {
      slots.sort((a, b) => a.startsAt.localeCompare(b.startsAt))
    }
    return map
  }, [availableQuery.data?.slots, weekDays])

  const totalSlotsThisWeek = useMemo(
    () => [...slotsByDay.values()].reduce((acc, slots) => acc + slots.length, 0),
    [slotsByDay],
  )

  const counselor = availableQuery.data?.counselor
  const calendarEnabled = availableQuery.data?.calendarEnabled !== false
  const selectedSlot = availableQuery.data?.slots.find((s) => s.id === selectedSlotId)

  return (
    <div className="mx-auto max-w-[1280px] space-y-6">
      {/* En-tête hero */}
      <div className="overflow-hidden rounded-2xl bg-gradient-to-br from-[#1a2744] via-[#243b5e] to-[#1e3a5f] p-6 text-white shadow-lg sm:p-8">
        <div className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <div className="mb-2 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-blue-100 ring-1 ring-white/10">
              <CalendarDays className="h-3.5 w-3.5" />
              Prise de rendez-vous en ligne
            </div>
            <h1 className="text-2xl font-bold tracking-tight sm:text-3xl">Rendez-vous</h1>
            <p className="mt-2 max-w-xl text-sm leading-relaxed text-blue-100/90">
              Réservez un créneau avec votre conseillère pour faire le point sur votre dossier,
              préparer un entretien ou avancer sur votre procédure.
            </p>
          </div>

          {counselor && (
            <div className="flex min-w-[280px] items-center gap-4 rounded-xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur-sm">
              <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-400 to-pink-600 text-lg font-bold shadow-md">
                {counselorInitials(counselor.firstName, counselor.lastName)}
              </div>
              <div className="min-w-0">
                <p className="text-[11px] font-medium uppercase tracking-wider text-blue-200">
                  Conseillère dédiée
                </p>
                <p className="truncate text-lg font-semibold">
                  {counselor.firstName} {counselor.lastName}
                </p>
                <p className="mt-0.5 flex items-center gap-1 truncate text-xs text-blue-100/80">
                  <Mail className="h-3 w-3 shrink-0" />
                  {counselor.email}
                </p>
              </div>
            </div>
          )}
        </div>
      </div>

      {successMessage && (
        <div className="flex items-start gap-3 rounded-2xl border border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-4 shadow-sm">
          <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />
          <p className="text-sm leading-relaxed text-emerald-900">{successMessage}</p>
        </div>
      )}

      {counselor && !calendarEnabled && (
        <div className="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
          La prise de rendez-vous est temporairement fermée par votre conseillère. Réessayez plus tard ou
          contactez-la par e-mail.
        </div>
      )}

      <div className="grid gap-6 xl:grid-cols-[1fr_380px]">
        {/* Calendrier */}
        <section className="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
          <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 px-5 py-4 sm:px-6">
            <div>
              <h2 className="text-base font-semibold text-slate-900">Disponibilités</h2>
              <p className="mt-0.5 text-xs text-slate-500">
                {totalSlotsThisWeek} créneau{totalSlotsThisWeek > 1 ? 'x' : ''} cette semaine
              </p>
            </div>
            <div className="flex items-center gap-1 rounded-xl bg-slate-100 p-1">
              <Button
                type="button"
                size="sm"
                variant="ghost"
                className="h-8 w-8 p-0 hover:bg-white"
                onClick={() => setWeekStart((d) => addDays(d, -7))}
                aria-label="Semaine précédente"
              >
                <ChevronLeft className="h-4 w-4" />
              </Button>
              <button
                type="button"
                onClick={() => setWeekStart(startOfWeek(new Date()))}
                className="rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 transition-colors hover:bg-white"
              >
                Aujourd&apos;hui
              </button>
              <span className="hidden min-w-[140px] text-center text-xs font-medium text-slate-600 sm:inline">
                {weekLabel}
              </span>
              <Button
                type="button"
                size="sm"
                variant="ghost"
                className="h-8 w-8 p-0 hover:bg-white"
                onClick={() => setWeekStart((d) => addDays(d, 7))}
                aria-label="Semaine suivante"
              >
                <ChevronRight className="h-4 w-4" />
              </Button>
            </div>
          </div>

          {availableQuery.isLoading ? (
            <div className="flex min-h-[320px] items-center justify-center p-8">
              <div className="flex flex-col items-center gap-3 text-slate-400">
                <div className="h-8 w-8 animate-spin rounded-full border-2 border-slate-200 border-t-blue-600" />
                <p className="text-sm">Chargement du calendrier...</p>
              </div>
            </div>
          ) : (
            <div className="overflow-x-auto p-4 sm:p-5">
              <div className="grid min-w-[720px] grid-cols-7 gap-2">
                {weekDays.map((day, index) => {
                  const key = toLocalDateKey(day)
                  const daySlots = slotsByDay.get(key) ?? []
                  const isToday = isSameDay(day, today)
                  const isWeekend = index >= 5
                  const hasSlots = daySlots.length > 0

                  return (
                    <div
                      key={key}
                      className={`flex min-h-[200px] flex-col rounded-xl border transition-shadow ${
                        isToday
                          ? 'border-blue-200 bg-blue-50/40 ring-2 ring-blue-100'
                          : isWeekend
                            ? 'border-slate-100 bg-slate-50/60'
                            : 'border-slate-100 bg-white hover:shadow-sm'
                      }`}
                    >
                      <div
                        className={`border-b px-2 py-2.5 text-center ${
                          isToday ? 'border-blue-100' : 'border-slate-100'
                        }`}
                      >
                        <p className="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                          {DAY_SHORT[index]}
                        </p>
                        <p
                          className={`mt-0.5 text-xl font-bold tabular-nums ${
                            isToday ? 'text-blue-700' : 'text-slate-800'
                          }`}
                        >
                          {day.getDate()}
                        </p>
                        <p className="text-[10px] capitalize text-slate-400">
                          {day.toLocaleDateString('fr-FR', { month: 'short' })}
                        </p>
                      </div>

                      <div className="flex flex-1 flex-col gap-1.5 p-2">
                        {!hasSlots ? (
                          <div className="flex flex-1 flex-col items-center justify-center py-4 text-center">
                            <div className="mb-1.5 rounded-full bg-slate-100 p-2">
                              <Clock className="h-3.5 w-3.5 text-slate-300" />
                            </div>
                            <p className="text-[10px] leading-snug text-slate-400">
                              {isWeekend ? 'Fermé' : 'Complet'}
                            </p>
                          </div>
                        ) : (
                          daySlots.map((slot) => {
                            const selected = selectedSlotId === slot.id
                            return (
                              <button
                                key={slot.id}
                                type="button"
                                onClick={() => {
                                  setSelectedSlotId(slot.id)
                                  setSuccessMessage(null)
                                }}
                                className={`group flex w-full items-center justify-center gap-1 rounded-lg px-2 py-2 text-xs font-semibold transition-all ${
                                  selected
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-200 ring-2 ring-blue-300'
                                    : 'bg-white text-blue-700 ring-1 ring-blue-100 hover:bg-blue-600 hover:text-white hover:shadow-md hover:ring-blue-600'
                                }`}
                              >
                                <Clock className="h-3 w-3 opacity-70" />
                                {formatTime(slot.startsAt)}
                              </button>
                            )
                          })
                        )}
                      </div>
                    </div>
                  )
                })}
              </div>
            </div>
          )}

          <div className="border-t border-slate-100 px-5 py-3 sm:px-6">
            <p className="text-center text-[11px] text-slate-400">
              Créneaux de 30 minutes · Fuseau horaire : {Intl.DateTimeFormat().resolvedOptions().timeZone}
            </p>
          </div>
        </section>

        {/* Panneau latéral */}
        <aside className="space-y-5">
          {/* Réservation */}
          <section className="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div className="mb-4 flex items-center gap-2">
              <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                <Sparkles className="h-4 w-4" />
              </div>
              <div>
                <h2 className="text-sm font-semibold text-slate-900">Réserver un créneau</h2>
                <p className="text-[11px] text-slate-500">Sélectionnez une heure dans le calendrier</p>
              </div>
            </div>

            {selectedSlot ? (
              <div className="space-y-4">
                <div className="rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 p-4 ring-1 ring-blue-100">
                  <p className="text-[11px] font-semibold uppercase tracking-wide text-blue-600">
                    Créneau sélectionné
                  </p>
                  <p className="mt-1 text-sm font-semibold capitalize text-slate-900">
                    {formatDateTime(selectedSlot.startsAt)}
                  </p>
                  <p className="mt-2 flex items-center gap-1.5 text-xs text-slate-600">
                    <Video className="h-3.5 w-3.5 text-blue-500" />
                    Visioconférence ou appel — lien envoyé par e-mail
                  </p>
                </div>

                <div>
                  <label htmlFor="subject" className="mb-1.5 block text-xs font-medium text-slate-700">
                    Objet du rendez-vous
                  </label>
                  <Input
                    id="subject"
                    value={subject}
                    onChange={(e) => setSubject(e.target.value)}
                    placeholder="Ex. Suivi dossier visa"
                    className="border-slate-200 bg-slate-50/50 focus:bg-white"
                  />
                </div>

                <div className="flex flex-col gap-2">
                  <Button
                    type="button"
                    className="w-full bg-blue-600 shadow-sm hover:bg-blue-700"
                    disabled={bookMutation.isPending}
                    onClick={() => bookMutation.mutate()}
                  >
                    {bookMutation.isPending ? 'Réservation en cours...' : 'Confirmer le rendez-vous'}
                  </Button>
                  <Button
                    type="button"
                    variant="ghost"
                    className="w-full text-slate-500"
                    onClick={() => setSelectedSlotId(null)}
                  >
                    Changer de créneau
                  </Button>
                </div>

                {bookMutation.isError && (
                  <p className="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700 ring-1 ring-red-100">
                    Ce créneau n&apos;est plus disponible. Veuillez en choisir un autre.
                  </p>
                )}
              </div>
            ) : (
              <div className="flex flex-col items-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-4 py-8 text-center">
                <CalendarCheck className="mb-3 h-10 w-10 text-slate-300" />
                <p className="text-sm font-medium text-slate-600">Aucun créneau sélectionné</p>
                <p className="mt-1 text-xs leading-relaxed text-slate-400">
                  Cliquez sur un horaire disponible dans le calendrier pour continuer.
                </p>
              </div>
            )}
          </section>

          {/* RDV confirmés */}
          <section className="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div className="mb-4 flex items-center justify-between">
              <h2 className="text-sm font-semibold text-slate-900">Mes rendez-vous</h2>
              {(mineQuery.data?.length ?? 0) > 0 && (
                <span className="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 ring-1 ring-blue-100">
                  {mineQuery.data!.length}
                </span>
              )}
            </div>

            {mineQuery.isLoading ? (
              <p className="text-xs text-slate-400">Chargement...</p>
            ) : (mineQuery.data?.length ?? 0) === 0 ? (
              <p className="text-xs leading-relaxed text-slate-400">
                Vous n&apos;avez pas encore de rendez-vous planifié.
              </p>
            ) : (
              <ul className="space-y-2.5">
                {mineQuery.data?.map((apt) => (
                  <li
                    key={apt.id}
                    className="relative overflow-hidden rounded-xl bg-gradient-to-r from-slate-50 to-white p-3.5 ring-1 ring-slate-100"
                  >
                    <div className="absolute left-0 top-0 h-full w-1 bg-emerald-500" />
                    <p className="pl-2 text-xs font-semibold capitalize text-slate-900">
                      {formatShortDate(apt.startsAt)}
                    </p>
                    <p className="pl-2 text-[11px] text-slate-500">
                      {formatTime(apt.startsAt)} · {apt.subject ?? 'Entretien de suivi'}
                    </p>
                    <span className="absolute right-3 top-3 rounded-full bg-emerald-50 px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-emerald-700">
                      Confirmé
                    </span>
                  </li>
                ))}
              </ul>
            )}
          </section>
        </aside>
      </div>
    </div>
  )
}
