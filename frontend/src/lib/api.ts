import {
  clearTokens,
  getAccessToken,
  getRefreshToken,
  setTokens,
} from '@/lib/auth'

const API_URL = import.meta.env.VITE_API_URL ?? 'http://api.localhost'

export interface HealthResponse {
  status: string
}

export interface CurrentUser {
  id: string
  email: string
  firstName: string
  lastName: string
  fullName: string
  roles: string[]
  permissions: string[]
  mfaEnabled: boolean
  isActive: boolean
  createdAt?: string
}

export interface LoginRequest {
  email: string
  password: string
  rememberMe?: boolean
  mfaCode?: string
}

export interface LoginResponse {
  token?: string
  refreshToken?: string
  expiresIn?: number
  user?: CurrentUser
  requiresMfa?: boolean
}

export interface UserListItem {
  id: string
  email: string
  firstName: string
  lastName: string
  isActive: boolean
  mfaEnabled: boolean
}

export interface RoleListItem {
  id: string
  code: string
  name: string
  description?: string | null
  isSystem: boolean
}

export interface PermissionListItem {
  id: string
  code: string
  name: string
  description?: string | null
  isSystem: boolean
}

export interface MfaSetupResponse {
  secret: string
  qrCode: string
}

export class ApiError extends Error {
  constructor(
    message: string,
    public status: number,
    public data?: unknown,
  ) {
    super(message)
    this.name = 'ApiError'
  }
}

let refreshPromise: Promise<string | null> | null = null

async function parseError(response: Response): Promise<string> {
  try {
    const data = (await response.json()) as { message?: string; detail?: string }
    return data.message ?? data.detail ?? response.statusText
  } catch {
    return response.statusText
  }
}

async function refreshAccessToken(): Promise<string | null> {
  const refreshToken = getRefreshToken()
  if (!refreshToken) {
    clearTokens()
    return null
  }

  const response = await fetch(`${API_URL}/api/auth/refresh`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ refreshToken }),
  })

  if (!response.ok) {
    clearTokens()
    return null
  }

  const data = (await response.json()) as {
    token: string
    refresh_token?: string
    refreshToken?: string
  }
  const newRefresh = data.refreshToken ?? data.refresh_token ?? refreshToken
  setTokens(data.token, newRefresh)
  return data.token
}

export async function apiRequest<T>(
  path: string,
  options: RequestInit = {},
  retry = true,
): Promise<T> {
  const headers = new Headers(options.headers)
  if (!headers.has('Content-Type') && options.body) {
    headers.set('Content-Type', 'application/json')
  }
  if (!headers.has('Accept')) {
    headers.set('Accept', 'application/ld+json')
  }

  const token = getAccessToken()
  if (token) {
    headers.set('Authorization', `Bearer ${token}`)
  }

  const response = await fetch(`${API_URL}${path}`, {
    ...options,
    headers,
  })

  if (response.status === 401 && retry && getRefreshToken()) {
    if (!refreshPromise) {
      refreshPromise = refreshAccessToken().finally(() => {
        refreshPromise = null
      })
    }
    const newToken = await refreshPromise
    if (newToken) {
      return apiRequest<T>(path, options, false)
    }
  }

  if (!response.ok) {
    throw new ApiError(await parseError(response), response.status)
  }

  if (response.status === 204) {
    return undefined as T
  }

  return response.json() as Promise<T>
}

function extractCollection<T>(data: Record<string, unknown>): T[] {
  const member = data['hydra:member'] ?? data.member
  if (Array.isArray(member)) {
    return member as T[]
  }
  if (Array.isArray(data)) {
    return data as T[]
  }
  return []
}

export async function fetchHealth(): Promise<HealthResponse> {
  const response = await fetch(`${API_URL}/health`)
  if (!response.ok) {
    throw new Error('Health check failed')
  }
  return response.json() as Promise<HealthResponse>
}

export async function login(payload: LoginRequest): Promise<LoginResponse> {
  const data = await apiRequest<LoginResponse>('/api/auth/login', {
    method: 'POST',
    body: JSON.stringify(payload),
  }, false)

  if (data.token && data.refreshToken) {
    setTokens(data.token, data.refreshToken)
  }

  return data
}

export async function logout(): Promise<void> {
  const refreshToken = getRefreshToken()
  try {
    await apiRequest('/api/auth/logout', {
      method: 'POST',
      body: JSON.stringify({ refreshToken }),
    })
  } finally {
    clearTokens()
  }
}

export async function getCurrentUser(): Promise<CurrentUser> {
  return apiRequest<CurrentUser>('/api/me')
}

export async function forgotPassword(email: string): Promise<{ message: string }> {
  return apiRequest('/api/auth/forgot-password', {
    method: 'POST',
    body: JSON.stringify({ email }),
  }, false)
}

export async function resetPassword(
  token: string,
  password: string,
): Promise<{ message: string }> {
  return apiRequest('/api/auth/reset-password', {
    method: 'POST',
    body: JSON.stringify({ token, password }),
  }, false)
}

export async function changePassword(
  currentPassword: string,
  newPassword: string,
): Promise<{ message: string }> {
  return apiRequest('/api/auth/change-password', {
    method: 'POST',
    body: JSON.stringify({ currentPassword, newPassword }),
  })
}

export async function mfaSetup(): Promise<MfaSetupResponse> {
  return apiRequest<MfaSetupResponse>('/api/auth/mfa/setup', { method: 'POST' })
}

export async function mfaEnable(code: string): Promise<{ message: string }> {
  return apiRequest('/api/auth/mfa/enable', {
    method: 'POST',
    body: JSON.stringify({ code }),
  })
}

export async function mfaDisable(
  password: string,
  code: string,
): Promise<{ message: string }> {
  return apiRequest('/api/auth/mfa/disable', {
    method: 'POST',
    body: JSON.stringify({ password, code }),
  })
}

export async function fetchUsers(): Promise<UserListItem[]> {
  const data = await apiRequest<Record<string, unknown>>('/api/users')
  return extractCollection<UserListItem>(data)
}

export async function fetchRoles(): Promise<RoleListItem[]> {
  const data = await apiRequest<Record<string, unknown>>('/api/roles')
  return extractCollection<RoleListItem>(data)
}

export async function fetchPermissions(): Promise<PermissionListItem[]> {
  const data = await apiRequest<Record<string, unknown>>('/api/permissions')
  return extractCollection<PermissionListItem>(data)
}
