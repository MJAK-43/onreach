import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import {
  CheckCircle2,
  ChevronDown,
  ChevronRight,
  Circle,
  Clock,
  Lock,
} from 'lucide-react'
import { PermissionGate } from '@/components/auth/PermissionGate'
import { StudyTypeBanner } from '@/components/candidate/demarches/PathwayComponents'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import {
  useCandidatePathways,
  usePatchCandidatePathway,
  usePatchCandidatePathwaySubStep,
} from '@/hooks/useCandidatePathways'
import type { Pathway, PathwayStage, PathwaySubStep } from '@/lib/pathways-api'
import {
  countValidatedSubSteps,
  formatPathwayUser,
  getNextPendingSubStep,
  isAdminValidated,
  isCounselorValidated,
} from '@/lib/pathways-api'
import { fetchCandidatePathwayAudit } from '@/lib/notifications-api'
import { cn } from '@/lib/utils'

function statusBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'info' | 'default' {
  if (status === 'accepted') return 'success'
  if (status === 'in_progress') return 'info'
  if (status === 'blocked' || status === 'refused') return 'danger'
  if (status === 'not_started') return 'default'
  return 'warning'
}

function StaffPathwaySummary({ pathway }: { pathway: Pathway }) {
  const next = getNextPendingSubStep(pathway)
  const { done, total } = countValidatedSubSteps(pathway)

  return (
    <div className="rounded-xl border border-border bg-muted/30 p-4">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-2">
            <h3 className="font-semibold">{pathway.name}</h3>
            <Badge variant={statusBadgeVariant(pathway.status)}>{pathway.statusLabel}</Badge>
            {pathway.doubleValidationEnabled && (
              <Badge variant="warning" className="text-[10px]">
                Double validation
              </Badge>
            )}
          </div>
          {pathway.blockedReason && (
            <p className="mt-2 flex items-start gap-2 text-sm text-red-600">
              <Lock className="mt-0.5 h-4 w-4 shrink-0" />
              {pathway.blockedReason}
            </p>
          )}
          {next && (
            <p className="mt-2 text-sm text-muted-foreground">
              Prochaine étape : <span className="font-medium text-foreground">{next.title}</span>
            </p>
          )}
          <p className="mt-1 text-xs text-muted-foreground">
            {done}/{total} sous-étapes validées
          </p>
        </div>
        <div className="w-full sm:w-36">
          <div className="mb-1 flex justify-between text-xs text-muted-foreground">
            <span>Progression</span>
            <span className="font-semibold tabular-nums text-foreground">{pathway.progressPercent}%</span>
          </div>
          <Progress value={pathway.progressPercent} className="h-2" />
        </div>
      </div>
    </div>
  )
}

