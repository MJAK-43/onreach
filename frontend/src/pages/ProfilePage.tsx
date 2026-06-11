import { useCurrentUser } from '@/hooks/useCurrentUser'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

export function ProfilePage() {
  const { data: user, isLoading, isError } = useCurrentUser()

  if (isLoading) {
    return <p className="text-sm text-muted-foreground">Chargement du profil...</p>
  }

  if (isError || !user) {
    return <p className="text-sm text-red-600">Impossible de charger le profil.</p>
  }

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Mon profil</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Informations de votre compte utilisateur
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>{user.fullName}</CardTitle>
          <CardDescription>{user.email}</CardDescription>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid gap-4 sm:grid-cols-2">
            <InfoItem label="Prénom" value={user.firstName} />
            <InfoItem label="Nom" value={user.lastName} />
            <InfoItem label="Statut" value={user.isActive ? 'Actif' : 'Inactif'} />
            <InfoItem
              label="Authentification à deux facteurs"
              value={user.mfaEnabled ? 'Activée' : 'Désactivée'}
            />
          </div>

          <div>
            <p className="text-sm font-medium">Rôles</p>
            <div className="mt-2 flex flex-wrap gap-2">
              {user.roles.map((role) => (
                <span
                  key={role}
                  className="rounded-full bg-muted px-3 py-1 text-xs font-medium"
                >
                  {role}
                </span>
              ))}
            </div>
          </div>

          {user.createdAt && (
            <InfoItem
              label="Compte créé le"
              value={new Date(user.createdAt).toLocaleDateString('fr-FR')}
            />
          )}
        </CardContent>
      </Card>
    </div>
  )
}

function InfoItem({ label, value }: { label: string; value: string }) {
  return (
    <div>
      <p className="text-sm text-muted-foreground">{label}</p>
      <p className="mt-1 font-medium">{value}</p>
    </div>
  )
}
