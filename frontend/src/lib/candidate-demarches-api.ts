import { apiRequest } from '@/lib/api'
import type {
  CampusFranceData,
  DemarchesOverview,
  ParisSaclayData,
  ParcoursupData,
} from '@/lib/demarches-api'

const jsonHeaders = { Accept: 'application/json' } as const

export async function fetchCandidateDemarchesOverview(candidateId: string): Promise<DemarchesOverview> {
  return apiRequest<DemarchesOverview>(`/api/candidates/${candidateId}/demarches/overview`, {
    headers: jsonHeaders,
  })
}

export async function fetchCandidateCampusFrance(candidateId: string): Promise<CampusFranceData> {
  return apiRequest<CampusFranceData>(`/api/candidates/${candidateId}/demarches/campus-france`, {
    headers: jsonHeaders,
  })
}

export async function fetchCandidateParcoursup(candidateId: string): Promise<ParcoursupData> {
  return apiRequest<ParcoursupData>(`/api/candidates/${candidateId}/demarches/parcoursup`, {
    headers: jsonHeaders,
  })
}

export async function fetchCandidateParisSaclay(candidateId: string): Promise<ParisSaclayData> {
  return apiRequest<ParisSaclayData>(`/api/candidates/${candidateId}/demarches/paris-saclay`, {
    headers: jsonHeaders,
  })
}
