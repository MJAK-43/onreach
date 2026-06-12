import { CandidateProcedureTimeline } from '@/components/candidate/CandidateProcedureTimeline'
import {
  CandidatePageLayout,
  CandidatePanel,
  CandidateSectionTitle,
} from '@/components/candidate/CandidatePageLayout'
import { useCandidateDossier } from '@/hooks/useCandidateDossier'
import { STATUS_LABELS } from '@/lib/candidate-utils'
import { Button } from '@/components/ui/button'

const PROCEDURE_STEPS = [
  { title: 'Constituer le dossier', detail: 'Pièces d\'identité, relevés de notes, attestations de langue.' },
  { title: 'Dépôt Campus France', detail: 'Validation du projet d\'études et entretien pédagogique.' },
  { title: 'Admission', detail: 'Réception de la lettre d\'admission de l\'établissement.' },
  { title: 'Demande de visa', detail: 'Prise de rendez-vous et dépôt au consulat.' },
  { title: 'Installation', detail: 'Logement, assurance, inscription administrative.' },
]

export function CandidateProcedurePage() {
  const { dossier, isLoading } = useCandidateDossier()
  const status = dossier?.status ?? 'admission_obtained'

  if (isLoading) {
    return <p className="text-sm text-slate-500">Chargement...</p>
  }

  return (
    <CandidatePageLayout
      title="Procédure & Étapes"
      description="Visualisez l'avancement de votre parcours et les prochaines actions à mener."
    >
      <CandidatePanel>
        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
          <CandidateSectionTitle>Avancement global</CandidateSectionTitle>
          <span className="rounded-full bg-orange-50 px-3 py-1 text-sm font-medium text-orange-800 ring-1 ring-orange-100">
            {STATUS_LABELS[status] ?? 'En cours'}
          </span>
        </div>
        <CandidateProcedureTimeline status={status} />
      </CandidatePanel>

      <CandidatePanel>
        <CandidateSectionTitle className="mb-4">Étapes détaillées</CandidateSectionTitle>
        <ol className="space-y-4">
          {PROCEDURE_STEPS.map((step, index) => (
            <li key={step.title} className="flex gap-4">
              <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-sm font-bold text-blue-700 ring-1 ring-blue-100">
                {index + 1}
              </span>
              <div>
                <p className="font-medium text-slate-900">{step.title}</p>
                <p className="mt-0.5 text-sm text-slate-500">{step.detail}</p>
              </div>
            </li>
          ))}
        </ol>
      </CandidatePanel>

      {(status === 'admission_obtained' || !dossier) && (
        <div className="flex flex-col gap-3 rounded-2xl bg-orange-50/80 px-5 py-4 ring-1 ring-orange-100 sm:flex-row sm:items-center sm:justify-between">
          <p className="text-sm text-orange-900">
            Votre admission a été enregistrée. Passez à la demande de visa avec l&apos;aide de votre conseillère.
          </p>
          <Button
            type="button"
            size="sm"
            variant="outline"
            className="shrink-0 border-orange-200 bg-white text-orange-800 hover:bg-orange-50"
          >
            Préparer ma demande de visa
          </Button>
        </div>
      )}
    </CandidatePageLayout>
  )
}
