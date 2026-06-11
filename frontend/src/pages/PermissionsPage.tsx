import { useQuery } from '@tanstack/react-query'
import { fetchPermissions } from '@/lib/api'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

export function PermissionsPage() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ['permissions'],
    queryFn: fetchPermissions,
  })

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Permissions</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Permissions granulaires du contrôle d&apos;accès
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Liste des permissions</CardTitle>
          <CardDescription>
            {data ? `${data.length} permission(s)` : 'Chargement...'}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {isLoading && (
            <p className="text-sm text-muted-foreground">Chargement...</p>
          )}
          {isError && (
            <p className="text-sm text-red-600">Impossible de charger les permissions.</p>
          )}
          {data && data.length === 0 && (
            <p className="text-sm text-muted-foreground">Aucune permission trouvée.</p>
          )}
          {data && data.length > 0 && (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-border text-left text-muted-foreground">
                    <th className="pb-3 pr-4 font-medium">Code</th>
                    <th className="pb-3 pr-4 font-medium">Nom</th>
                    <th className="pb-3 font-medium">Description</th>
                  </tr>
                </thead>
                <tbody>
                  {data.map((permission) => (
                    <tr key={permission.id} className="border-b border-border last:border-0">
                      <td className="py-3 pr-4 font-mono text-xs">{permission.code}</td>
                      <td className="py-3 pr-4 font-medium">{permission.name}</td>
                      <td className="py-3 text-muted-foreground">
                        {permission.description ?? '—'}
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
