import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  assignCandidateCounselor,
  fetchCandidates,
  fetchUsers,
  type CandidateListItem,
  type UserListItem,
} from '@/lib/api'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

function isCounselor(user: UserListItem): boolean {
  if (!user.roles?.length) return false
  return user.roles.some((role) => {
    if (typeof role === 'string') {
      return role.includes('COUNSELOR')
    }
    return role.code === 'COUNSELOR'
  })
}

function counselorLabel(candidate: CandidateListItem): string {
  const counselor = candidate.assignedCounselor
  if (!counselor) return 'Non assigné'
  if (typeof counselor === 'string') return 'Assigné'
  const name = `${counselor.firstName ?? ''} ${counselor.lastName ?? ''}`.trim()
  return name || counselor.email || 'Assigné'
}

function assignedCounselorId(candidate: CandidateListItem): string {
  const counselor = candidate.assignedCounselor
  if (!counselor || typeof counselor === 'string') return ''
  return counselor.id ?? ''
}

export function MatchingPage() {
  const queryClient = useQueryClient()

  const candidatesQuery = useQuery({ queryKey: ['candidates'], queryFn: fetchCandidates })
  const usersQuery = useQuery({ queryKey: ['users'], queryFn: fetchUsers })

  const counselors = (usersQuery.data ?? []).filter(isCounselor)

  const assignMutation = useMutation({
    mutationFn: ({
      candidateId,
      counselorId,
    }: {
      candidateId: string
      counselorId: string | null
    }) => assignCandidateCounselor(candidateId, counselorId),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['candidates'] })
    },
  })

  const candidates = candidatesQuery.data ?? []
  const isLoading = candidatesQuery.isLoading || usersQuery.isLoading

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Matching</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Attribuez un conseiller à chaque candidat — un candidat ne peut avoir qu&apos;un seul
          conseiller
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Attribution conseiller / candidat</CardTitle>
          <CardDescription>
            {candidates.length} candidat(s) — {counselors.length} conseiller(s) disponible(s)
          </CardDescription>
        </CardHeader>
        <CardContent>
          {isLoading && (
            <p className="text-sm text-muted-foreground">Chargement...</p>
          )}
          {(candidatesQuery.isError || usersQuery.isError) && (
            <p className="text-sm text-red-600">Impossible de charger les données.</p>
          )}
          {!isLoading && candidates.length === 0 && (
            <p className="text-sm text-muted-foreground">Aucun candidat trouvé.</p>
          )}
          {candidates.length > 0 && (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-border text-left text-muted-foreground">
                    <th className="pb-3 pr-4 font-medium">Candidat</th>
                    <th className="pb-3 pr-4 font-medium">Référence</th>
                    <th className="pb-3 pr-4 font-medium">Conseiller actuel</th>
                    <th className="pb-3 font-medium">Attribuer</th>
                  </tr>
                </thead>
                <tbody>
                  {candidates.map((candidate) => (
                    <tr key={candidate.id} className="border-b border-border last:border-0">
                      <td className="py-3 pr-4 font-medium">
                        {candidate.firstName} {candidate.lastName}
                      </td>
                      <td className="py-3 pr-4 text-muted-foreground">
                        {candidate.referenceNumber}
                      </td>
                      <td className="py-3 pr-4">{counselorLabel(candidate)}</td>
                      <td className="py-3">
                        <select
                          className="w-full max-w-xs rounded-md border border-input bg-background px-2 py-1.5 text-sm"
                          value={assignedCounselorId(candidate)}
                          disabled={assignMutation.isPending}
                          onChange={(event) => {
                            const counselorId = event.target.value || null
                            assignMutation.mutate({ candidateId: candidate.id, counselorId })
                          }}
                        >
                          <option value="">— Non assigné —</option>
                          {counselors.map((counselor) => (
                            <option key={counselor.id} value={counselor.id}>
                              {counselor.firstName} {counselor.lastName}
                            </option>
                          ))}
                        </select>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
          {assignMutation.isError && (
            <p className="mt-3 text-sm text-red-600">
              Erreur lors de l&apos;attribution. Veuillez réessayer.
            </p>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
