import { useQuery } from '@tanstack/react-query'
import {
  fetchMyPathways,
  findPathwayByCode,
  type Pathway,
  type PathwayCode,
} from '@/lib/pathways-api'

export function useMyPathways() {
  return useQuery({
    queryKey: ['me-pathways'],
    queryFn: fetchMyPathways,
    staleTime: 2 * 60_000,
  })
}

export function usePathway(code: PathwayCode): {
  pathway: Pathway | undefined
  isLoading: boolean
  isError: boolean
  studyApplicationTypeLabel: string | null
} {
  const query = useMyPathways()
  const pathway = query.data ? findPathwayByCode(query.data.pathways, code) : undefined

  return {
    pathway,
    isLoading: query.isLoading,
    isError: query.isError,
    studyApplicationTypeLabel: query.data?.studyApplicationTypeLabel ?? null,
  }
}
