import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import {
  AlertsBanner,
  CounselorCard,
  ErrorState,
  GlobalCompletionHero,
  LoadingState,
  ProcedureCards,
  SummaryStats,
} from '@/components/candidate/demarches/DemarchesComponents'
import { PathwayOverviewCards, StudyTypeBanner } from '@/components/candidate/demarches/PathwayComponents'
import { useMyPathways } from '@/hooks/useMyPathways'
import { fetchMyApplications } from '@/lib/demarches-api'
import { averagePathwayProgress, pathwayToProcedureCard, PATHWAY_ROUTES } from '@/lib/pathways-api'

export function DemarchesOverviewPage() {
  const pathwaysQuery = useMyPathways()
  const applicationsQuery = useQuery({
    queryKey: ['me-applications'],
    queryFn: fetchMyApplications,
  })

  const isLoading = pathwaysQuery.isLoading || applicationsQuery.isLoading
  const isError = pathwaysQuery.isError || applicationsQuery.isError

  if (isLoading) return <LoadingState />
  if (isError || !applicationsQuery.data) return <ErrorState />

  const applications = applicationsQuery.data
  const pathways = pathwaysQuery.data?.pathways ?? []
  const pathwayCards = pathways.map(pathwayToProcedureCard)

  const completion = {
    ...applications.completion,
    global: pathways.length
      ? Math.round((averagePathwayProgress(pathways) + applications.completion.global) / 2)
      : applications.completion.global,
    campusFrance: pathways.find((p) => p.code === 'campus_france')?.progressPercent ?? applications.completion.campusFrance,
    parcoursup: pathways.find((p) => p.code === 'parcoursup')?.progressPercent ?? applications.completion.parcoursup,
    parisSaclay: pathways.find((p) => p.code === 'paris_saclay')?.progressPercent ?? applications.completion.parisSaclay,
  }

  return (
    <div className="space-y-6">
      <StudyTypeBanner label={pathwaysQuery.data?.studyApplicationTypeLabel ?? null} />
      <AlertsBanner alerts={applications.alerts} />
      <GlobalCompletionHero completion={completion} referenceNumber={applications.referenceNumber} />
      <SummaryStats {...applications.summary} />

      <div>
        <h2 className="mb-4 text-base font-semibold text-slate-900">Vos parcours</h2>
        {pathways.length > 0 ? (
          <>
            <PathwayOverviewCards pathways={pathways} />
            <div className="mt-4 flex flex-wrap gap-3">
              {pathways.map((pathway) => (
                <Link
                  key={pathway.id}
                  to={PATHWAY_ROUTES[pathway.code]}
                  className="text-sm font-medium text-blue-600 hover:text-blue-700"
                >
                  Voir le détail {pathway.name} →
                </Link>
              ))}
            </div>
          </>
        ) : (
          <ProcedureCards procedures={pathwayCards.length ? pathwayCards : [
            applications.procedures.campusFrance,
            applications.procedures.parcoursup,
            applications.procedures.parisSaclay,
          ]} />
        )}
      </div>

      <CounselorCard counselor={applications.counselor} />
    </div>
  )
}
