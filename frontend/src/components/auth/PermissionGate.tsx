import { useCurrentUser } from '@/hooks/useCurrentUser'
import { hasPermission } from '@/lib/auth'

interface PermissionGateProps {
  permission: string
  children: React.ReactNode
  fallback?: React.ReactNode
}

export function PermissionGate({
  permission,
  children,
  fallback = null,
}: PermissionGateProps) {
  const { data: user } = useCurrentUser()

  if (!hasPermission(user?.permissions, permission, user?.roles)) {
    return fallback
  }

  return children
}
