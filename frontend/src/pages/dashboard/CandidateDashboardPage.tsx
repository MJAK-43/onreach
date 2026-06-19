import type { ReactNode } from 'react'
import {
  Calendar,
  ClipboardList,
  Download,
  GraduationCap,
  Mail,
  Wallet,
} from 'lucide-react'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { useMyDashboard } from '@/hooks/useMyDashboard'
import { CandidateProcedureTimeline } from '@/components/candidate/CandidateProcedureTimeline'
import { STATUS_LABELS } from '@/lib/candidate-utils'
import { Button } from '@/components/ui/button'

const DOCUMENT_LABELS: Record<string, string> = {
  passport: 'Passeport',
  cv: 'CV',
  motivation_letter: 'Lettre de motivation',
  transcript: 'Relevés de notes',
  language_certificate: 'Attestation de langue',
  diploma: 'Diplôme',
}

const DEMO_DOCUMENTS = [
  'Passeport',
  'Relevés de notes',
  'Attestation de langue',
  'Lettre de motivation',
]

const DEMO_APPOINTMENTS = [
  { day: '24', month: 'MAI', time: '10:00', title: 'Atelier : Préparation entretien', tag: 'Atelier', color: 'bg-blue-50 text-blue-700 ring-1 ring-blue-100' },
  { day: '30', month: 'MAI', time: '14:30', title: 'Entretien individuel', tag: 'Individuel', color: 'bg-violet-50 text-violet-700 ring-1 ring-violet-100' },
  { day: '05', month: 'JUIN', time: '09:00', title: 'Suivi de dossier', tag: 'Suivi', color: 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100' },
]

const DEMO_TASKS = [
  { label: 'Envoyer l\'attestation d\'assurance', deadline: '25 Mai 2024', urgent: true },
  { label: 'Payer la 3e tranche (Visa)', deadline: '10 Juin 2024', urgent: false },
  { label: 'Prendre rendez-vous pour demande de visa', deadline: '15 Juin 2024', urgent: false },
]

function counselorInitials(firstName: string, lastName: string): string {
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
}

function globalStatusLabel(status: string): string {
  if (['admission_obtained', 'visa_obtained', 'in_progress', 'documents_pending'].includes(status)) {
    return 'En cours'
  }
  return STATUS_LABELS[status] ?? status
}

function currentStepLabel(status: string): string {
  return STATUS_LABELS[status] ?? 'Admission obtenue'
}

export function CandidateDashboardPage() {
  const { data: user } = useCurrentUser()
  const { data: dashboard, isLoading, isError } = useMyDashboard()

  const status = dashboard?.status ?? 'admission_obtained'
  const counselor = dashboard?.counselor
  const counselorName = counselor
    ? `${counselor.firstName} ${counselor.lastName}`.trim()
    : 'Marie Kouassi'
  const counselorEmail = counselor?.email ?? 'marie.kouassi@onreach.inovixora.fr'

  const validatedTypes = new Set(
    dashboard?.documents
      .filter((document) => document.status === 'validated')
      .map((document) => DOCUMENT_LABELS[document.type] ?? document.type) ?? [],
  )
  const displayDocs = DEMO_DOCUMENTS.map((name) => ({
    name,
    validated: validatedTypes.has(name) || validatedTypes.size === 0,
  }))

  const checklistTasks = dashboard?.checklist.items.filter((item) => !item.completed) ?? []
  const tasks =
    checklistTasks.length > 0
      ? checklistTasks.slice(0, 3).map((item, idx) => ({
          label: item.label,
          deadline: DEMO_TASKS[idx]?.deadline ?? '—',
          urgent: idx === 0,
        }))
      : DEMO_TASKS

  if (isError) {
    return (
      <div className="flex min-h-[40vh] items-center justify-center">
        <p className="text-sm text-red-600">Impossible de charger votre espace.</p>
      </div>
    )
  }

  const contentLoading = isLoading && !dashboard

  return (
    <div className="mx-auto max-w-[1400px] space-y-7">
      <div className="grid items-start gap-5 lg:grid-cols-[1fr_minmax(280px,380px)] lg:gap-8">
        <div className="min-w-0">
          <h1 className="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
            Bonjour {user?.firstName ?? 'Mohamed'} 👋
          </h1>
          <p className="mt-1.5 max-w-xl text-sm leading-relaxed text-slate-500">
            Bienvenue sur votre espace On&apos;Reach. Voici l&apos;avancement de votre dossier.
          </p>
        </div>

        {contentLoading ? (
          <div className="h-24 animate-pulse rounded-2xl bg-slate-200/70" />
        ) : (
          <Panel className="flex flex-col gap-3 sm:flex-row sm:items-center">
          <div className="flex min-w-0 flex-1 items-center gap-3">
            <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-400 to-pink-600 text-sm font-bold text-white shadow-sm">
              {counselorInitials(counselor?.firstName ?? 'M', counselor?.lastName ?? 'K')}
            </div>
            <div className="min-w-0">
              <p className="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                Conseillère dédiée
              </p>
              <p className="truncate font-semibold text-slate-900">{counselorName}</p>
              <p className="truncate text-xs text-slate-500">{counselorEmail}</p>
            </div>
          </div>
          <Button
            type="button"
            size="sm"
            className="w-full shrink-0 bg-blue-600 shadow-sm hover:bg-blue-700 sm:w-auto"
          >
            <Mail className="mr-1.5 h-4 w-4" />
            Message
          </Button>
        </Panel>
        )}
      </div>

      {contentLoading ? (
        <div className="space-y-7 animate-pulse">
          <div className="grid grid-cols-2 gap-3 lg:grid-cols-4">
            {Array.from({ length: 4 }).map((_, index) => (
              <div key={index} className="h-28 rounded-2xl bg-slate-200/70" />
            ))}
          </div>
          <div className="h-48 rounded-2xl bg-slate-200/70" />
          <div className="grid gap-4 md:grid-cols-2">
            {Array.from({ length: 4 }).map((_, index) => (
              <div key={index} className="h-40 rounded-2xl bg-slate-200/70" />
            ))}
          </div>
        </div>
      ) : (
        <>
      <div className="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
        <KpiCard
          icon={<ClipboardList className="h-5 w-5 text-violet-600" />}
          iconBg="bg-violet-50"
          label="Statut global"
          value={globalStatusLabel(status)}
          hint="Dossier en traitement"
        />
        <KpiCard
          icon={<GraduationCap className="h-5 w-5 text-emerald-600" />}
          iconBg="bg-emerald-50"
          label="Étape actuelle"
          value={currentStepLabel(status)}
        />
        <KpiCard
          icon={<Wallet className="h-5 w-5 text-orange-600" />}
          iconBg="bg-orange-50"
          label="Paiements"
          value="2/3"
          hint="Tranches réglées"
        />
        <KpiCard
          icon={<Calendar className="h-5 w-5 text-blue-600" />}
          iconBg="bg-blue-50"
          label="Prochain RDV"
          value="24 Mai"
          hint="2024"
        />
      </div>

      <Panel>
        <SectionTitle>Avancement de ma procédure</SectionTitle>
        <div className="mt-5">
          <CandidateProcedureTimeline status={status} />
        </div>
        {status === 'admission_obtained' && (
          <div className="mt-5 flex flex-col gap-3 rounded-xl bg-orange-50/80 px-4 py-3.5 ring-1 ring-orange-100 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-sm leading-relaxed text-orange-900">
              Votre admission a été enregistrée. Vous pouvez maintenant lancer votre demande de visa.
            </p>
            <Button
              type="button"
              size="sm"
              variant="outline"
              className="shrink-0 border-orange-200 bg-white text-orange-800 hover:bg-orange-50"
            >
              Étapes suivantes
            </Button>
          </div>
        )}
      </Panel>

      <div className="grid items-start gap-4 md:grid-cols-2 lg:gap-5">
        <Panel compact>
          <div className="mb-3 flex items-baseline justify-between gap-2">
            <SectionTitle>Paiements</SectionTitle>
            <span className="text-lg font-bold tabular-nums text-slate-900">750 €</span>
          </div>
          <div className="flex items-center gap-4">
            <PaymentDonut percent={66} paid={500} total={750} />
            <div className="min-w-0 flex-1 space-y-2">
              <TrancheRow label="Tranche 1 — Inscription" amount="250 €" status="paid" date="15/02/2024" />
              <TrancheRow label="Tranche 2 — Accompagnement" amount="250 €" status="paid" date="10/03/2024" />
              <TrancheRow label="Tranche 3 — Visa" amount="250 €" status="pending" />
            </div>
          </div>
        </Panel>

        <Panel compact>
          <SectionTitle className="mb-3">Mes documents</SectionTitle>
          <ul className="divide-y divide-slate-100">
            {displayDocs.map((doc) => (
              <li key={doc.name} className="flex items-center justify-between gap-3 py-2 first:pt-0 last:pb-0">
                <span className="text-sm text-slate-700">{doc.name}</span>
                <div className="flex shrink-0 items-center gap-2">
                  <span
                    className={`rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ${
                      doc.validated
                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-100'
                        : 'bg-orange-50 text-orange-700 ring-orange-100'
                    }`}
                  >
                    {doc.validated ? 'Validé' : 'À fournir'}
                  </span>
                  {doc.validated && (
                    <button
                      type="button"
                      className="rounded-md p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                      aria-label={`Télécharger ${doc.name}`}
                    >
                      <Download className="h-3.5 w-3.5" />
                    </button>
                  )}
                </div>
              </li>
            ))}
          </ul>
        </Panel>

        <Panel compact>
          <SectionTitle className="mb-3">Prochains rendez-vous</SectionTitle>
          <ul className="space-y-2.5">
            {DEMO_APPOINTMENTS.map((apt) => (
              <li key={apt.title} className="flex gap-2.5">
                <div className="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-slate-50 ring-1 ring-slate-100">
                  <span className="text-sm font-bold leading-none text-slate-800">{apt.day}</span>
                  <span className="text-[8px] font-semibold uppercase text-slate-400">{apt.month}</span>
                </div>
                <div className="min-w-0 flex-1">
                  <p className="text-[11px] text-slate-400">{apt.time}</p>
                  <p className="text-sm font-medium leading-snug text-slate-800">{apt.title}</p>
                  <span className={`mt-0.5 inline-block rounded-full px-1.5 py-0.5 text-[10px] font-medium ${apt.color}`}>
                    {apt.tag}
                  </span>
                </div>
              </li>
            ))}
          </ul>
        </Panel>

        <Panel compact>
          <SectionTitle className="mb-3">Mes tâches</SectionTitle>
          <ul className="space-y-2">
            {tasks.map((task) => (
              <li
                key={task.label}
                className="flex items-start justify-between gap-2 rounded-lg bg-slate-50 px-2.5 py-2 ring-1 ring-slate-100"
              >
                <div className="min-w-0">
                  <p className="text-sm leading-snug text-slate-800">{task.label}</p>
                  <p className="mt-0.5 text-[11px] text-slate-400">Échéance : {task.deadline}</p>
                </div>
                <span
                  className={`shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold ${
                    task.urgent
                      ? 'bg-red-50 text-red-700 ring-1 ring-red-100'
                      : 'bg-blue-50 text-blue-700 ring-1 ring-blue-100'
                  }`}
                >
                  {task.urgent ? 'Urgent' : 'À faire'}
                </span>
              </li>
            ))}
          </ul>
        </Panel>
      </div>
        </>
      )}
    </div>
  )
}

function Panel({
  children,
  className = '',
  compact = false,
}: {
  children: ReactNode
  className?: string
  compact?: boolean
}) {
  return (
    <section
      className={`h-fit self-start rounded-2xl border border-slate-200/80 bg-white shadow-sm ${
        compact ? 'p-4' : 'p-5 sm:p-6'
      } ${className}`}
    >
      {children}
    </section>
  )
}

function SectionTitle({ children, className = '' }: { children: ReactNode; className?: string }) {
  return (
    <h2 className={`text-base font-semibold text-slate-900 ${className}`}>
      {children}
    </h2>
  )
}

function KpiCard({
  icon,
  iconBg,
  label,
  value,
  hint,
}: {
  icon: ReactNode
  iconBg: string
  label: string
  value: string
  hint?: string
}) {
  return (
    <div className="flex h-full flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5">
      <div className={`mb-3 flex h-9 w-9 items-center justify-center rounded-xl ${iconBg}`}>
        {icon}
      </div>
      <div>
        <p className="text-[11px] font-medium uppercase tracking-wide text-slate-400">{label}</p>
        <p className="mt-0.5 text-lg font-bold leading-tight text-slate-900 sm:text-xl">{value}</p>
        {hint && <p className="mt-0.5 text-[11px] text-slate-400">{hint}</p>}
      </div>
    </div>
  )
}

function PaymentDonut({ percent, paid }: { percent: number; paid: number; total: number }) {
  const r = 34
  const c = 2 * Math.PI * r
  const offset = c - (percent / 100) * c

  return (
    <div className="relative h-[96px] w-[96px] shrink-0">
      <svg className="h-full w-full -rotate-90" viewBox="0 0 100 100">
        <circle cx="50" cy="50" r={r} fill="none" stroke="#f1f5f9" strokeWidth="8" />
        <circle
          cx="50"
          cy="50"
          r={r}
          fill="none"
          stroke="#3b82f6"
          strokeWidth="8"
          strokeDasharray={c}
          strokeDashoffset={offset}
          strokeLinecap="round"
        />
      </svg>
      <div className="absolute inset-0 flex flex-col items-center justify-center">
        <span className="text-sm font-bold tabular-nums text-slate-900">{paid} €</span>
        <span className="text-[9px] text-slate-500">{percent}%</span>
      </div>
    </div>
  )
}

function TrancheRow({
  label,
  amount,
  status,
  date,
}: {
  label: string
  amount: string
  status: 'paid' | 'pending'
  date?: string
}) {
  return (
    <div className="flex items-start justify-between gap-2 text-sm">
      <div className="min-w-0">
        <p className="font-medium leading-snug text-slate-800">{label}</p>
        <p className="mt-0.5 text-xs text-slate-400">
          {amount}
          {date ? ` · ${date}` : ''}
        </p>
      </div>
      <span
        className={`shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold ${
          status === 'paid'
            ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
            : 'bg-orange-50 text-orange-700 ring-1 ring-orange-100'
        }`}
      >
        {status === 'paid' ? 'Payé' : 'En attente'}
      </span>
    </div>
  )
}
