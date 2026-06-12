import { HOUSING_LISTINGS } from '@/components/candidate/candidate-demo-data'
import {
  CandidatePageLayout,
  CandidatePanel,
  CandidateSectionTitle,
} from '@/components/candidate/CandidatePageLayout'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'

export function CandidateHousingPage() {
  return (
    <CandidatePageLayout
      title="Recherche de logement"
      description="Explorez les options de logement proches de votre établissement et contactez votre conseillère."
    >
      <CandidatePanel>
        <CandidateSectionTitle className="mb-4">Rechercher</CandidateSectionTitle>
        <div className="grid gap-3 sm:grid-cols-3">
          <Input placeholder="Ville ou campus" defaultValue="Paris-Saclay" />
          <Input placeholder="Budget max (€/mois)" defaultValue="700" />
          <Input placeholder="Type (studio, colocation…)" defaultValue="Studio" />
        </div>
        <Button type="button" className="mt-4 bg-blue-600 hover:bg-blue-700">
          Lancer la recherche
        </Button>
      </CandidatePanel>

      <CandidatePanel>
        <CandidateSectionTitle className="mb-4">Propositions pour vous</CandidateSectionTitle>
        <ul className="space-y-3">
          {HOUSING_LISTINGS.map((listing) => (
            <li
              key={listing.title}
              className="flex flex-col gap-3 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-100 sm:flex-row sm:items-center sm:justify-between"
            >
              <div>
                <p className="font-medium text-slate-900">{listing.title}</p>
                <p className="mt-0.5 text-sm text-slate-500">
                  {listing.city} · {listing.type} · {listing.price}
                </p>
              </div>
              <div className="flex items-center gap-2">
                <span className="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-100">
                  {listing.status}
                </span>
                <Button type="button" size="sm" variant="outline">
                  Voir la fiche
                </Button>
              </div>
            </li>
          ))}
        </ul>
      </CandidatePanel>
    </CandidatePageLayout>
  )
}
