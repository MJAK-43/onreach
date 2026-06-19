import { apiRequest } from '@/lib/api'
import type { PathwayCode } from '@/lib/pathways-api'

export interface PathwayTrackingCounselor {
  id: string
  firstName: string
  lastName: string
  email: string
}

export interface PathwayTrackingRow {
  pathwayId: string
  candidateId: string
  candidateFirstName: string
  candidateLastName: string
  candidateEmail: string
  pathwayCode: PathwayCode
  pathwayName: string
  status: string
  statusLabel: string
  progressPercent: number
  blockedReason: string | null
  campaignYear: number
  campaignName: string
  counselor: PathwayTrackingCounselor | null
  nextSubStepTitle: string | null
  nextDueDate: string | null
  updatedAt: string
}

export interface PathwayTrackingFilters {
  pathway?: string
  status?: string
  campaign?: string
  counselor?: string
  search?: string
}

export interface PathwayTrackingResponse {
  items: PathwayTrackingRow[]
  total: number
}

export interface PathwayStatsResponse {
  totalPathways: number
  blockedCount: number
  overdueCount: number
  byStatus: Array<{ code: string; label: string; count: number }>
  byPathway: Array<{ code: string; label: string; count: number }>
}

const jsonHeaders = { Accept: 'application/json' } as const

function buildQuery(filters: PathwayTrackingFilters): string {
  const params = new URLSearchParams()
  Object.entries(filters).forEach(([key, value]) => {
    if (value) params.set(key, value)
  })
  const query = params.toString()
  return query ? `?${query}` : ''
}

export async function fetchPathwayTracking(
  filters: PathwayTrackingFilters = {},
): Promise<PathwayTrackingResponse> {
  return apiRequest<PathwayTrackingResponse>(`/api/pathways/candidates${buildQuery(filters)}`, {
    headers: jsonHeaders,
  })
}

export async function fetchPathwayStats(): Promise<PathwayStatsResponse> {
  return apiRequest<PathwayStatsResponse>('/api/pathways/stats', { headers: jsonHeaders })
}
