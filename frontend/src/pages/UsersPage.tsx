import { useQuery } from '@tanstack/react-query'
import { fetchUsers } from '@/lib/api'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

export function UsersPage() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ['users'],
    queryFn: fetchUsers,
  })

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Utilisateurs</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Gestion des comptes utilisateurs de la plateforme
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Liste des utilisateurs</CardTitle>
          <CardDescription>
            {data ? `${data.length} utilisateur(s)` : 'Chargement...'}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {isLoading && (
            <p className="text-sm text-muted-foreground">Chargement...</p>
          )}
          {isError && (
            <p className="text-sm text-red-600">Impossible de charger les utilisateurs.</p>
          )}
          {data && data.length === 0 && (
            <p className="text-sm text-muted-foreground">Aucun utilisateur trouvé.</p>
          )}
          {data && data.length > 0 && (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-border text-left text-muted-foreground">
                    <th className="pb-3 pr-4 font-medium">Nom</th>
                    <th className="pb-3 pr-4 font-medium">E-mail</th>
                    <th className="pb-3 pr-4 font-medium">Statut</th>
                    <th className="pb-3 font-medium">MFA</th>
                  </tr>
                </thead>
                <tbody>
                  {data.map((user) => (
                    <tr key={user.id} className="border-b border-border last:border-0">
                      <td className="py-3 pr-4 font-medium">
                        {user.firstName} {user.lastName}
                      </td>
                      <td className="py-3 pr-4">{user.email}</td>
                      <td className="py-3 pr-4">
                        <StatusBadge active={user.isActive} />
                      </td>
                      <td className="py-3">
                        {user.mfaEnabled ? 'Activé' : 'Désactivé'}
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

function StatusBadge({ active }: { active: boolean }) {
  return (
    <span
      className={
        active
          ? 'rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700'
          : 'rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700'
      }
    >
      {active ? 'Actif' : 'Inactif'}
    </span>
  )
}
