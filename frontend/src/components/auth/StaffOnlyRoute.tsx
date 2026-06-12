import { Navigate } from 'react-router-dom'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { getPrimaryRole } from '@/lib/roles'

export function StaffOnlyRoute({ children }: { children: React.ReactNode }) {
  const { data: user, isLoading } = useCurrentUser()

  if (isLoading) {
    return (
      <div className="flex min-h-[40vh] items-center justify-center">
        <p className="text-sm text-slate-500">Chargement...</p>
      </div>
    )
  }

  if (getPrimaryRole(user?.roles) === 'CANDIDATE') {
    return <Navigate to="/" replace />
  }

  return children
}
