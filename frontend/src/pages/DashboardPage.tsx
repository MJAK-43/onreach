import { useCurrentUser } from '@/hooks/useCurrentUser'
import { getPrimaryRole } from '@/lib/roles'
import { CandidateDashboardPage } from '@/pages/dashboard/CandidateDashboardPage'
import { StaffDashboardPage } from '@/pages/dashboard/StaffDashboardPage'

export function DashboardPage() {
  const { data: user } = useCurrentUser()
  const role = getPrimaryRole(user?.roles)

  if (role === 'CANDIDATE') {
    return <CandidateDashboardPage />
  }

  return <StaffDashboardPage />
}
