import { Link } from 'react-router-dom'
import {
  AlertTriangle,
  Calendar,
  CheckCircle2,
  Circle,
  Clock,
  FileText,
  User,
} from 'lucide-react'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { CandidatePanel, CandidateSectionTitle } from '@/components/candidate/CandidatePageLayout'
import type {
  DemarchesAlert,
  DemarchesCompletion,
  DocumentIndicator,
  ProcedureCard,
  TimelineEntry,
  WorkflowStep,
} from '@/lib/demarches-api'
import { cn } from '@/lib/utils'

export function AlertsBanner({ alerts }: { alerts: DemarchesAlert[] }) {
  if (!alerts.length) return null

  const styles = {
    info: 'border-blue-200 bg-blue-50 text-blue-800',
    warning: 'border-orange-200 bg-orange-50 text-orange-800',
    error: 'border-red-200 bg-red-50 text-red-800',
  }

  return (
    <div className="space-y-2">
      {alerts.map((alert) => (
        <div
          key={`${alert.type}-${alert.message}`}
          className={cn('flex items-start gap-2 rounded-xl border px-4 py-3 text-sm', styles[alert.severity])}
        >
          <AlertTriangle className="mt-0.5 h-4 w-4 shrink-0" />
          <span>{alert.message}</span>
        </div>
      ))}
    </div>
  )
}

export function KpiGrid({ completion }: { completion: DemarchesCompletion }) {
  const items = [
    { label: 'Profil', value: completion.profile },
    { label: 'Documents', value: completion.documents },
    { label: 'Campus France', value: completion.campusFrance },
    { label: 'Parcoursup', value: completion.parcoursup },
    { label: 'Paris-Saclay', value: completion.parisSaclay },
    { label: 'Visa', value: completion.visa },
  ]

  return (
    <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
      {items.map((item) => (
        <div
          key={item.label}
          className="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
        >
          <div className="flex items-center justify-between gap-2">
            <span className="text-sm font-medium text-slate-600">{item.label}</span>
            <span className="text-lg font-bold tabular-nums text-slate-900">{item.value}%</span>
          </div>
          <Progress value={item.value} className="mt-3" />
        </div>
      ))}
    </div>
  )
}

export function GlobalCompletionHero({
  completion,
  referenceNumber,
}: {
  completion: DemarchesCompletion
  referenceNumber: string
}) {
  return (
    <CandidatePanel className="relative overflow-hidden bg-gradient-to-br from-slate-900 via-[#1a2744] to-blue-900 text-white">
      <div className="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <p className="text-xs font-medium uppercase tracking-wider text-blue-200">Complétude globale</p>
          <p className="mt-2 text-4xl font-bold tabular-nums">{completion.global}%</p>
          <p className="mt-1 text-sm text-slate-300">Dossier {referenceNumber}</p>
        </div>
        <div className="h-28 w-28 shrink-0">
          <svg className="h-full w-full -rotate-90" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.15)" strokeWidth="8" />
            <circle
              cx="50"
              cy="50"
              r="42"
              fill="none"
              stroke="#60a5fa"
              strokeWidth="8"
              strokeDasharray={2 * Math.PI * 42}
              strokeDashoffset={2 * Math.PI * 42 * (1 - completion.global / 100)}
              strokeLinecap="round"
            />
          </svg>
        </div>
      </div>
    </CandidatePanel>
  )
}

export function SummaryStats({
  documentsValidated,
  documentsMissing,
  paymentsPending,
  upcomingAppointments,
}: {
  documentsValidated: number
  documentsMissing: number
  paymentsPending: number
  upcomingAppointments: number
}) {
  const stats = [
    { label: 'Documents validés', value: documentsValidated, icon: CheckCircle2 },
    { label: 'Documents manquants', value: documentsMissing, icon: FileText },
    { label: 'Paiements en attente', value: paymentsPending, icon: Clock },
    { label: 'Rendez-vous à venir', value: upcomingAppointments, icon: Calendar },
  ]

  return (
    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
      {stats.map(({ label, value, icon: Icon }) => (
        <div key={label} className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div className="flex items-center gap-2 text-slate-500">
            <Icon className="h-4 w-4" />
            <span className="text-xs font-medium">{label}</span>
          </div>
          <p className="mt-2 text-2xl font-bold text-slate-900">{value}</p>
        </div>
      ))}
    </div>
  )
}

const PROCEDURE_ROUTES: Record<string, string> = {
  campus_france: '/demarches/campus-france',
  parcoursup: '/demarches/parcoursup',
  paris_saclay: '/demarches/paris-saclay',
}

export function ProcedureCards({ procedures }: { procedures: ProcedureCard[] }) {
  return (
    <div className="grid gap-4 md:grid-cols-3">
      {procedures.map((card) => (
        <Link
          key={card.type}
          to={PROCEDURE_ROUTES[card.type] ?? '/demarches'}
          className="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md"
        >
          <div className="flex items-start justify-between gap-2">
            <h3 className="font-semibold text-slate-900 group-hover:text-blue-700">{card.label}</h3>
            <Badge variant={card.progress >= 80 ? 'success' : card.progress >= 40 ? 'warning' : 'default'}>
              {card.statusLabel}
            </Badge>
          </div>
          <Progress value={card.progress} className="mt-4" />
          <p className="mt-3 text-xs text-slate-500">
            Mis à jour le {new Date(card.updatedAt).toLocaleDateString('fr-FR')}
          </p>
          <p className="mt-2 text-sm font-medium text-blue-600">{card.nextAction}</p>
        </Link>
      ))}
    </div>
  )
}

