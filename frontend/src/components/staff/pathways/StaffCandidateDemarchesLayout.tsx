import { Link, NavLink, Outlet, useParams } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import { ArrowLeft } from 'lucide-react'
import { StudyTypeBanner } from '@/components/candidate/demarches/PathwayComponents'
import { Button } from '@/components/ui/button'
import { useCandidatePathways } from '@/hooks/useCandidatePathways'
import { fetchCandidate } from '@/lib/api'
import {
  PATHWAY_LABELS,
  staffPathwayRoute,
  type PathwayCode,
} from '@/lib/pathways-api'
import { cn } from '@/lib/utils'

const PATHWAY_ORDER_LIST: PathwayCode[] = ['parcoursup', 'campus_france', 'paris_saclay']

function NavItems({
  items,
  mobile,
}: {
  items: Array<{ to: string; label: string; end?: boolean }>
  mobile?: boolean
}) {
  return (
    <>
      {items.map((item) => (
        <NavLink
          key={item.to}
          to={item.to}
          end={item.end}
          className={({ isActive }) =>
            cn(
              mobile
                ? 'shrink-0 rounded-lg px-3 py-2 text-xs font-medium transition-colors'
                : 'flex rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
              isActive
                ? mobile
                  ? 'bg-white text-slate-900 shadow-sm'
                  : 'bg-blue-50 text-blue-700'
                : mobile
                  ? 'text-slate-500 hover:text-slate-800'
                  : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
            )
          }
        >
          {item.label}
        </NavLink>
      ))}
    </>
  )
}

export function StaffCandidateDemarchesLayout() {
  const { candidateId = '' } = useParams()
  const pathwaysQuery = useCandidatePathways(candidateId)
  const candidateQuery = useQuery({
    queryKey: ['candidate', candidateId],
    queryFn: () => fetchCandidate(candidateId),
    enabled: Boolean(candidateId),
  })

  const pathwayCodes = pathwaysQuery.data?.pathways.map((p) => p.code) ?? []
  const navItems = PATHWAY_ORDER_LIST.filter((code) => pathwayCodes.includes(code)).map((code) => ({
    to: staffPathwayRoute(candidateId, code),
    label: PATHWAY_LABELS[code],
  }))

  const candidate = candidateQuery.data

  return (
    <div className="mx-auto max-w-[1200px] space-y-6">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <Button variant="ghost" size="sm" className="mb-2 -ml-2 text-muted-foreground" asChild>
            <Link to="/pathways/tracking">
              <ArrowLeft className="mr-1 h-4 w-4" />
              Retour au suivi
            </Link>
          </Button>
          <p className="text-xs font-semibold uppercase tracking-wider text-blue-600">Suivi des candidatures</p>
          <h1 className="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
            {candidate ? `${candidate.firstName} ${candidate.lastName}` : 'Candidat'}
          </h1>
          <p className="mt-1.5 max-w-2xl text-sm text-slate-500">
            {pathwaysQuery.data?.studyApplicationTypeLabel
              ? `Parcours ${pathwaysQuery.data.studyApplicationTypeLabel.toLowerCase()} — vue identique au portail candidat, avec validation des étapes.`
              : 'Vue détaillée du parcours — cocher ou décocher les sous-étapes pour mettre à jour la progression.'}
          </p>
          {candidate?.referenceNumber && (
            <p className="mt-1 text-xs text-slate-400">Dossier {candidate.referenceNumber}</p>
          )}
        </div>
        {candidate && (
          <Button variant="outline" size="sm" asChild>
            <Link to={`/candidates/${candidateId}`}>Fiche candidat</Link>
          </Button>
        )}
      </div>

      <StudyTypeBanner label={pathwaysQuery.data?.studyApplicationTypeLabel ?? null} />

      <nav className="flex gap-1 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50/80 p-1 lg:hidden">
        <NavItems items={navItems} mobile />
      </nav>

      <div className="grid gap-6 lg:grid-cols-[220px_1fr]">
        <aside className="hidden lg:block">
          <nav className="sticky top-24 space-y-0.5 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
            <NavItems items={navItems} />
          </nav>
        </aside>

        <div className="min-w-0">
          <Outlet />
        </div>
      </div>
    </div>
  )
}
