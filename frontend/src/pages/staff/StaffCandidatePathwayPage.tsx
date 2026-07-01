import { Navigate, useLocation, useParams } from 'react-router-dom'
import { StaffCandidatePathwayView } from '@/components/staff/pathways/StaffCandidatePathwayView'
import { useCandidatePathways } from '@/hooks/useCandidatePathways'
import { pathwayCodeFromSlug, staffPathwayRoute } from '@/lib/pathways-api'

function resolvePathwaySlug(pathname: string, paramSlug: string): string {
  if (paramSlug) {
    return paramSlug
  }

  const segments = pathname.split('/').filter(Boolean)
  const trackingIndex = segments.indexOf('tracking')
  if (trackingIndex >= 0 && segments.length > trackingIndex + 2) {
    return segments[trackingIndex + 2]
  }

  return ''
}

export function StaffCandidatePathwayRedirect() {
  const { candidateId = '' } = useParams()
  const pathwaysQuery = useCandidatePathways(candidateId)

  if (pathwaysQuery.isLoading) {
    return <p className="text-sm text-slate-500">Chargement du parcours…</p>
  }

  const first = pathwaysQuery.data?.pathways[0]
  if (!first) {
    return (
      <p className="text-sm text-slate-500">
        Aucun parcours assigné à ce candidat.
      </p>
    )
  }

  return <Navigate to={staffPathwayRoute(candidateId, first.code)} replace />
}

export function StaffCandidatePathwayPage() {
  const { candidateId = '', pathwaySlug = '' } = useParams()
  const { pathname } = useLocation()
  const slug = resolvePathwaySlug(pathname, pathwaySlug)
  const pathwayCode = pathwayCodeFromSlug(slug)

  if (!pathwayCode) {
    return <p className="text-sm text-red-600">Parcours introuvable pour cette URL.</p>
  }

  return <StaffCandidatePathwayView candidateId={candidateId} pathwayCode={pathwayCode} />
}