export function CounselorCard({
  counselor,
}: {
  counselor: { fullName: string; email: string } | null
}) {
  if (!counselor) {
    return (
      <CandidatePanel>
        <CandidateSectionTitle>Conseiller assigné</CandidateSectionTitle>
        <p className="mt-2 text-sm text-slate-500">Aucun conseiller assigné pour le moment.</p>
      </CandidatePanel>
    )
  }

  return (
    <CandidatePanel>
      <CandidateSectionTitle>Conseiller assigné</CandidateSectionTitle>
      <div className="mt-4 flex items-center gap-4">
        <div className="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-700">
          <User className="h-5 w-5" />
        </div>
        <div>
          <p className="font-semibold text-slate-900">{counselor.fullName}</p>
          <p className="text-sm text-slate-500">{counselor.email}</p>
        </div>
      </div>
      <button
        type="button"
        className="mt-4 text-sm font-medium text-blue-600 hover:text-blue-700"
        disabled
        title="Messagerie bientôt disponible"
      >
        Contacter mon conseiller →
      </button>
    </CandidatePanel>
  )
}

export function WorkflowStepper({ steps }: { steps: WorkflowStep[] }) {
  return (
    <ol className="space-y-3">
      {steps.map((step) => (
        <li key={step.key} className="flex items-start gap-3">
          {step.state === 'done' ? (
            <CheckCircle2 className="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" />
          ) : step.state === 'current' ? (
            <Clock className="mt-0.5 h-5 w-5 shrink-0 text-orange-500" />
          ) : (
            <Circle className="mt-0.5 h-5 w-5 shrink-0 text-slate-300" />
          )}
          <div>
            <p
              className={cn(
                'text-sm font-medium',
                step.state === 'done' && 'text-slate-900',
                step.state === 'current' && 'text-orange-700',
                step.state === 'upcoming' && 'text-slate-400',
              )}
            >
              {step.state === 'done' ? '✓ ' : step.state === 'current' ? '→ ' : '○ '}
              {step.label}
            </p>
          </div>
        </li>
      ))}
    </ol>
  )
}

const DOC_STATUS: Record<DocumentIndicator['status'], { label: string; variant: 'success' | 'warning' | 'danger' | 'default' }> = {
  validated: { label: 'Validé', variant: 'success' },
  pending: { label: 'En attente', variant: 'warning' },
  rejected: { label: 'Refusé', variant: 'danger' },
  missing: { label: 'Manquant', variant: 'danger' },
}

export function DocumentStatusList({ documents }: { documents: DocumentIndicator[] }) {
  return (
    <ul className="divide-y divide-slate-100">
      {documents.map((doc) => {
        const meta = DOC_STATUS[doc.status]
        return (
          <li key={doc.type} className="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
            <div className="min-w-0">
              <p className="text-sm font-medium text-slate-900">{doc.label}</p>
              {doc.originalFilename && (
                <p className="truncate text-xs text-slate-500">{doc.originalFilename}</p>
              )}
            </div>
            <Badge variant={meta.variant}>{meta.label}</Badge>
          </li>
        )
      })}
    </ul>
  )
}

export function TimelineList({ entries }: { entries: TimelineEntry[] }) {
  if (!entries.length) {
    return <p className="text-sm text-slate-500">Aucun événement pour le moment.</p>
  }

  return (
    <ol className="relative border-l-2 border-slate-200 pl-6">
      {entries.map((entry) => (
        <li key={entry.id} className="relative pb-6 last:pb-0">
          <span className="absolute -left-[9px] top-1 h-4 w-4 rounded-full border-2 border-white bg-blue-500" />
          <p className="text-xs font-semibold text-slate-400">{entry.date}</p>
          <p className="mt-1 text-sm font-medium text-slate-900">{entry.description}</p>
          <p className="mt-0.5 text-xs text-slate-500">{entry.author}</p>
        </li>
      ))}
    </ol>
  )
}

export function ProcedureTabs({
  tabs,
  active,
  onChange,
}: {
  tabs: { id: string; label: string }[]
  active: string
  onChange: (id: string) => void
}) {
  return (
    <div className="flex flex-wrap gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1">
      {tabs.map((tab) => (
        <button
          key={tab.id}
          type="button"
          onClick={() => onChange(tab.id)}
          className={cn(
            'rounded-lg px-3 py-2 text-sm font-medium transition-colors',
            active === tab.id
              ? 'bg-white text-slate-900 shadow-sm'
              : 'text-slate-500 hover:text-slate-800',
          )}
        >
          {tab.label}
        </button>
      ))}
    </div>
  )
}

export function LoadingState() {
  return (
    <div className="flex min-h-[200px] items-center justify-center">
      <p className="text-sm text-slate-500">Chargement de vos démarches…</p>
    </div>
  )
}

export function ErrorState({ message }: { message?: string }) {
  return (
    <CandidatePanel>
      <p className="text-sm text-red-600">{message ?? 'Impossible de charger vos démarches.'}</p>
    </CandidatePanel>
  )
}
