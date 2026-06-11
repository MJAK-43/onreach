import type { CurrentUser } from '@/lib/api'

export type AppRole = 'SUPER_ADMIN' | 'ADMIN' | 'COUNSELOR' | 'CANDIDATE' | 'OTHER'

const ROLE_PRIORITY: AppRole[] = ['SUPER_ADMIN', 'ADMIN', 'COUNSELOR', 'CANDIDATE']

export function getPrimaryRole(roles: string[] | undefined): AppRole {
  if (!roles?.length) {
    return 'OTHER'
  }
  for (const role of ROLE_PRIORITY) {
    if (roles.includes(role)) {
      return role
    }
  }

  return 'OTHER'
}

export function getRoleLabel(role: AppRole): string {
  const labels: Record<AppRole, string> = {
    SUPER_ADMIN: 'Super administrateur',
    ADMIN: 'Administrateur',
    COUNSELOR: 'Conseillère',
    CANDIDATE: 'Candidat',
    OTHER: 'Utilisateur',
  }

  return labels[role]
}

export function isStaffUser(user: CurrentUser | undefined): boolean {
  const role = getPrimaryRole(user?.roles)
  return role === 'SUPER_ADMIN' || role === 'ADMIN' || role === 'COUNSELOR'
}

export function isAdminUser(user: CurrentUser | undefined): boolean {
  const role = getPrimaryRole(user?.roles)
  return role === 'SUPER_ADMIN' || role === 'ADMIN'
}
