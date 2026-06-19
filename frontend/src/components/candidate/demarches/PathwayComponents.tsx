import { Link } from 'react-router-dom'
import { useState } from 'react'
import {
  CheckCircle2,
  ChevronDown,
  ChevronRight,
  Circle,
  Clock,
  Lock,
} from 'lucide-react'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { CandidatePanel } from '@/components/candidate/CandidatePageLayout'
import type { Pathway, PathwayStage, PathwaySubStep } from '@/lib/pathways-api'
import { countValidatedSubSteps, getNextPendingSubStep, PATHWAY_ROUTES } from '@/lib/pathways-api'
import { cn } from '@/lib/utils'

function statusBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'info' | 'default' {
  if (status === 'accepted') return 'success'
  if (status === 'in_progress') return 'info'
  if (status === 'blocked' || status === 'refused') return 'danger'
  if (status === 'not_started') return 'default'
  return 'warning'
}

export function PathwayHero({ pathway }: { pathway: Pathway }) {
  const next = getNextPendingSubStep(pathway)
  const { done, total } = countValidatedSubSteps(pathway)

  return (
    <CandidatePanel className="relative overflow-hidden">
      <div className="absolute inset-0 bg-gradient-to-br from-blue-50/80 via-white to-slate-50 pointer-events-none" />
      <div className="relative flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-2">
            <h2 className="text-xl font-bold tracking-tight text-slate-900">{pathway.name}</h2>
            <Badge variant={statusBadgeVariant(pathway.status)}>{pathway.statusLabel}</Badge>
          </div>
          {pathway.blockedReason && (
            <p className="mt-2 flex items-start gap-2 text-sm text-red-600">
              <Lock className="mt-0.5 h-4 w-4 shrink-0" />
              {pathway.blockedReason}
            </p>
          )}
          {next && (
            <p className="mt-3 text-sm font-medium text-blue-700">
              Prochaine étape : {next.title}
              {next.dueDate && (
                <span className="ml-2 font-normal text-slate-500">
                  (échéance {new Date(next.dueDate).toLocaleDateString('fr-FR')})
                </span>
              )}
            </p>
          )}
          {!next && pathway.progressPercent === 100 && (
            <p className="mt-3 text-sm font-medium text-emerald-700">Toutes les étapes obligatoires sont validées.</p>
          )}
          <p className="mt-2 text-xs text-slate-500">
            {done}/{total} sous-étapes validées · mis à jour le{' '}
            {new Date(pathway.updatedAt).toLocaleDateString('fr-FR')}
          </p>
        </div>
        <div className="w-full shrink-0 sm:w-44">
          <div className="flex items-end justify-between gap-2">
            <span className="text-xs font-medium uppercase tracking-wide text-slate-500">Progression</span>
            <span className="text-2xl font-bold tabular-nums text-slate-900">{pathway.progressPercent}%</span>
          </div>
          <Progress value={pathway.progressPercent} className="mt-2 h-2.5" />
        </div>
      </div>
    </CandidatePanel>
  )
}

function SubStepRow({ subStep }: { subStep: PathwaySubStep }) {
  const isOverdue =
    subStep.dueDate && !subStep.validated && new Date(subStep.dueDate) < new Date(new Date().toDateString())

  return (
    <li
      className={cn(
        'flex items-start gap-3 rounded-xl border px-3 py-3 transition-colors',
        subStep.validated
          ? 'border-emerald-100 bg-emerald-50/60'
          : isOverdue
            ? 'border-orange-200 bg-orange-50/50'
            : 'border-slate-100 bg-white',
      )}
    >
      {subStep.validated ? (
        <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" />
      ) : (
        <Circle className="mt-0.5 h-4 w-4 shrink-0 text-slate-300" />
      )}
      <div className="min-w-0 flex-1">
        <div className="flex flex-wrap items-center gap-2">
          <p className={cn('text-sm font-medium', subStep.validated ? 'text-emerald-900' : 'text-slate-900')}>
            {subStep.title}
          </p>
          {!subStep.required && (
            <Badge variant="default" className="text-[10px]">
              Optionnel
            </Badge>
          )}
          {isOverdue && (
            <Badge variant="warning" className="text-[10px]">
              En retard
            </Badge>
          )}
        </div>
        {subStep.description && <p className="mt-0.5 text-xs text-slate-500">{subStep.description}</p>}
        {subStep.dueDate && (
          <p className="mt-1 flex items-center gap-1 text-xs text-slate-400">
            <Clock className="h-3 w-3" />
            Échéance {new Date(subStep.dueDate).toLocaleDateString('fr-FR')}
          </p>
        )}
        {subStep.validated && subStep.counselorValidatedBy && (
          <p className="mt-1 text-xs text-emerald-700">
            Validé par {subStep.counselorValidatedBy.firstName} {subStep.counselorValidatedBy.lastName}
          </p>
        )}
      </div>
    </li>
  )
}

