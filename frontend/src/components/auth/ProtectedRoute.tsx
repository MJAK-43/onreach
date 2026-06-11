import { Navigate, useLocation } from 'react-router-dom'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { clearTokens, isAuthenticated } from '@/lib/auth'

interface ProtectedRouteProps {
  children: React.ReactNode
  permission?: string
}

export function ProtectedRoute({ children, permission }: ProtectedRouteProps) {
  const location = useLocation()
  const { data: user, isLoading, isError } = useCurrentUser()

  if (!isAuthenticated()) {
    return <Navigate to="/login" state={{ from: location }} replace />
  }

  if (isLoading) {
    return (
      <div className="flex min-h-[50vh] items-center justify-center">
        <p className="text-sm text-muted-foreground">Chargement...</p>
      </div>
    )
  }

  if (isError || !user) {
    clearTokens()
    return <Navigate to="/login" state={{ from: location }} replace />
  }

  if (permission && !user.permissions.includes(permission)) {
    return <Navigate to="/unauthorized" replace />
  }

  return children
}
