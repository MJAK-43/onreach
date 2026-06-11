import { useQuery } from '@tanstack/react-query'
import { Navigate } from 'react-router-dom'
import { fetchCandidates } from '@/lib/api'

/** Redirige le candidat vers sa fiche dossier. */
export function MyFilePage() {
  const { data: candidates, isLoading } = useQuery({
    queryKey: ['candidates'],
    queryFn: fetchCandidates,
  })

  if (isLoading) {
    return <p className="text-sm text-muted-foreground">Chargement...</p>
  }

  const dossier = candidates?.[0]
  if (!dossier) {
    return (
      <p className="text-sm text-muted-foreground">
        Votre dossier n&apos;est pas encore disponible.
      </p>
    )
  }

  return <Navigate to={`/candidates/${dossier.id}`} replace />
}
