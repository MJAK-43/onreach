import type { RoleListItem, UserListItem, UserRoleRef } from '@/lib/api'

function roleCodeFromRef(
  role: UserRoleRef | string,
  rolesById: Map<string, string>,
): string | null {
  if (typeof role === 'string') {
    if (role.includes('COUNSELOR')) {
      return 'COUNSELOR'
    }
    if (role.startsWith('/api/roles/')) {
      const id = role.split('/').pop()
      return id ? rolesById.get(id) ?? null : null
    }
    return role.replace(/^ROLE_/, '')
  }

  if (role.code) {
    return role.code.replace(/^ROLE_/, '')
  }

  if (role.id) {
    return rolesById.get(role.id) ?? null
  }

  return null
}

export function buildRoleCodeMap(roles: RoleListItem[]): Map<string, string> {
  return new Map(roles.map((role) => [role.id, role.code.replace(/^ROLE_/, '')]))
}

export function userHasRole(
  user: UserListItem,
  roleCode: string,
  rolesCatalog: RoleListItem[] | Map<string, string>,
): boolean {
  if (!user.roles?.length) {
    return false
  }

  const rolesById = rolesCatalog instanceof Map ? rolesCatalog : buildRoleCodeMap(rolesCatalog)
  const normalized = roleCode.replace(/^ROLE_/, '')

  return user.roles.some((role) => roleCodeFromRef(role, rolesById) === normalized)
}

export function isCounselorUser(
  user: UserListItem,
  rolesCatalog: RoleListItem[] | Map<string, string>,
): boolean {
  return userHasRole(user, 'COUNSELOR', rolesCatalog)
}
