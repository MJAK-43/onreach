import { useQuery } from '@tanstack/react-query'
import { getCurrentUser } from '@/lib/api'
import { isAuthenticated } from '@/lib/auth'

export function useCurrentUser() {
  return useQuery({
    queryKey: ['currentUser'],
    queryFn: getCurrentUser,
    enabled: isAuthenticated(),
    staleTime: 5 * 60_000,
  })
}
