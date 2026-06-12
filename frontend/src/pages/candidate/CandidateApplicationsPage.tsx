import { PARIS_SACLAY_STEPS, PARCOURSUP_WISHES } from '@/components/candidate/candidate-demo-data'
import {
  CandidatePageLayout,
  CandidatePanel,
  CandidateSectionTitle,
} from '@/components/candidate/CandidatePageLayout'

export function CandidateApplicationsPage() {
  return (
    <CandidatePageLayout
      title="Parcoursup / Paris-Saclay"
      description="Suivez vos vœux Parcoursup et l'avancement de votre candidature Paris-Saclay."
    >
      <CandidatePanel>
        <CandidateSectionTitle className="mb-4">Parcoursup — Mes vœux</CandidateSectionTitle>
        <ul className="divide-y divide-slate-100">
          {PARCOURSUP_WISHES.map((wish) => (
            <li key={wish.rank} className="flex items-start justify-between gap-4 py-3 first:pt-0 last:pb-0">
              <div>
                <p className="text-xs font-medium uppercase tracking-wide text-slate-400">
                  Vœu n°{wish.rank}
                </p>
                <p className="mt-0.5 text-sm font-medium text-slate-800">{wish.program}</p>
              </div>
              <span
                className={`shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold ${
                  wish.status === 'Admis'
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
                    : wish.status.includes('attente')
                      ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-100'
                      : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200'
                }`}
              >
                {wish.status}
              </span>
            </li>
          ))}
        </ul>
      </CandidatePanel>

      <CandidatePanel>
        <CandidateSectionTitle className="mb-4">Paris-Saclay — Dossier</CandidateSectionTitle>
        <ul className="space-y-3">
          {PARIS_SACLAY_STEPS.map((step) => (
            <li
              key={step.step}
              className="flex items-center justify-between gap-4 rounded-xl bg-slate-50 px-4 py-3 ring-1 ring-slate-100"
            >
              <div>
                <p className="text-sm font-medium text-slate-800">{step.step}</p>
                {step.date && <p className="text-xs text-slate-400">{step.date}</p>}
              </div>
              <span
                className={`rounded-full px-2.5 py-1 text-[11px] font-semibold ${
                  step.status === 'done'
                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
                    : step.status === 'current'
                      ? 'bg-orange-50 text-orange-700 ring-1 ring-orange-100'
                      : 'bg-slate-100 text-slate-500 ring-1 ring-slate-200'
                }`}
              >
                {step.status === 'done' ? 'Terminé' : step.status === 'current' ? 'En cours' : 'À venir'}
              </span>
            </li>
          ))}
        </ul>
      </CandidatePanel>
    </CandidatePageLayout>
  )
}