function StaffSubStepRow({
  subStep,
  pathway,
  isAdmin,
  onValidate,
  onInvalidate,
  onAdminValidate,
  onAdminInvalidate,
  isPending,
}: {
  subStep: PathwaySubStep
  pathway: Pathway
  isAdmin: boolean
  onValidate: (subStepId: string) => void
  onInvalidate: (subStepId: string) => void
  onAdminValidate: (subStepId: string) => void
  onAdminInvalidate: (subStepId: string) => void
  isPending: boolean
}) {
  const counselorValidated = isCounselorValidated(subStep)
  const adminValidated = isAdminValidated(subStep)
  const isOverdue =
    subStep.dueDate && !subStep.validated && new Date(subStep.dueDate) < new Date(new Date().toDateString())

  return (
    <li
      className={cn(
        'rounded-lg border p-4',
        subStep.validated
          ? 'border-emerald-200 bg-emerald-50/50'
          : counselorValidated
            ? 'border-amber-200 bg-amber-50/40'
            : isOverdue
              ? 'border-orange-200 bg-orange-50/40'
              : 'border-border bg-background',
      )}
    >
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div className="flex min-w-0 flex-1 items-start gap-3">
          {subStep.validated ? (
            <CheckCircle2 className="mt-0.5 h-4 w-4 shrink-0 text-emerald-600" />
          ) : counselorValidated ? (
            <Clock className="mt-0.5 h-4 w-4 shrink-0 text-amber-600" />
          ) : (
            <Circle className="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground/40" />
          )}
          <div className="min-w-0 flex-1">
            <div className="flex flex-wrap items-center gap-2">
              <p className="text-sm font-medium">{subStep.title}</p>
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
              {counselorValidated && !subStep.validated && pathway.doubleValidationEnabled && (
                <Badge variant="warning" className="text-[10px]">
                  En attente admin
                </Badge>
              )}
            </div>
            {subStep.description && (
              <p className="mt-0.5 text-xs text-muted-foreground">{subStep.description}</p>
            )}
            {subStep.dueDate && (
              <p className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                <Clock className="h-3 w-3" />
                Échéance {new Date(subStep.dueDate).toLocaleDateString('fr-FR')}
              </p>
            )}
            {counselorValidated && subStep.counselorValidatedBy && (
              <p className="mt-1 text-xs text-emerald-700">
                Validé conseiller par {formatPathwayUser(subStep.counselorValidatedBy)}
                {subStep.counselorValidatedAt &&
                  ` le ${new Date(subStep.counselorValidatedAt).toLocaleDateString('fr-FR')}`}
              </p>
            )}
            {subStep.adminValidatedBy && (
              <p className="mt-0.5 text-xs text-blue-700">
                Validé admin par {formatPathwayUser(subStep.adminValidatedBy)}
              </p>
            )}
          </div>
        </div>

        <PermissionGate permission="applications.edit">
          <div className="flex shrink-0 flex-col gap-2 sm:flex-row">
            {!counselorValidated ? (
              <Button
                type="button"
                size="sm"
                disabled={isPending}
                onClick={() => onValidate(subStep.id)}
              >
                Valider conseiller
              </Button>
            ) : (
              <Button
                type="button"
                size="sm"
                variant="outline"
                disabled={isPending}
                onClick={() => onInvalidate(subStep.id)}
              >
                Retirer conseiller
              </Button>
            )}
            {isAdmin && pathway.doubleValidationEnabled && (
              !adminValidated ? (
                <Button
                  type="button"
                  size="sm"
                  variant="default"
                  disabled={isPending}
                  onClick={() => onAdminValidate(subStep.id)}
                >
                  Valider admin
                </Button>
              ) : (
                <Button
                  type="button"
                  size="sm"
                  variant="outline"
                  disabled={isPending}
                  onClick={() => onAdminInvalidate(subStep.id)}
                >
                  Retirer admin
                </Button>
              )
            )}
          </div>
        </PermissionGate>
      </div>
    </li>
  )
}

function StaffStageBlock({
  stage,
  pathway,
  isAdmin,
  onValidate,
  onInvalidate,
  onAdminValidate,
  onAdminInvalidate,
  isPendingSubStepId,
  defaultOpen,
}: {
  stage: PathwayStage
  pathway: Pathway
  isAdmin: boolean
  onValidate: (subStepId: string) => void
  onInvalidate: (subStepId: string) => void
  onAdminValidate: (subStepId: string) => void
  onAdminInvalidate: (subStepId: string) => void
  isPendingSubStepId: string | null
  defaultOpen: boolean
}) {
  const [open, setOpen] = useState(defaultOpen)
  const requiredSubSteps = stage.subSteps.filter((s) => s.required)
  const validatedCount = requiredSubSteps.filter((s) => s.validated).length

  return (
    <div className="overflow-hidden rounded-xl border border-border">
      <button
        type="button"
        onClick={() => setOpen((value) => !value)}
        className="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-muted/50"
      >
        {open ? (
          <ChevronDown className="h-4 w-4 shrink-0 text-muted-foreground" />
        ) : (
          <ChevronRight className="h-4 w-4 shrink-0 text-muted-foreground" />
        )}
        <div className="min-w-0 flex-1">
          <div className="flex flex-wrap items-center gap-2">
            <h4 className="font-medium">{stage.title}</h4>
            <span className="text-xs tabular-nums text-muted-foreground">
              {validatedCount}/{requiredSubSteps.length || stage.subSteps.length}
            </span>
          </div>
        </div>
        <span className="text-sm font-semibold tabular-nums">{stage.progressPercent}%</span>
      </button>
      {open && (
        <ul className="space-y-2 border-t border-border bg-muted/20 p-4">
          {stage.subSteps.map((subStep) => (
            <StaffSubStepRow
              key={subStep.id}
              subStep={subStep}
              pathway={pathway}
              isAdmin={isAdmin}
              onValidate={onValidate}
              onInvalidate={onInvalidate}
              onAdminValidate={onAdminValidate}
              onAdminInvalidate={onAdminInvalidate}
              isPending={isPendingSubStepId === subStep.id}
            />
          ))}
        </ul>
      )}
    </div>
  )
}

