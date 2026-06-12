import { useQuery } from '@tanstack/react-query'
import { fetchCandidates } from '@/lib/api'

export function useCandidateDossier() {
  const query = useQuery({
    queryKey: ['candidates'],
    queryFn: fetchCandidates,
  })

  return {
    ...query,
    dossier: query.data?.[0],
  }
}
