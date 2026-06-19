import { apiRequest } from '@/lib/api'
import type { ProcedureCard } from '@/lib/demarches-api'

export type PathwayCode = 'campus_france' | 'parcoursup' | 'paris_saclay'

export type StudyApplicationType = 'first_year' | 'continuing'

export interface PathwayUser {
  id: string
  firstName: string
  lastName: string
  email: string
}

export interface PathwaySubStep {
  id: string
  title: string
  description: string | null
  required: boolean
  sortOrder: number
  dueDate: string | null
  validated: boolean
  counselorValidatedAt: string | null
  counselorValidatedBy: PathwayUser | null
  adminValidatedAt: string | null
  adminValidatedBy: PathwayUser | null
  updatedAt: string
}

export interface PathwayStage {
  id: string
  title: string
  description: string | null
  sortOrder: number
  progressPercent: number
  subSteps: PathwaySubStep[]
}

export interface Pathway {
  id: string
  code: PathwayCode
  name: string
  status: string
  statusLabel: string
  progressPercent: number
  blockedReason: string | null
  doubleValidationEnabled: boolean
  updatedAt: string
  stages: PathwayStage[]
}

export interface MyPathwaysResponse {
  studyApplicationType: StudyApplicationType | null
  studyApplicationTypeLabel: string | null
  pathways: Pathway[]
}

export type PathwaysResponse = MyPathwaysResponse

export interface PatchSubStepPayload {
  validated?: boolean
  counselorValidated?: boolean
  adminValidated?: boolean
}

export interface PatchSubStepResponse {
  pathway: Pathway
}

export const PATHWAY_ROUTES: Record<PathwayCode, string> = {
  campus_france: '/demarches/campus-france',
  parcoursup: '/demarches/parcoursup',
  paris_saclay: '/demarches/paris-saclay',
}

export const PATHWAY_LABELS: Record<PathwayCode, string> = {
  campus_france: 'Campus France',
  parcoursup: 'Parcoursup',
  paris_saclay: 'Paris-Saclay',
}

const jsonHeaders = { Accept: 'application/json' } as const

export async function fetchMyPathways(): Promise<MyPathwaysResponse> {
  return apiRequest<MyPathwaysResponse>('/api/me/pathways', { headers: jsonHeaders })
}

export async function fetchCandidatePathways(candidateId: string): Promise<PathwaysResponse> {
  return apiRequest<PathwaysResponse>(`/api/candidates/${candidateId}/pathways`, { headers: jsonHeaders })
}

export async function patchCandidatePathwaySubStep(
  candidateId: string,
  pathwayId: string,
  subStepId: string,
  payload: PatchSubStepPayload,
): Promise<PatchSubStepResponse> {
  return apiRequest<PatchSubStepResponse>(
    `/api/candidates/${candidateId}/pathways/${pathwayId}/sub-steps/${subStepId}`,
    {
      method: 'PATCH',
      headers: jsonHeaders,
      body: JSON.stringify(payload),
    },
  )
}

export interface PatchPathwayPayload {
  status?: string
  blockedReason?: string | null
}

export async function patchCandidatePathway(
  candidateId: string,
  pathwayId: string,
  payload: PatchPathwayPayload,
): Promise<PatchSubStepResponse> {
  return apiRequest<PatchSubStepResponse>(`/api/candidates/${candidateId}/pathways/${pathwayId}`, {
    method: 'PATCH',
    headers: jsonHeaders,
    body: JSON.stringify(payload),
  })
}

export function findPathwayByCode(pathways: Pathway[], code: PathwayCode): Pathway | undefined {
  return pathways.find((p) => p.code === code)
}

export function getNextPendingSubStep(pathway: Pathway): PathwaySubStep | null {
  for (const stage of pathway.stages) {
    for (const subStep of stage.subSteps) {
      if (subStep.required && !subStep.validated) {
        return subStep
      }
    }
  }
  return null
}

export function pathwayToProcedureCard(pathway: Pathway): ProcedureCard {
  const next = getNextPendingSubStep(pathway)
  return {
    type: pathway.code,
    label: pathway.name,
    status: pathway.status,
    statusLabel: pathway.statusLabel,
    progress: pathway.progressPercent,
    updatedAt: pathway.updatedAt,
    nextAction: next ? `Prochaine étape : ${next.title}` : 'Parcours complété',
  }
}

export function averagePathwayProgress(pathways: Pathway[]): number {
  if (!pathways.length) return 0
  const total = pathways.reduce((sum, p) => sum + p.progressPercent, 0)
  return Math.round(total / pathways.length)
}

export function countValidatedSubSteps(pathway: Pathway): { done: number; total: number } {
  let done = 0
  let total = 0
  for (const stage of pathway.stages) {
    for (const subStep of stage.subSteps) {
      if (!subStep.required) continue
      total += 1
      if (subStep.validated) done += 1
    }
  }
  return { done, total }
}

export function isCounselorValidated(subStep: PathwaySubStep): boolean {
  return subStep.counselorValidatedAt !== null
}

export function isAdminValidated(subStep: PathwaySubStep): boolean {
  return subStep.adminValidatedAt !== null
}

export function formatPathwayUser(user: PathwayUser): string {
  return `${user.firstName} ${user.lastName}`
}
