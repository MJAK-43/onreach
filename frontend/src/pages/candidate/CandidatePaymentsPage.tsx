import { PAYMENT_TRANCHES } from '@/components/candidate/candidate-demo-data'
import {
  CandidatePageLayout,
  CandidatePanel,
  CandidateSectionTitle,
  PaymentDonut,
  TrancheRow,
} from '@/components/candidate/CandidatePageLayout'
import { Button } from '@/components/ui/button'

export function CandidatePaymentsPage() {
  return (
    <CandidatePageLayout
      title="Paiements"
      description="Suivez vos tranches de paiement et réglez la prochaine échéance en toute sécurité."
    >
      <CandidatePanel>
        <div className="mb-4 flex flex-wrap items-baseline justify-between gap-2">
          <CandidateSectionTitle>Synthèse</CandidateSectionTitle>
          <span className="text-2xl font-bold tabular-nums text-slate-900">750 €</span>
        </div>
        <div className="flex flex-col gap-6 sm:flex-row sm:items-center">
          <PaymentDonut percent={66} paid={500} />
          <div className="min-w-0 flex-1 space-y-3">
            {PAYMENT_TRANCHES.map((tranche) => (
              <TrancheRow key={tranche.label} {...tranche} />
            ))}
          </div>
        </div>
      </CandidatePanel>

      <CandidatePanel>
        <CandidateSectionTitle className="mb-2">Prochaine échéance</CandidateSectionTitle>
        <p className="text-sm text-slate-600">
          Tranche 3 — Visa : <strong>250 €</strong> · échéance recommandée avant le 10 juin 2024.
        </p>
        <Button type="button" className="mt-4 bg-blue-600 hover:bg-blue-700">
          Payer la tranche 3
        </Button>
      </CandidatePanel>
    </CandidatePageLayout>
  )
}
