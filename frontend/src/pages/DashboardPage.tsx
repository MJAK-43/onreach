import { useCurrentUser } from '@/hooks/useCurrentUser'
import { getPrimaryRole } from '@/lib/roles'
import { CandidateDashboardPage } from '@/pages/dashboard/CandidateDashboardPage'
import { StaffDashboardPage } from '@/pages/dashboard/StaffDashboardPage'

export function DashboardPage() {
  const { data: user, isLoading } = useCurrentUser()

  if (isLoading) {
    return (
      <div className="flex min-h-[40vh] items-center justify-center">
        <p className="text-sm text-muted-foreground">Chargement du tableau de bord...</p>
      </div>
    )
  }

  const role = getPrimaryRole(user?.roles)

  if (role === 'CANDIDATE') {
    return <CandidateDashboardPage />
  }

  return <StaffDashboardPage />
}
