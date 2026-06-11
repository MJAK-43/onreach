import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { GraduationCap, Plane, Trophy, Users } from 'lucide-react'
import { fetchCandidates } from '@/lib/api'
import { computeCandidateStats } from '@/lib/candidate-utils'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { getPrimaryRole } from '@/lib/roles'
import { PermissionGate } from '@/components/auth/PermissionGate'
import { AlertsPanel } from '@/components/dashboard/AlertsPanel'
import { CandidateTrackingTable } from '@/components/dashboard/CandidateTrackingTable'
import { CountryDistribution } from '@/components/dashboard/CountryDistribution'
import { KpiCard } from '@/components/dashboard/KpiCard'
import { ProcedureFunnel } from '@/components/dashboard/ProcedureFunnel'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'

export function StaffDashboardPage() {
  const { data: user } = useCurrentUser()
  const role = getPrimaryRole(user?.roles)
  const isCounselor = role === 'COUNSELOR'

  const { data: candidates = [], isLoading } = useQuery({
    queryKey: ['candidates'],
    queryFn: fetchCandidates,
  })

  const stats = computeCandidateStats(candidates)
  const inProgressPct = stats.total > 0 ? Math.round((stats.inProgress / stats.total) * 100) : 0

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <h2 className="text-xl font-semibold">
            Bonjour {user?.firstName} 👋
          </h2>
          <p className="mt-1 text-sm text-muted-foreground">
            {isCounselor
              ? 'Voici l\'ensemble de vos dossiers et activités du jour.'
              : 'Vue d\'ensemble de la plateforme On\'Reach.'}
          </p>
        </div>
        <PermissionGate permission="candidates.create">
          <Button asChild>
            <Link to="/candidates/new">+ Nouveau candidat</Link>
          </Button>
        </PermissionGate>
      </div>

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <KpiCard
          label={isCounselor ? 'Candidats suivis' : 'Total candidats'}
          value={isLoading ? '…' : stats.total}
          hint={isCounselor ? 'Dossiers assignés' : 'Tous les dossiers'}
          icon={Users}
        />
        <KpiCard
          label="Dossiers en cours"
          value={isLoading ? '…' : stats.inProgress}
          hint={`${inProgressPct}% du total`}
          icon={GraduationCap}
        />
        <KpiCard
          label="Admissions obtenues"
          value={isLoading ? '…' : stats.admissions}
          icon={Trophy}
          accent="text-green-600"
        />
        <KpiCard
          label="Visas obtenus"
          value={isLoading ? '…' : stats.visas}
          icon={Plane}
          accent="text-blue-600"
        />
      </div>

      <div className="grid gap-6 xl:grid-cols-3">
        <Card className="xl:col-span-2">
          <CardHeader className="flex flex-row items-center justify-between">
            <div>
              <CardTitle>Suivi des candidatures</CardTitle>
              <CardDescription>Derniers dossiers mis à jour</CardDescription>
            </div>
            <Button variant="outline" size="sm" asChild>
              <Link to="/candidates">Voir tout</Link>
            </Button>
          </CardHeader>
          <CardContent>
            {isLoading ? (
              <p className="text-sm text-muted-foreground">Chargement...</p>
            ) : (
              <CandidateTrackingTable candidates={candidates} />
            )}
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Alertes & tâches</CardTitle>
            <CardDescription>Points d'attention</CardDescription>
          </CardHeader>
          <CardContent>
            <AlertsPanel candidates={candidates} />
          </CardContent>
        </Card>
      </div>

      <div className="grid gap-6 lg:grid-cols-2">
        <Card>
          <CardHeader>
            <CardTitle>Étapes de la procédure</CardTitle>
            <CardDescription>Répartition par étape</CardDescription>
          </CardHeader>
          <CardContent>
            <ProcedureFunnel stats={stats} />
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Dossiers par pays</CardTitle>
            <CardDescription>Origine des candidats</CardDescription>
          </CardHeader>
          <CardContent>
            <CountryDistribution stats={stats} />
          </CardContent>
        </Card>
      </div>
    </div>
  )
}
