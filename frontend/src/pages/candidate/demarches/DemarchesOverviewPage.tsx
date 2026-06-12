import { useQuery } from '@tanstack/react-query'
import {
  AlertsBanner,
  CounselorCard,
  ErrorState,
  GlobalCompletionHero,
  KpiGrid,
  LoadingState,
  ProcedureCards,
  SummaryStats,
} from '@/components/candidate/demarches/DemarchesComponents'
import { fetchMyApplications } from '@/lib/demarches-api'

export function DemarchesOverviewPage() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ['me-applications'],
    queryFn: fetchMyApplications,
  })

  if (isLoading) return <LoadingState />
  if (isError || !data) return <ErrorState />

  const procedures = [
    data.procedures.campusFrance,
    data.procedures.parcoursup,
    data.procedures.parisSaclay,
  ]

  return (
    <div className="space-y-6">
      <AlertsBanner alerts={data.alerts} />
      <GlobalCompletionHero completion={data.completion} referenceNumber={data.referenceNumber} />
      <KpiGrid completion={data.completion} />
      <SummaryStats {...data.summary} />
      <div>
        <h2 className="mb-4 text-base font-semibold text-slate-900">Vos procédures</h2>
        <ProcedureCards procedures={procedures} />
      </div>
      <CounselorCard counselor={data.counselor} />
    </div>
  )
}
