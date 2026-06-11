import { useQuery } from '@tanstack/react-query'
import { fetchRoles } from '@/lib/api'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

export function RolesPage() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ['roles'],
    queryFn: fetchRoles,
  })

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Rôles</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Rôles et niveaux d&apos;accès du système
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Liste des rôles</CardTitle>
          <CardDescription>
            {data ? `${data.length} rôle(s)` : 'Chargement...'}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {isLoading && (
            <p className="text-sm text-muted-foreground">Chargement...</p>
          )}
          {isError && (
            <p className="text-sm text-red-600">Impossible de charger les rôles.</p>
          )}
          {data && data.length === 0 && (
            <p className="text-sm text-muted-foreground">Aucun rôle trouvé.</p>
          )}
          {data && data.length > 0 && (
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {data.map((role) => (
                <div
                  key={role.id}
                  className="rounded-lg border border-border p-4"
                >
                  <div className="flex items-start justify-between gap-2">
                    <p className="font-medium">{role.name}</p>
                    {role.isSystem && (
                      <span className="shrink-0 rounded-full bg-muted px-2 py-0.5 text-xs">
                        Système
                      </span>
                    )}
                  </div>
                  <p className="mt-1 text-xs text-muted-foreground">{role.code}</p>
                  {role.description && (
                    <p className="mt-2 text-sm text-muted-foreground">
                      {role.description}
                    </p>
                  )}
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  )
}
