import type { CandidateListItem } from '@/lib/api'

export const STATUS_LABELS: Record<string, string> = {
  lead: 'Lead',
  profile_incomplete: 'Profil incomplet',
  documents_pending: 'Documents en attente',
  in_progress: 'En cours',
  admission_obtained: 'Admission obtenue',
  visa_obtained: 'Visa obtenu',
  completed: 'Clôturé',
  suspended: 'Suspendu',
  cancelled: 'Annulé',
}

export const STATUS_COLORS: Record<string, string> = {
  admission_obtained: 'bg-green-100 text-green-800',
  visa_obtained: 'bg-green-100 text-green-800',
  completed: 'bg-green-100 text-green-800',
  suspended: 'bg-red-100 text-red-800',
  cancelled: 'bg-red-100 text-red-800',
  documents_pending: 'bg-amber-100 text-amber-800',
  in_progress: 'bg-orange-100 text-orange-800',
  lead: 'bg-slate-100 text-slate-800',
  profile_incomplete: 'bg-amber-100 text-amber-800',
}

export interface CandidateStats {
  total: number
  inProgress: number
  admissions: number
  visas: number
  byStatus: Record<string, number>
  byCountry: Record<string, number>
}

export function computeCandidateStats(candidates: CandidateListItem[]): CandidateStats {
  const byStatus: Record<string, number> = {}
  const byCountry: Record<string, number> = {}

  for (const c of candidates) {
    byStatus[c.status] = (byStatus[c.status] ?? 0) + 1
    byCountry[c.nationality] = (byCountry[c.nationality] ?? 0) + 1
  }

  const inProgress = candidates.filter((c) =>
    ['in_progress', 'documents_pending', 'profile_incomplete'].includes(c.status),
  ).length

  return {
    total: candidates.length,
    inProgress,
    admissions: byStatus.admission_obtained ?? 0,
    visas: byStatus.visa_obtained ?? 0,
    byStatus,
    byCountry,
  }
}

export function formatRelativeDate(dateStr?: string): string {
  if (!dateStr) return '—'
  const date = new Date(dateStr)
  const diff = Date.now() - date.getTime()
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  if (days <= 0) return "Aujourd'hui"
  if (days === 1) return 'Hier'
  return `Il y a ${days} jours`
}