function PathwayStatusControls({
  pathway,
  candidateId,
}: {
  pathway: Pathway
  candidateId: string
}) {
  const statusMutation = usePatchCandidatePathway(candidateId)
  const [reason, setReason] = useState(pathway.blockedReason ?? '')

  return (
    <PermissionGate permission="applications.edit">
      <div className="rounded-xl border border-border p-4">
        <p className="text-sm font-medium">Statut du parcours</p>
        <div className="mt-3 flex flex-wrap gap-2">
          {pathway.status !== 'in_progress' && (
            <Button
              size="sm"
              variant="outline"
              disabled={statusMutation.isPending}
              onClick={() => statusMutation.mutate({ pathwayId: pathway.id, payload: { status: 'in_progress' } })}
            >
              Reprendre
            </Button>
          )}
          {pathway.status !== 'blocked' && (
            <>
              <Input
                placeholder="Motif de blocage (obligatoire)"
                value={reason}
                onChange={(event) => setReason(event.target.value)}
                className="max-w-md"
              />
              <Button
                size="sm"
                variant="outline"
                className="border-red-300 text-red-700 hover:bg-red-50"
                disabled={statusMutation.isPending || reason.trim() === ''}
                onClick={() =>
                  statusMutation.mutate({
                    pathwayId: pathway.id,
                    payload: { status: 'blocked', blockedReason: reason.trim() },
                  })
                }
              >
                Bloquer
              </Button>
            </>
          )}
        </div>
      </div>
    </PermissionGate>
  )
}

function StaffPathwayPanel({ pathway, candidateId }: { pathway: Pathway; candidateId: string }) {
  const patchMutation = usePatchCandidatePathwaySubStep(candidateId)
  const { data: user } = useCurrentUser()
  const isAdmin = user?.roles.some((role) => role === 'ADMIN' || role === 'SUPER_ADMIN') ?? false
  const firstIncompleteIndex = pathway.stages.findIndex((stage) => stage.progressPercent < 100)
  const pendingSubStepId = patchMutation.isPending ? (patchMutation.variables?.subStepId ?? null) : null

  const handleValidate = (subStepId: string) => {
    patchMutation.mutate({
      pathwayId: pathway.id,
      subStepId,
      payload: { counselorValidated: true },
    })
  }

  const handleInvalidate = (subStepId: string) => {
    patchMutation.mutate({
      pathwayId: pathway.id,
      subStepId,
      payload: { counselorValidated: false },
    })
  }

  const handleAdminValidate = (subStepId: string) => {
    patchMutation.mutate({
      pathwayId: pathway.id,
      subStepId,
      payload: { adminValidated: true },
    })
  }

  const handleAdminInvalidate = (subStepId: string) => {
    patchMutation.mutate({
      pathwayId: pathway.id,
      subStepId,
      payload: { adminValidated: false },
    })
  }

  return (
    <div className="space-y-4">
      <StaffPathwaySummary pathway={pathway} />
      <PathwayStatusControls pathway={pathway} candidateId={candidateId} />
      <div className="space-y-3">
        {pathway.stages.map((stage, index) => (
          <StaffStageBlock
            key={stage.id}
            stage={stage}
            pathway={pathway}
            isAdmin={isAdmin}
            onValidate={handleValidate}
            onInvalidate={handleInvalidate}
            onAdminValidate={handleAdminValidate}
            onAdminInvalidate={handleAdminInvalidate}
            isPendingSubStepId={pendingSubStepId}
            defaultOpen={
              index === firstIncompleteIndex
              || (firstIncompleteIndex === -1 && index === pathway.stages.length - 1)
            }
          />
        ))}
      </div>
    </div>
  )
}

