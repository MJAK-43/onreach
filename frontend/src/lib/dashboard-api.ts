import { apiRequest } from '@/lib/api'

export interface MyDashboardChecklistItem {
  id: string
  label: string
  documentType: string
  required: boolean
  completed: boolean
}

export interface MyDashboardResponse {
  id: string
  status: string
  statusLabel: string
  counselor: {
    id: string
    firstName: string
    lastName: string
    email: string
  } | null
  checklist: {
    items: MyDashboardChecklistItem[]
    percent: number
  }
  documents: Array<{
    type: string
    status: string
  }>
}

export async function fetchMyDashboard(): Promise<MyDashboardResponse> {
  return apiRequest<MyDashboardResponse>('/api/me/dashboard', {
    headers: { Accept: 'application/json' },
  })
}
