import { ExternalLink } from 'lucide-react'
import { USEFUL_LINKS } from '@/components/candidate/candidate-demo-data'
import {
  CandidatePageLayout,
  CandidatePanel,
  CandidateSectionTitle,
} from '@/components/candidate/CandidatePageLayout'

export function CandidateLinksPage() {
  const grouped = USEFUL_LINKS.reduce<Record<string, typeof USEFUL_LINKS>>((acc, link) => {
    if (!acc[link.category]) acc[link.category] = []
    acc[link.category].push(link)
    return acc
  }, {})

  return (
    <CandidatePageLayout
      title="Liens utiles"
      description="Accédez rapidement aux sites officiels et ressources de confiance pour votre projet d'études."
    >
      {Object.entries(grouped).map(([category, links]) => (
        <CandidatePanel key={category}>
          <CandidateSectionTitle className="mb-4">{category}</CandidateSectionTitle>
          <ul className="space-y-2">
            {links.map((link) => (
              <li key={link.url}>
                <a
                  href={link.url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-4 py-3 text-sm font-medium text-slate-800 ring-1 ring-slate-100 transition-colors hover:bg-blue-50 hover:text-blue-800"
                >
                  {link.label}
                  <ExternalLink className="h-4 w-4 shrink-0 text-slate-400" />
                </a>
              </li>
            ))}
          </ul>
        </CandidatePanel>
      ))}
    </CandidatePageLayout>
  )
}