function PathwayAuditPanel({ candidateId }: { candidateId: string }) {
  const auditQuery = useQuery({
    queryKey: ['candidate-pathway-audit', candidateId],
    queryFn: () => fetchCandidatePathwayAudit(candidateId),
  })

  return (
    <Card>
      <CardHeader>
        <CardTitle>Journal d&apos;audit parcours</CardTitle>
        <CardDescription>Historique des validations et attributions</CardDescription>
      </CardHeader>
      <CardContent>
        {auditQuery.isLoading && <p className="text-sm text-muted-foreground">Chargement...</p>}
        {auditQuery.isError && (
          <p className="text-sm text-red-600">Impossible de charger l&apos;audit.</p>
        )}
        {auditQuery.data && auditQuery.data.items.length === 0 && (
          <p className="text-sm text-muted-foreground">Aucun événement enregistré.</p>
        )}
        {auditQuery.data && auditQuery.data.items.length > 0 && (
          <ul className="space-y-3">
            {auditQuery.data.items.map((entry) => (
              <li key={entry.id} className="rounded-lg border border-border p-3 text-sm">
                <div className="flex flex-wrap items-start justify-between gap-2">
                  <p className="font-medium">{entry.description}</p>
                  <Badge variant="default" className="text-[10px]">
                    {entry.actionLabel}
                  </Badge>
                </div>
                <p className="mt-1 text-xs text-muted-foreground">
                  {new Date(entry.occurredAt).toLocaleString('fr-FR')}
                  {entry.performedBy
                    ? ` — ${entry.performedBy.firstName} ${entry.performedBy.lastName}`
                    : ''}
                </p>
              </li>
            ))}
          </ul>
        )}
      </CardContent>
    </Card>
  )
}

export function StaffPathwaysTab({ candidateId }: { candidateId: string }) {
  const pathwaysQuery = useCandidatePathways(candidateId)
  const [activePathwayId, setActivePathwayId] = useState<string | null>(null)

  if (pathwaysQuery.isLoading) {
    return <p className="text-sm text-muted-foreground">Chargement des parcours...</p>
  }

  if (pathwaysQuery.isError) {
    return <p className="text-sm text-red-600">Impossible de charger les parcours du candidat.</p>
  }

  const pathways = pathwaysQuery.data?.pathways ?? []
  const selectedId = activePathwayId ?? pathways[0]?.id ?? null
  const selectedPathway = pathways.find((pathway) => pathway.id === selectedId)

  return (
    <div className="space-y-4">
      <StudyTypeBanner label={pathwaysQuery.data?.studyApplicationTypeLabel ?? null} />

      <Card>
        <CardHeader>
          <CardTitle>Parcours de candidature</CardTitle>
          <CardDescription>
            Validation conseiller — {pathways.length} parcours assigné(s)
          </CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          {pathways.length === 0 ? (
            <p className="text-sm text-muted-foreground">
              Aucun parcours assigné. Vérifiez le type de candidature du dossier.
            </p>
          ) : (
            <>
              {pathways.length > 1 && (
                <div className="flex flex-wrap gap-2">
                  {pathways.map((pathway) => (
                    <button
                      key={pathway.id}
                      type="button"
                      onClick={() => setActivePathwayId(pathway.id)}
                      className={cn(
                        'rounded-lg px-3 py-1.5 text-sm font-medium transition-colors',
                        selectedId === pathway.id
                          ? 'bg-primary text-primary-foreground'
                          : 'bg-muted text-muted-foreground hover:text-foreground',
                      )}
                    >
                      {pathway.name}
                    </button>
                  ))}
                </div>
              )}
              {selectedPathway && (
                <StaffPathwayPanel pathway={selectedPathway} candidateId={candidateId} />
              )}
            </>
          )}
        </CardContent>
      </Card>

      <PathwayAuditPanel candidateId={candidateId} />
    </div>
  )
}