function StageBlock({ stage, defaultOpen }: { stage: PathwayStage; defaultOpen: boolean }) {
  const [open, setOpen] = useState(defaultOpen)
  const requiredSubSteps = stage.subSteps.filter((s) => s.required)
  const validatedCount = requiredSubSteps.filter((s) => s.validated).length

  return (
    <div className="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <button
        type="button"
        onClick={() => setOpen((v) => !v)}
        className="flex w-full items-center gap-3 px-4 py-4 text-left hover:bg-slate-50/80 transition-colors"
      >
        {open ? (
          <ChevronDown className="h-4 w-4 shrink-0 text-slate-400" />
        ) : (
          <ChevronRight className="h-4 w-4 shrink-0 text-slate-400" />
        )}
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-2">
            <h3 className="font-semibold text-slate-900">{stage.title}</h3>
            <span className="text-xs font-medium tabular-nums text-slate-500">
              {validatedCount}/{requiredSubSteps.length || stage.subSteps.length}
            </span>
          </div>
          {stage.description && <p className="mt-0.5 text-xs text-slate-500 line-clamp-1">{stage.description}</p>}
        </div>
        <div className="hidden w-24 sm:block">
          <Progress value={stage.progressPercent} className="h-1.5" />
        </div>
        <span className="text-sm font-bold tabular-nums text-slate-700">{stage.progressPercent}%</span>
      </button>
      {open && (
        <ul className="space-y-2 border-t border-slate-100 bg-slate-50/50 px-4 py-4">
          {stage.subSteps.map((subStep) => (
            <SubStepRow key={subStep.id} subStep={subStep} />
          ))}
        </ul>
      )}
    </div>
  )
}

export function PathwayStageTimeline({ pathway }: { pathway: Pathway }) {
  const firstIncompleteIndex = pathway.stages.findIndex((stage) => stage.progressPercent < 100)

  return (
    <div className="space-y-3">
      {pathway.stages.map((stage, index) => (
        <StageBlock key={stage.id} stage={stage} defaultOpen={index === firstIncompleteIndex || firstIncompleteIndex === -1 && index === pathway.stages.length - 1} />
      ))}
    </div>
  )
}

export function PathwayOverviewCards({ pathways }: { pathways: Pathway[] }) {
  if (!pathways.length) {
    return (
      <CandidatePanel>
        <p className="text-sm text-slate-500">
          Aucun parcours assigné. Complétez votre type de candidature dans Mon dossier pour démarrer.
        </p>
      </CandidatePanel>
    )
  }

  return (
    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
      {pathways.map((pathway) => (
        <PathwayMiniCard key={pathway.id} pathway={pathway} />
      ))}
    </div>
  )
}

function PathwayMiniCard({ pathway }: { pathway: Pathway }) {
  const next = getNextPendingSubStep(pathway)
  const { done, total } = countValidatedSubSteps(pathway)

  return (
    <Link
      to={PATHWAY_ROUTES[pathway.code]}
      className="group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md"
    >
      <div className="flex items-start justify-between gap-2">
        <h3 className="font-semibold text-slate-900 group-hover:text-blue-700">{pathway.name}</h3>
        <Badge variant={statusBadgeVariant(pathway.status)}>{pathway.statusLabel}</Badge>
      </div>
      <Progress value={pathway.progressPercent} className="mt-4" />
      <p className="mt-2 text-xs text-slate-500">
        {done}/{total} étapes · {pathway.progressPercent}%
      </p>
      {next && (
        <p className="mt-3 text-sm text-blue-600 line-clamp-2">{next.title}</p>
      )}
    </Link>
  )
}

export function StudyTypeBanner({ label }: { label: string | null }) {
  if (!label) return null

  return (
    <div className="rounded-xl border border-blue-100 bg-blue-50/80 px-4 py-3 text-sm text-blue-900">
      Parcours configurés pour : <span className="font-semibold">{label}</span>
    </div>
  )
}
