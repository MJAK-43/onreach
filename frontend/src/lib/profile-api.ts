import { getAccessToken } from '@/lib/auth'

const API_URL = import.meta.env.VITE_API_URL ?? 'http://api.localhost'

export interface ProfileCompletion {
  profile: number
  documents: number
  academic: number
  financing: number
  global: number
}

export interface MyProfile {
  id: string
  referenceNumber: string
  createdAt: string
  updatedAt: string
  completion: ProfileCompletion
  personal: {
    firstName: string
    lastName: string
    gender: string | null
    dateOfBirth: string | null
    placeOfBirth: string | null
    nationality: string
    maritalStatus: string | null
    passportNumber: string | null
    passportIssuedAt: string | null
    passportExpiresAt: string | null
    passportCountry: string | null
    identityCardNumber: string | null
  }
  contact: {
    address: string | null
    city: string | null
    region: string | null
    postalCode: string | null
    country: string | null
    phone: string | null
    whatsapp: string | null
    email: string
    secondaryEmail: string | null
  }
  studyProject: {
    domain: string | null
    specialty: string | null
    level: string | null
    targetCountry: string | null
    universities: string[]
    description: string | null
  }
  careerProject: {
    targetJob: string | null
    objectives: string | null
    sector: string | null
    description: string | null
  }
  academic: {
    highestDiploma: string | null
    institutionName: string | null
    graduationYear: number | null
    overallAverage: string | null
    ranking: string | null
    specialty: string | null
    academicAchievements: string | null
    records: AcademicRecord[]
  } | null
  languages: {
    frenchLevel: string | null
    englishLevel: string | null
    otherLanguages: string[]
    certificates: LanguageCertificate[]
  } | null
  financing: {
    type: string
    availableBudget: string | null
    plannedAmount: string | null
    description: string | null
    guarantors: Guarantor[]
  } | null
  experiences: ProfessionalExperience[]
  counselor: {
    id: string
    firstName: string
    lastName: string
    email: string
    phone: string | null
    whatsapp: string | null
  } | null
}

export interface AcademicRecord {
  id?: string
  diplomaType: string | null
  diploma: string
  institution: string
  country: string | null
  year: number
  mention: string | null
  average: string | null
  ranking: string | null
  description: string | null
}

export interface LanguageCertificate {
  id?: string
  type: string
  score: string | null
  issueDate: string | null
  expirationDate: string | null
}

export interface Guarantor {
  id?: string
  firstName: string | null
  lastName: string | null
  fullName: string
  profession: string | null
  employer: string | null
  phone: string | null
  email: string | null
  address: string | null
  monthlyIncome: string | null
}

export interface ProfessionalExperience {
  id?: string
  company: string
  position: string
  startDate: string
  endDate: string | null
  description: string | null
}

export type CandidateProfile = MyProfile
export type ProfileDocument = MyDocument

export interface MyDocument {
  id: string
  type: string
  typeLabel: string
  status: string
  statusLabel: string
  originalFilename: string | null
  mimeType: string | null
  size: number | null
  version: number
  uploadedAt: string | null
  validatedAt: string | null
  rejectionReason: string | null
  previewable: boolean
}

export interface HistoryEntry {
  id: string
  date: string
  occurredAt: string
  action: string
  description: string
  author: string
  comment: string
}

export const DOCUMENT_TYPES = [
  { value: 'passport', label: 'Passeport' },
  { value: 'photo', label: 'Photo' },
  { value: 'cv', label: 'CV' },
  { value: 'motivation_letter', label: 'Lettre de motivation' },
  { value: 'diploma', label: 'Diplôme' },
  { value: 'transcript', label: 'Relevé de notes' },
  { value: 'tcf', label: 'TCF' },
  { value: 'delf', label: 'DELF' },
  { value: 'dalf', label: 'DALF' },
  { value: 'toefl', label: 'TOEFL' },
  { value: 'ielts', label: 'IELTS' },
  { value: 'recommendation_letter', label: 'Lettre de recommandation' },
  { value: 'support_attestation', label: 'Attestation de prise en charge' },
  { value: 'other', label: 'Autre' },
] as const

const jsonHeaders = { Accept: 'application/json', 'Content-Type': 'application/json' } as const

async function profileRequest<T>(path: string, options: RequestInit = {}): Promise<T> {
  const headers = new Headers(options.headers ?? jsonHeaders)
  const token = getAccessToken()
  if (token) {
    headers.set('Authorization', `Bearer ${token}`)
  }
  const response = await fetch(`${API_URL}${path}`, { ...options, headers })
  if (!response.ok) {
    const message = await response.text()
    throw new Error(message || response.statusText)
  }
  if (response.status === 204) {
    return undefined as T
  }
  return response.json() as Promise<T>
}

export function fetchMyProfile(): Promise<MyProfile> {
  return profileRequest<MyProfile>('/api/me/profile')
}

export function updateMyProfile(payload: Record<string, unknown>): Promise<MyProfile> {
  return profileRequest<MyProfile>('/api/me/profile', {
    method: 'PUT',
    body: JSON.stringify(payload),
  })
}

export function fetchMyProfileDocuments(): Promise<MyDocument[]> {
  return profileRequest<MyDocument[]>('/api/me/documents')
}

export function fetchMyHistory(): Promise<HistoryEntry[]> {
  return profileRequest<HistoryEntry[]>('/api/me/history')
}

export function documentDownloadUrl(id: string): string {
  return `${API_URL}/api/me/documents/${id}/download`
}

export async function openDocumentPreview(id: string): Promise<void> {
  const token = getAccessToken()
  const response = await fetch(`${API_URL}/api/me/documents/${id}/preview`, {
    headers: token ? { Authorization: `Bearer ${token}` } : {},
  })
  if (!response.ok) {
    throw new Error('Impossible d\'ouvrir l\'aperçu')
  }
  const blob = await response.blob()
  const url = URL.createObjectURL(blob)
  window.open(url, '_blank', 'noopener,noreferrer')
}

export async function uploadMyDocument(
  file: File,
  type: string,
  onProgress?: (percent: number) => void,
  replaceDocumentId?: string,
): Promise<MyDocument> {
  const form = new FormData()
  form.append('file', file)
  form.append('type', type)
  if (replaceDocumentId) {
    form.append('replaceDocumentId', replaceDocumentId)
  }

  const token = getAccessToken()
  return new Promise((resolve, reject) => {
    const xhr = new XMLHttpRequest()
    xhr.open('POST', `${API_URL}/api/me/documents`)
    if (token) {
      xhr.setRequestHeader('Authorization', `Bearer ${token}`)
    }
    xhr.upload.onprogress = (e) => {
      if (e.lengthComputable && onProgress) {
        onProgress(Math.round((e.loaded / e.total) * 100))
      }
    }
    xhr.onload = () => {
      if (xhr.status >= 200 && xhr.status < 300) {
        resolve(JSON.parse(xhr.responseText) as MyDocument)
      } else {
        reject(new Error(xhr.responseText || 'Échec du téléversement'))
      }
    }
    xhr.onerror = () => reject(new Error('Erreur réseau'))
    xhr.send(form)
  })
}

export function deleteMyDocument(id: string): Promise<void> {
  return profileRequest<void>(`/api/me/documents/${id}`, { method: 'DELETE' })
}
