import { useState } from 'react'
import { ChevronDown, ChevronRight, Clock } from 'lucide-react'
import { PermissionGate } from '@/components/auth/PermissionGate'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { usePatchCandidatePathwaySubStep } from '@/hooks/useCandidatePathways'
import type { Pathway, PathwayStage, PathwaySubStep } from '@/lib/pathways-api'
import {
  formatPathwayUser,
  isAdminValidated,
  isCounselorValidated,
  isPendingAdminValidation,
} from '@/lib/pathways-api'
import { cn } from '@/lib/utils'

function EditableSubStepRow({
  subStep,
  pathway,
  candidateId,
  isAdmin,
  pendingSubStepId,
}: {
  subStep: PathwaySubStep
  pathway: Pathway
  candidateId: string
  isAdmin: boolean
  pendingSubStepId: string | null
}) {
  const patchMutation = usePatchCandidatePathwaySubStep(candidateId)
  const counselorValidated = isCounselorValidated(subStep)
  const adminValidated = isAdminValidated(subStep)
  const pendingAdmin = isPendingAdminValidation(subStep, pathway)
  const isPending = pendingSubStepId === subStep.id
  const isOverdue =
    subStep.dueDate && !subStep.validated && new Date(subStep.dueDate) < new Date(new Date().toDateString())

  const toggleCounselor = () => {
    patchMutation.mutate({
      pathwayId: pathway.id,
      subStepId: subStep.id,
      payload: { counselorValidated: !counselorValidated },
    })
  }

  const toggleAdmin = () => {
    patchMutation.mutate({
      pathwayId: pathway.id,
      subStepId: subStep.id,
      payload: { adminValidated: !adminValidated },
    })
  }

  return (
    <li
      className={cn(
        'flex items-start gap-3 rounded-xl border px-3 py-3 transition-colors',
        subStep.validated
          ? 'border-emerald-100 bg-emerald-50/60'
          : pendingAdmin
            ? 'border-amber-100 bg-amber-50/50'
            : isOverdue
              ? 'border-orange-200 bg-orange-50/50'
              : 'border-slate-100 bg-white',
      )}
    >
      <div className="mt-0.5 flex shrink-0 flex-col gap-2">
        <PermissionGate
          permission="applications.edit"
          fallback={
            <label className="flex cursor-not-allowed items-center gap-1.5">
              <input
                type="checkbox"
                checked={counselorValidated}
                disabled
                className="h-4 w-4 rounded border-slate-300"
                aria-label={`Validation conseiller — ${subStep.title}`}
              />
              <span className="text-[10px] font-medium text-slate-500">Conseiller</span>
            </label>
          }
        >
          <label className="flex cursor-pointer items-center gap-1.5">
            <input
              type="checkbox"
              checked={counselorValidated}
              disabled={isPending}
              onChange={toggleCounselor}
              className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
              aria-label={`Validation conseiller — ${subStep.title}`}
            />
            <span className="text-[10px] font-medium text-slate-600">Conseiller</span>
          </label>
        </PermissionGate>

        {pathway.doubleValidationEnabled && (
          <label
            className={cn(
              'flex items-center gap-1.5',
              isAdmin ? 'cursor-pointer' : 'cursor-not-allowed',
            )}
          >
            <input
              type="checkbox"
              checked={adminValidated}
              disabled={!isAdmin || isPending}
              onChange={isAdmin ? toggleAdmin : undefined}
              className={cn(
                'h-4 w-4 rounded border-slate-300 text-blue-600',
                isAdmin ? 'cursor-pointer focus:ring-blue-500' : 'cursor-not-allowed opacity-70',
              )}
              aria-label={`Validation administrateur — ${subStep.title}`}
            />
            <span className="text-[10px] font-medium text-slate-600">Admin</span>
          </label>
        )}
      </div>

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
          {pathway.doubleValidationEnabled && (
            <Badge variant="warning" className="text-[10px]">
              Double validation
            </Badge>
          )}
          {isOverdue && (
            <Badge variant="warning" className="text-[10px]">
              En retard
            </Badge>
          )}
          {pendingAdmin && (
            <Badge variant="warning" className="text-[10px]">
              En attente admin
            </Badge>
          )}
          {subStep.grandfatheredValidation && (
            <Badge variant="default" className="text-[10px]">
              Validée avant double validation
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
        {counselorValidated && subStep.counselorValidatedBy && (
          <p className="mt-1 text-xs text-emerald-700">
            Conseiller : {formatPathwayUser(subStep.counselorValidatedBy)}
          </p>
        )}
        {adminValidated && subStep.adminValidatedBy && (
          <p className="mt-0.5 text-xs text-blue-700">
            Admin : {formatPathwayUser(subStep.adminValidatedBy)}
          </p>
        )}
      </div>
    </li>
  )
}

function EditableStageBlock({
  stage,
  pathway,
  candidateId,
  isAdmin,
  pendingSubStepId,
  defaultOpen,
}: {
  stage: PathwayStage
  pathway: Pathway
  candidateId: string
  isAdmin: boolean
  pendingSubStepId: string | null
  defaultOpen: boolean
}) {
  const [open, setOpen] = useState(defaultOpen)
  const requiredSubSteps = stage.subSteps.filter((s) => s.required)
  const validatedCount = requiredSubSteps.filter((s) => s.validated).length

  return (
    <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
      <button
        type="button"
        onClick={() => setOpen((value) => !value)}
        className="flex w-full items-center gap-3 px-4 py-4 text-left transition-colors hover:bg-slate-50/80"
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
          {stage.description && (
            <p className="mt-0.5 line-clamp-1 text-xs text-slate-500">{stage.description}</p>
          )}
        </div>
        <div className="hidden w-24 sm:block">
          <Progress value={stage.progressPercent} className="h-1.5" />
        </div>
        <span className="text-sm font-bold tabular-nums text-slate-700">{stage.progressPercent}%</span>
      </button>
      {open && (
        <ul className="space-y-2 border-t border-slate-100 bg-slate-50/50 px-4 py-4">
          {stage.subSteps.map((subStep) => (
            <EditableSubStepRow
              key={subStep.id}
              subStep={subStep}
              pathway={pathway}
              candidateId={candidateId}
              isAdmin={isAdmin}
              pendingSubStepId={pendingSubStepId}
            />
          ))}
        </ul>
      )}
    </div>
  )
}

export function StaffEditablePathwayTimeline({
  pathway,
  candidateId,
}: {
  pathway: Pathway
  candidateId: string
}) {
  const { data: user } = useCurrentUser()
  const patchMutation = usePatchCandidatePathwaySubStep(candidateId)
  const isAdmin = user?.roles.some((role) => role === 'ADMIN' || role === 'SUPER_ADMIN') ?? false
  const firstIncompleteIndex = pathway.stages.findIndex((stage) => stage.progressPercent < 100)
  const pendingSubStepId = patchMutation.isPending ? (patchMutation.variables?.subStepId ?? null) : null

  return (
    <div className="space-y-3">
      {pathway.doubleValidationEnabled && (
        <p className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
          Double validation activée : cochez en tant que conseiller, puis l&apos;administrateur doit valider
          séparément pour que l&apos;étape compte dans la progression. Les étapes déjà cochées avant
          l&apos;activation restent validées.
        </p>
      )}
      {pathway.stages.map((stage, index) => (
        <EditableStageBlock
          key={stage.id}
          stage={stage}
          pathway={pathway}
          candidateId={candidateId}
          isAdmin={isAdmin}
          pendingSubStepId={pendingSubStepId}
          defaultOpen={
            index === firstIncompleteIndex
            || (firstIncompleteIndex === -1 && index === pathway.stages.length - 1)
          }
        />
      ))}
    </div>
  )
}
