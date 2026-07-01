import { apiRequest } from '@/lib/api'

export interface CampaignItem {
  id: string
  name: string
  year: number
  startDate: string
  endDate: string
  active: boolean
  templateCount: number
}

export interface PathwayTemplateSummary {
  id: string
  code: string
  name: string
  campaignId: string
  campaignYear: number
  stageCount: number
  subStepCount: number
}

export interface PathwayTemplateDetail extends PathwayTemplateSummary {
  campaignStartDate: string
  eligibleStudyTypes: string[]
  stages: Array<{
    id: string
    title: string
    description: string | null
    sortOrder: number
    subSteps: Array<{
      id: string
      title: string
      description: string | null
      required: boolean
      defaultDueOffsetDays: number | null
      sortOrder: number
    }>
  }>
}

const jsonHeaders = { Accept: 'application/json' } as const

export async function fetchCampaigns(): Promise<{ items: CampaignItem[] }> {
  return apiRequest('/api/admin/campaigns', { headers: jsonHeaders })
}

export async function createCampaign(payload: {
  name: string
  year: number
  startDate?: string
  endDate?: string
}): Promise<CampaignItem> {
  return apiRequest('/api/admin/campaigns', {
    method: 'POST',
    headers: jsonHeaders,
    body: JSON.stringify(payload),
  })
}

export async function updateCampaign(
  id: string,
  payload: Partial<{ name: string; active: boolean; startDate: string; endDate: string }>,
): Promise<CampaignItem> {
  return apiRequest(`/api/admin/campaigns/${id}`, {
    method: 'PATCH',
    headers: jsonHeaders,
    body: JSON.stringify(payload),
  })
}

export async function fetchPathwayTemplates(campaignId?: string): Promise<{ items: PathwayTemplateSummary[] }> {
  const query = campaignId ? `?campaign=${encodeURIComponent(campaignId)}` : ''
  return apiRequest(`/api/admin/pathway-templates${query}`, { headers: jsonHeaders })
}

export async function fetchPathwayTemplate(id: string): Promise<PathwayTemplateDetail> {
  return apiRequest(`/api/admin/pathway-templates/${id}`, { headers: jsonHeaders })
}

export async function updatePathwayTemplate(id: string, payload: { name?: string }): Promise<PathwayTemplateDetail> {
  return apiRequest(`/api/admin/pathway-templates/${id}`, {
    method: 'PATCH',
    headers: jsonHeaders,
    body: JSON.stringify(payload),
  })
}

export async function updatePathwayStage(
  id: string,
  payload: { title?: string; description?: string | null },
): Promise<PathwayTemplateDetail> {
  return apiRequest(`/api/admin/pathway-stages/${id}`, {
    method: 'PATCH',
    headers: jsonHeaders,
    body: JSON.stringify(payload),
  })
}

export async function updatePathwaySubStepTemplate(
  id: string,
  payload: Partial<{ title: string; description: string | null; required: boolean; defaultDueOffsetDays: number | null }>,
): Promise<PathwayTemplateDetail> {
  return apiRequest(`/api/admin/pathway-sub-steps/${id}`, {
    method: 'PATCH',
    headers: jsonHeaders,
    body: JSON.stringify(payload),
  })
}

export async function importPathwayCalendar(payload: {
  templateId: string
  calendarText: string
  apply?: boolean
}): Promise<{ suggestions: Array<{ subStepId: string; subStepTitle: string; suggestedDueOffsetDays: number; sourceLine: string }>; applied: number }> {
  return apiRequest('/api/admin/pathways/import-calendar', {
    method: 'POST',
    headers: jsonHeaders,
    body: JSON.stringify(payload),
  })
}
