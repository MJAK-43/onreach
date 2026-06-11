import { useQuery } from '@tanstack/react-query'
import { fetchHealth } from '@/lib/api'

export function DashboardPage() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ['health'],
    queryFn: fetchHealth,
  })

  return (
    <div className="space-y-6">
      <div className="rounded-xl border border-border bg-background p-6 shadow-sm">
        <h2 className="text-xl font-semibold">Bienvenue sur On&apos;Reach</h2>
        <p className="mt-2 text-muted-foreground">
          Plateforme SaaS d&apos;accompagnement des étudiants internationaux.
          Ce tableau de bord sera enrichi au Sprint 1.
        </p>
      </div>

      <div className="grid gap-4 md:grid-cols-3">
        <StatCard title="Candidats" value="—" />
        <StatCard title="Admissions" value="—" />
        <StatCard title="Visas" value="—" />
      </div>

      <div className="rounded-xl border border-border p-4">
        <p className="text-sm font-medium">État API</p>
        {isLoading && <p className="text-sm text-muted-foreground">Vérification...</p>}
        {isError && <p className="text-sm text-red-600">API indisponible</p>}
        {data && (
          <p className="text-sm text-green-700">
            Backend connecté — status: {data.status}
          </p>
        )}
      </div>
    </div>
  )
}

function StatCard({ title, value }: { title: string; value: string }) {
  return (
    <div className="rounded-xl border border-border bg-background p-4">
      <p className="text-sm text-muted-foreground">{title}</p>
      <p className="mt-1 text-2xl font-bold">{value}</p>
    </div>
  )
}
