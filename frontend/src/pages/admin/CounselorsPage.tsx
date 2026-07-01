import { useQuery } from '@tanstack/react-query'
import { fetchCandidates, fetchRoles, fetchUsers } from '@/lib/api'
import { isCounselorUser } from '@/lib/user-roles'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

function counselorCandidateCount(
  counselorId: string,
  candidates: { assignedCounselor?: { id?: string } | string | null }[],
): number {
  return candidates.filter((candidate) => {
    const counselor = candidate.assignedCounselor
    if (!counselor || typeof counselor === 'string') return false
    return counselor.id === counselorId
  }).length
}

export function CounselorsPage() {
  const usersQuery = useQuery({ queryKey: ['users'], queryFn: fetchUsers })
  const rolesQuery = useQuery({ queryKey: ['roles'], queryFn: fetchRoles })
  const candidatesQuery = useQuery({ queryKey: ['candidates'], queryFn: fetchCandidates })

  const roles = rolesQuery.data ?? []
  const counselors = (usersQuery.data ?? []).filter((user) => isCounselorUser(user, roles))
  const candidates = candidatesQuery.data ?? []

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Conseillers</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Liste des conseillers actifs sur la plateforme
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Conseillers inscrits</CardTitle>
          <CardDescription>
            {counselors.length} conseiller(s) — un conseiller peut accompagner plusieurs candidats
          </CardDescription>
        </CardHeader>
        <CardContent>
          {usersQuery.isLoading && (
            <p className="text-sm text-muted-foreground">Chargement...</p>
          )}
          {usersQuery.isError && (
            <p className="text-sm text-red-600">Impossible de charger les conseillers.</p>
          )}
          {usersQuery.isSuccess && counselors.length === 0 && (
            <p className="text-sm text-muted-foreground">Aucun conseiller trouvé.</p>
          )}
          {counselors.length > 0 && (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-border text-left text-muted-foreground">
                    <th className="pb-3 pr-4 font-medium">Nom</th>
                    <th className="pb-3 pr-4 font-medium">E-mail</th>
                    <th className="pb-3 pr-4 font-medium">Statut</th>
                    <th className="pb-3 font-medium">Candidats assignés</th>
                  </tr>
                </thead>
                <tbody>
                  {counselors.map((counselor) => (
                    <tr key={counselor.id} className="border-b border-border last:border-0">
                      <td className="py-3 pr-4 font-medium">
                        {counselor.firstName} {counselor.lastName}
                      </td>
                      <td className="py-3 pr-4">{counselor.email}</td>
                      <td className="py-3 pr-4">
                        {counselor.isActive ? 'Actif' : 'Inactif'}
                      </td>
                      <td className="py-3">
                        {counselorCandidateCount(counselor.id, candidates)}
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
