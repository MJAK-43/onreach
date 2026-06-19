import { apiRequest } from '@/lib/api'

export interface NotificationItem {
  id: string
  type: string
  title: string
  message: string
  linkUrl: string | null
  metadata: Record<string, unknown> | null
  read: boolean
  readAt: string | null
  createdAt: string
}

export interface NotificationsResponse {
  unreadCount: number
  items: NotificationItem[]
}

export interface PathwaySettingItem {
  code: string
  label: string
  doubleValidationEnabled: boolean
}

export interface PathwaySettingsResponse {
  items: PathwaySettingItem[]
}

export interface PathwayAuditEntry {
  id: string
  action: string
  actionLabel: string
  description: string
  payload: Record<string, unknown> | null
  occurredAt: string
  performedBy: {
    id: string
    firstName: string
    lastName: string
    email: string
  } | null
  pathwayName: string | null
  subStepTitle: string | null
}

export interface PathwayAuditResponse {
  items: PathwayAuditEntry[]
}

const jsonHeaders = { Accept: 'application/json' } as const

export async function fetchMyNotifications(): Promise<NotificationsResponse> {
  return apiRequest<NotificationsResponse>('/api/me/notifications', { headers: jsonHeaders })
}

export async function markNotificationRead(notificationId: string): Promise<void> {
  await apiRequest(`/api/me/notifications/${notificationId}/read`, {
    method: 'PATCH',
    headers: jsonHeaders,
  })
}

export async function markAllNotificationsRead(): Promise<void> {
  await apiRequest('/api/me/notifications/read-all', {
    method: 'POST',
    headers: jsonHeaders,
  })
}

export async function fetchPathwaySettings(): Promise<PathwaySettingsResponse> {
  return apiRequest<PathwaySettingsResponse>('/api/admin/pathway-settings', { headers: jsonHeaders })
}

export async function updatePathwaySetting(
  code: string,
  doubleValidationEnabled: boolean,
): Promise<PathwaySettingItem> {
  return apiRequest<PathwaySettingItem>(`/api/admin/pathway-settings/${code}`, {
    method: 'PATCH',
    headers: jsonHeaders,
    body: JSON.stringify({ doubleValidationEnabled }),
  })
}

export async function fetchCandidatePathwayAudit(candidateId: string): Promise<PathwayAuditResponse> {
  return apiRequest<PathwayAuditResponse>(`/api/candidates/${candidateId}/pathway-audit`, {
    headers: jsonHeaders,
  })
}
