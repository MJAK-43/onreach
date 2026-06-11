import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { fetchCandidates } from '@/lib/api'
import { PermissionGate } from '@/components/auth/PermissionGate'
import { Button } from '@/components/ui/button'
import { CandidateStatusBadge } from '@/components/dashboard/CandidateStatusBadge'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

export function CandidatesPage() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ['candidates'],
    queryFn: fetchCandidates,
  })

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-xl font-semibold">Candidats</h2>
          <p className="mt-1 text-sm text-muted-foreground">
            Dossiers étudiants unifiés — Campus France, Parcoursup, Paris-Saclay
          </p>
        </div>
        <PermissionGate permission="candidates.create">
          <Button asChild>
            <Link to="/candidates/new">+ Nouveau candidat</Link>
          </Button>
        </PermissionGate>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Liste des candidats</CardTitle>
          <CardDescription>
            {data ? `${data.length} candidat(s)` : 'Chargement...'}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {isLoading && <p className="text-sm text-muted-foreground">Chargement...</p>}
          {isError && (
            <p className="text-sm text-red-600">Impossible de charger les candidats.</p>
          )}
          {data && data.length === 0 && (
            <p className="text-sm text-muted-foreground">Aucun candidat trouvé.</p>
          )}
          {data && data.length > 0 && (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-border text-left text-muted-foreground">
                    <th className="pb-3 pr-4 font-medium">Référence</th>
                    <th className="pb-3 pr-4 font-medium">Candidat</th>
                    <th className="pb-3 pr-4 font-medium">Nationalité</th>
                    <th className="pb-3 pr-4 font-medium">Statut</th>
                    <th className="pb-3 font-medium">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {data.map((candidate) => (
                    <tr key={candidate.id} className="border-b border-border last:border-0">
                      <td className="py-3 pr-4 font-mono text-xs">{candidate.referenceNumber}</td>
                      <td className="py-3 pr-4">
                        <div className="font-medium">
                          {candidate.firstName} {candidate.lastName}
                        </div>
                        <div className="text-muted-foreground">{candidate.email}</div>
                      </td>
                      <td className="py-3 pr-4">{candidate.nationality}</td>
                      <td className="py-3 pr-4">
                        <CandidateStatusBadge status={candidate.status} />
                      </td>
                      <td className="py-3">
                        <Button variant="outline" size="sm" asChild>
                          <Link to={`/candidates/${candidate.id}`}>Voir</Link>
                        </Button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
