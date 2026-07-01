import type { ReactNode } from 'react'
import {
  Calendar,
  ClipboardList,
  GraduationCap,
  Wallet,
} from 'lucide-react'
import { CounselorPendingBanner } from '@/components/candidate/CounselorPendingBanner'
import { CandidateProcedureTimeline } from '@/components/candidate/CandidateProcedureTimeline'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { useMyDashboard } from '@/hooks/useMyDashboard'
import { STATUS_LABELS } from '@/lib/candidate-utils'
import { DOCUMENT_TYPES } from '@/lib/profile-api'

const DOCUMENT_LABEL_BY_TYPE = Object.fromEntries(DOCUMENT_TYPES.map((item) => [item.value, item.label]))

function counselorInitials(firstName: string, lastName: string): string {
  return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
}

function globalStatusLabel(status: string, statusLabel?: string): string {
  return statusLabel ?? STATUS_LABELS[status] ?? status
}

export function CandidateDashboardPage() {
  const { data: user } = useCurrentUser()
  const { data: dashboard, isLoading, isError } = useMyDashboard()

  const status = dashboard?.status ?? 'lead'
  const statusLabel = dashboard?.statusLabel
  const counselor = dashboard?.counselor
  const hasCounselor = Boolean(counselor)

  const checklistTasks = dashboard?.checklist.items.filter((item) => !item.completed) ?? []
  const documents = dashboard?.documents ?? []

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
            Bonjour {user?.firstName ?? 'Candidat'}
          </h1>
          <p className="mt-1.5 max-w-xl text-sm leading-relaxed text-slate-500">
            Bienvenue sur votre espace On&apos;Reach. Voici l&apos;avancement de votre dossier.
          </p>
        </div>

        {contentLoading ? (
          <div className="h-24 animate-pulse rounded-2xl bg-slate-200/70" />
        ) : hasCounselor && counselor ? (
          <Panel className="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div className="flex min-w-0 flex-1 items-center gap-3">
              <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-rose-400 to-pink-600 text-sm font-bold text-white shadow-sm">
                {counselorInitials(counselor.firstName, counselor.lastName)}
              </div>
              <div className="min-w-0">
                <p className="text-[11px] font-medium uppercase tracking-wide text-slate-400">
                  Conseiller dédié
                </p>
                <p className="truncate font-semibold text-slate-900">
                  {counselor.firstName} {counselor.lastName}
                </p>
                <p className="truncate text-xs text-slate-500">{counselor.email}</p>
              </div>
            </div>
          </Panel>
        ) : (
          <CounselorPendingBanner />
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
              value={globalStatusLabel(status, statusLabel)}
              hint="Votre dossier"
            />
            <KpiCard
              icon={<GraduationCap className="h-5 w-5 text-emerald-600" />}
              iconBg="bg-emerald-50"
              label="Complétude dossier"
              value={`${dashboard?.checklist.percent ?? 0}%`}
            />
            <KpiCard
              icon={<Wallet className="h-5 w-5 text-orange-600" />}
              iconBg="bg-orange-50"
              label="Paiements"
              value="—"
              hint="Non configuré"
            />
            <KpiCard
              icon={<Calendar className="h-5 w-5 text-blue-600" />}
              iconBg="bg-blue-50"
              label="Prochain RDV"
              value="—"
              hint="Aucun rendez-vous"
            />
          </div>

          <Panel>
            <SectionTitle>Avancement de ma procédure</SectionTitle>
            <div className="mt-5">
              <CandidateProcedureTimeline status={status} />
            </div>
          </Panel>

          <div className="grid items-start gap-4 md:grid-cols-2 lg:gap-5">
            <Panel compact>
              <SectionTitle className="mb-3">Paiements</SectionTitle>
              <p className="text-sm text-slate-500">Aucun paiement enregistré pour le moment.</p>
            </Panel>

            <Panel compact>
              <SectionTitle className="mb-3">Mes documents</SectionTitle>
              {documents.length === 0 ? (
                <p className="text-sm text-slate-500">Aucun document déposé pour le moment.</p>
              ) : (
                <ul className="divide-y divide-slate-100">
                  {documents.map((doc) => (
                    <li key={doc.type} className="flex items-center justify-between gap-3 py-2 first:pt-0 last:pb-0">
                      <span className="text-sm text-slate-700">
                        {DOCUMENT_LABEL_BY_TYPE[doc.type] ?? doc.type}
                      </span>
                      <span
                        className={`rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 ${
                          doc.status === 'validated'
                            ? 'bg-emerald-50 text-emerald-700 ring-emerald-100'
                            : 'bg-orange-50 text-orange-700 ring-orange-100'
                        }`}
                      >
                        {doc.status === 'validated' ? 'Validé' : 'À fournir'}
                      </span>
                    </li>
                  ))}
                </ul>
              )}
            </Panel>

            <Panel compact>
              <SectionTitle className="mb-3">Prochains rendez-vous</SectionTitle>
              <p className="text-sm text-slate-500">Aucun rendez-vous planifié.</p>
            </Panel>

            <Panel compact>
              <SectionTitle className="mb-3">Mes tâches</SectionTitle>
              {checklistTasks.length === 0 ? (
                <p className="text-sm text-slate-500">Aucune tâche en attente.</p>
              ) : (
                <ul className="space-y-2">
                  {checklistTasks.slice(0, 5).map((task) => (
                    <li
                      key={task.id}
                      className="flex items-start justify-between gap-2 rounded-lg bg-slate-50 px-2.5 py-2 ring-1 ring-slate-100"
                    >
                      <div className="min-w-0">
                        <p className="text-sm leading-snug text-slate-800">{task.label}</p>
                        {task.required && (
                          <p className="mt-0.5 text-[11px] text-slate-400">Document requis</p>
                        )}
                      </div>
                      <span className="shrink-0 rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 ring-1 ring-blue-100">
                        À faire
                      </span>
                    </li>
                  ))}
                </ul>
              )}
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
