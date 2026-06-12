import { apiRequest } from '@/lib/api'

export interface DemarchesCompletion {
  global: number
  profile: number
  documents: number
  campusFrance: number
  parcoursup: number
  parisSaclay: number
  visa: number
}

export interface ProcedureCard {
  type: string
  label: string
  status: string
  statusLabel: string
  progress: number
  updatedAt: string
  nextAction: string
}

export interface DemarchesAlert {
  type: string
  severity: 'info' | 'warning' | 'error'
  message: string
}

export interface DemarchesOverview {
  candidateId: string
  referenceNumber: string
  completion: DemarchesCompletion
  summary: {
    documentsValidated: number
    documentsMissing: number
    paymentsPending: number
    upcomingAppointments: number
  }
  counselor: {
    id: string
    firstName: string
    lastName: string
    email: string
    fullName: string
  } | null
  procedures: {
    campusFrance: ProcedureCard
    parcoursup: ProcedureCard
    parisSaclay: ProcedureCard
  }
  alerts: DemarchesAlert[]
}

export interface DocumentIndicator {
  type: string
  label: string
  status: 'validated' | 'pending' | 'rejected' | 'missing'
  documentId?: string
  originalFilename?: string | null
  uploadedAt?: string | null
}

export interface WorkflowStep {
  key: string
  label: string
  state: 'done' | 'current' | 'upcoming'
}

export interface TimelineEntry {
  id: string
  date: string
  occurredAt: string
  action: string
  description: string
  author: string
  comment: string
}

export interface ParcoursupWishItem {
  id: string
  rank: number
  formation: string
  university: string
  submittedAt?: string | null
  status: string
  statusLabel: string
}

export interface CampusFranceData {
  card: ProcedureCard
  information: Record<string, unknown>
  documents: DocumentIndicator[]
  workflow: WorkflowStep[]
  history: TimelineEntry[]
  messages: { readOnly: boolean; threads: unknown[] }
  checklist: { items: unknown[]; percent: number }
}

export interface ParcoursupData {
  card: ProcedureCard
  information: Record<string, unknown> | null
  wishes: ParcoursupWishItem[]
  documents: DocumentIndicator[]
  history: TimelineEntry[]
  messages: { readOnly: boolean; threads: unknown[] }
}

export interface ParisSaclayData {
  card: ProcedureCard
  information: Record<string, unknown> | null
  documents: DocumentIndicator[]
  project: Record<string, unknown>
  workflow: WorkflowStep[]
  history: TimelineEntry[]
  messages: { readOnly: boolean; threads: unknown[] }
}

const jsonHeaders = { Accept: 'application/json' } as const

export async function fetchMyApplications(): Promise<DemarchesOverview> {
  return apiRequest<DemarchesOverview>('/api/me/applications', { headers: jsonHeaders })
}

export async function fetchMyCampusFrance(): Promise<CampusFranceData> {
  return apiRequest<CampusFranceData>('/api/me/campus-france', { headers: jsonHeaders })
}

export async function fetchMyParcoursup(): Promise<ParcoursupData> {
  return apiRequest<ParcoursupData>('/api/me/parcoursup', { headers: jsonHeaders })
}

export async function fetchMyParisSaclay(): Promise<ParisSaclayData> {
  return apiRequest<ParisSaclayData>('/api/me/paris-saclay', { headers: jsonHeaders })
}

export async function fetchMyDocuments(): Promise<DocumentIndicator[]> {
  return apiRequest<DocumentIndicator[]>('/api/me/documents', { headers: jsonHeaders })
}

export async function fetchMyTimeline(): Promise<TimelineEntry[]> {
  return apiRequest<TimelineEntry[]>('/api/me/timeline', { headers: jsonHeaders })
}
