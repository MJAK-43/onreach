import { useQuery } from '@tanstack/react-query'
import { CandidatePanel, CandidateSectionTitle } from '@/components/candidate/CandidatePageLayout'
import { ErrorState, LoadingState, TimelineList } from '@/components/candidate/demarches/DemarchesComponents'
import { fetchMyTimeline } from '@/lib/demarches-api'

export function DemarchesHistoryPage() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ['me-timeline'],
    queryFn: fetchMyTimeline,
  })

  if (isLoading) return <LoadingState />
  if (isError) return <ErrorState />

  return (
    <CandidatePanel>
      <CandidateSectionTitle>Historique global</CandidateSectionTitle>
      <p className="mt-1 text-sm text-slate-500">
        Toutes les actions sur votre dossier — documents, validations, changements de statut.
      </p>
      <div className="mt-6">
        <TimelineList entries={data ?? []} />
      </div>
    </CandidatePanel>
  )
}
