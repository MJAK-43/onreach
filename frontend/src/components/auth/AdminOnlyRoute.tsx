import { Navigate } from 'react-router-dom'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { isAdminUser } from '@/lib/roles'

export function AdminOnlyRoute({ children }: { children: React.ReactNode }) {
  const { data: user, isLoading } = useCurrentUser()

  if (isLoading) {
    return (
      <div className="flex min-h-[40vh] items-center justify-center">
        <p className="text-sm text-slate-500">Chargement...</p>
      </div>
    )
  }

  if (!isAdminUser(user)) {
    return <Navigate to="/unauthorized" replace />
  }

  return children
}
