import { useEffect } from 'react'
import { Outlet } from 'react-router-dom'
import { useQueryClient } from '@tanstack/react-query'
import { CandidateShell } from '@/components/layout/candidate/CandidateShell'
import { StaffShell } from '@/components/layout/staff/StaffShell'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { fetchMyDashboard } from '@/lib/dashboard-api'
import { isAuthenticated } from '@/lib/auth'
import { getPrimaryRole } from '@/lib/roles'

export function MainLayout() {
  const queryClient = useQueryClient()
  const { data: user } = useCurrentUser()
  const isCandidate = getPrimaryRole(user?.roles) === 'CANDIDATE'

  useEffect(() => {
    if (isCandidate && isAuthenticated()) {
      void queryClient.prefetchQuery({
        queryKey: ['my-dashboard'],
        queryFn: fetchMyDashboard,
        staleTime: 2 * 60_000,
      })
    }
  }, [isCandidate, queryClient])

  if (isCandidate) {
    return (
      <CandidateShell>
        <Outlet />
      </CandidateShell>
    )
  }

  return (
    <StaffShell>
      <Outlet />
    </StaffShell>
  )
}
