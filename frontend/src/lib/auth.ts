const ACCESS_TOKEN_KEY = 'onreach_access_token'
const REFRESH_TOKEN_KEY = 'onreach_refresh_token'

export function getAccessToken(): string | null {
  return localStorage.getItem(ACCESS_TOKEN_KEY)
}

export function getRefreshToken(): string | null {
  return localStorage.getItem(REFRESH_TOKEN_KEY)
}

export function setTokens(accessToken: string, refreshToken: string): void {
  localStorage.setItem(ACCESS_TOKEN_KEY, accessToken)
  localStorage.setItem(REFRESH_TOKEN_KEY, refreshToken)
}

export function clearTokens(): void {
  localStorage.removeItem(ACCESS_TOKEN_KEY)
  localStorage.removeItem(REFRESH_TOKEN_KEY)
}

export function isAuthenticated(): boolean {
  return getAccessToken() !== null
}

export function hasPermission(
  permissions: string[] | undefined,
  permission: string,
  roles?: string[],
): boolean {
  if (roles?.includes('SUPER_ADMIN')) {
    return true
  }

  return permissions?.includes(permission) ?? false
}

export function hasRole(roles: string[] | undefined, role: string): boolean {
  return roles?.includes(role) ?? false
}
