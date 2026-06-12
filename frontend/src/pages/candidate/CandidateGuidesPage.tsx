import { Link } from 'react-router-dom'
import { GUIDE_ARTICLES, GUIDE_CARDS } from '@/components/candidate/candidate-demo-data'
import {
  CandidatePageLayout,
  CandidatePanel,
  CandidateSectionTitle,
} from '@/components/candidate/CandidatePageLayout'

export function CandidateGuidesPage() {
  return (
    <CandidatePageLayout
      title="Guides & Ressources"
      description="Retrouvez les guides pratiques pour préparer votre dossier, votre visa et votre installation en France."
    >
      <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        {GUIDE_CARDS.map((guide) => (
          <Link
            key={guide.title}
            to={guide.to}
            className={`flex min-h-[130px] flex-col justify-between rounded-2xl bg-gradient-to-br ${guide.color} p-5 text-white shadow-sm transition-shadow hover:shadow-md`}
          >
            <div>
              <h3 className="font-semibold leading-snug">{guide.title}</h3>
              <p className="mt-1 text-sm text-white/75">{guide.subtitle}</p>
            </div>
            <span className="mt-3 text-sm font-medium text-white/90">{guide.link} →</span>
          </Link>
        ))}
      </div>

      <CandidatePanel>
        <CandidateSectionTitle className="mb-4">Articles recommandés</CandidateSectionTitle>
        <ul className="divide-y divide-slate-100">
          {GUIDE_ARTICLES.map((article) => (
            <li key={article.title} className="flex items-center justify-between gap-4 py-3 first:pt-0 last:pb-0">
              <div>
                <p className="text-sm font-medium text-slate-800">{article.title}</p>
                <p className="mt-0.5 text-xs text-slate-400">
                  {article.category} · {article.duration} de lecture
                </p>
              </div>
              <span className="shrink-0 rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-medium text-blue-700 ring-1 ring-blue-100">
                Lire
              </span>
            </li>
          ))}
        </ul>
      </CandidatePanel>
    </CandidatePageLayout>
  )
}
