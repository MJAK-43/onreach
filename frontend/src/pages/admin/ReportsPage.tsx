import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

export function ReportsPage() {
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Rapports statistiques</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Indicateurs et tableaux de bord de la plateforme
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Bientôt disponible</CardTitle>
          <CardDescription>
            Les rapports statistiques (candidatures, rendez-vous, paiements) seront
            disponibles prochainement.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">
            Cette section permettra de consulter les indicateurs clés de la plateforme.
          </p>
        </CardContent>
      </Card>
    </div>
  )
}
