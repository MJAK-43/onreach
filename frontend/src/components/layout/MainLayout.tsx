import { Outlet } from 'react-router-dom'
import { CandidateShell } from '@/components/layout/candidate/CandidateShell'
import { StaffShell } from '@/components/layout/staff/StaffShell'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { getPrimaryRole } from '@/lib/roles'

export function MainLayout() {
  const { data: user, isLoading } = useCurrentUser()
  const isCandidate = !isLoading && getPrimaryRole(user?.roles) === 'CANDIDATE'

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
