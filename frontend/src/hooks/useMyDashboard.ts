import { useQuery } from '@tanstack/react-query'
import { fetchMyDashboard } from '@/lib/dashboard-api'
import { isAuthenticated } from '@/lib/auth'

export function useMyDashboard() {
  return useQuery({
    queryKey: ['my-dashboard'],
    queryFn: fetchMyDashboard,
    enabled: isAuthenticated(),
    staleTime: 2 * 60_000,
  })
}
