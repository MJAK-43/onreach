import { useQuery } from '@tanstack/react-query'
import {
  CompletionPanel,
  DossierError,
  DossierLoading,
  OverviewHero,
} from '@/components/candidate/dossier/DossierComponents'
import { fetchMyProfile } from '@/lib/profile-api'

export function DossierOverviewPage() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ['my-profile'],
    queryFn: fetchMyProfile,
  })

  if (isLoading) {
    return <DossierLoading />
  }
  if (isError || !data) {
    return <DossierError />
  }

  return (
    <div className="space-y-6">
      <OverviewHero profile={data} />
      <CompletionPanel completion={data.completion} />
    </div>
  )
}
