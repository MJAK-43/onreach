import { useMemo, useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
  fetchPathwayTracking,
  type PathwayTrackingFilters,
  type PathwayTrackingRow,
} from '@/lib/pathway-tracking-api'
import { staffPathwayRoute, PATHWAY_LABELS, type PathwayCode } from '@/lib/pathways-api'
import { formatRelativeDate } from '@/lib/candidate-utils'

const STATUS_OPTIONS = [
  { value: '', label: 'Tous les statuts' },
  { value: 'not_started', label: 'Non démarré' },
  { value: 'in_progress', label: 'En cours' },
  { value: 'blocked', label: 'Bloqué' },
  { value: 'accepted', label: 'Accepté' },
  { value: 'refused', label: 'Refusé' },
]

function statusVariant(status: string): 'success' | 'warning' | 'danger' | 'info' | 'default' {
  if (status === 'accepted') return 'success'
  if (status === 'in_progress') return 'info'
  if (status === 'blocked' || status === 'refused') return 'danger'
  return 'default'
}

function TrackingRow({ row }: { row: PathwayTrackingRow }) {
  return (
    <tr className="border-b border-border last:border-0">
      <td className="py-3 pr-4">
        <div className="font-medium">
          {row.candidateFirstName} {row.candidateLastName}
        </div>
        <div className="text-xs text-muted-foreground">{row.candidateEmail}</div>
      </td>
      <td className="py-3 pr-4">
        <div>{row.pathwayName}</div>
        <div className="text-xs text-muted-foreground">Campagne {row.campaignYear}</div>
      </td>
      <td className="py-3 pr-4">
        <Badge variant={statusVariant(row.status)}>{row.statusLabel}</Badge>
      </td>
      <td className="py-3 pr-4 tabular-nums">{row.progressPercent}%</td>
      <td className="py-3 pr-4">
        {row.nextSubStepTitle ? (
          <div>
            <div className="text-sm">{row.nextSubStepTitle}</div>
            {row.nextDueDate && (
              <div className="text-xs text-muted-foreground">Échéance {row.nextDueDate}</div>
            )}
          </div>
        ) : (
          <span className="text-muted-foreground">—</span>
        )}
      </td>
      <td className="py-3 pr-4 text-sm">
        {row.counselor ? `${row.counselor.firstName} ${row.counselor.lastName}` : '—'}
      </td>
      <td className="py-3 pr-4 text-muted-foreground">{formatRelativeDate(row.updatedAt)}</td>
      <td className="py-3">
        <Button variant="outline" size="sm" asChild>
          <Link to={staffPathwayRoute(row.candidateId, row.pathwayCode)}>Voir</Link>
        </Button>
      </td>
    </tr>
  )
}

export function PathwayTrackingPage() {
  const [filters, setFilters] = useState<PathwayTrackingFilters>({})
  const [searchInput, setSearchInput] = useState('')

  const query = useQuery({
    queryKey: ['pathway-tracking', filters],
    queryFn: () => fetchPathwayTracking(filters),
  })

  const pathwayOptions = useMemo(
    () => Object.entries(PATHWAY_LABELS) as Array<[PathwayCode, string]>,
    [],
  )

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Suivi des candidatures</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Vue consolidée des parcours Campus France, Parcoursup et Paris-Saclay.
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Filtres</CardTitle>
          <CardDescription>Affinez la liste par parcours, statut ou campagne.</CardDescription>
        </CardHeader>
        <CardContent className="flex flex-wrap gap-3">
          <Input
            placeholder="Rechercher un candidat…"
            value={searchInput}
            onChange={(event) => setSearchInput(event.target.value)}
            onKeyDown={(event) => {
              if (event.key === 'Enter') {
                setFilters((current) => ({ ...current, search: searchInput || undefined }))
              }
            }}
            className="max-w-xs"
          />
          <select
            className="h-10 rounded-md border border-input bg-background px-3 text-sm"
            value={filters.pathway ?? ''}
            onChange={(event) =>
              setFilters((current) => ({ ...current, pathway: event.target.value || undefined }))
            }
          >
            <option value="">Tous les parcours</option>
            {pathwayOptions.map(([code, label]) => (
              <option key={code} value={code}>
                {label}
              </option>
            ))}
          </select>
          <select
            className="h-10 rounded-md border border-input bg-background px-3 text-sm"
            value={filters.status ?? ''}
            onChange={(event) =>
              setFilters((current) => ({ ...current, status: event.target.value || undefined }))
            }
          >
            {STATUS_OPTIONS.map((option) => (
              <option key={option.value || 'all'} value={option.value}>
                {option.label}
              </option>
            ))}
          </select>
          <Input
            placeholder="Campagne (année)"
            value={filters.campaign ?? ''}
            onChange={(event) =>
              setFilters((current) => ({ ...current, campaign: event.target.value || undefined }))
            }
            className="max-w-[140px]"
          />
          <Button
            variant="outline"
            onClick={() => setFilters((current) => ({ ...current, search: searchInput || undefined }))}
          >
            Appliquer
          </Button>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Résultats</CardTitle>
          <CardDescription>
            {query.data ? `${query.data.total} parcours` : 'Chargement…'}
          </CardDescription>
        </CardHeader>
        <CardContent>
          {query.isLoading && <p className="text-sm text-muted-foreground">Chargement…</p>}
          {query.isError && (
            <p className="text-sm text-red-600">Impossible de charger le suivi des candidatures.</p>
          )}
          {query.data && query.data.items.length === 0 && (
            <p className="text-sm text-muted-foreground">Aucun parcours ne correspond aux filtres.</p>
          )}
          {query.data && query.data.items.length > 0 && (
            <div className="overflow-x-auto">
              <table className="w-full text-sm">
                <thead>
                  <tr className="border-b border-border text-left text-muted-foreground">
                    <th className="pb-3 pr-4 font-medium">Candidat</th>
                    <th className="pb-3 pr-4 font-medium">Parcours</th>
                    <th className="pb-3 pr-4 font-medium">Statut</th>
                    <th className="pb-3 pr-4 font-medium">Progression</th>
                    <th className="pb-3 pr-4 font-medium">Prochaine étape</th>
                    <th className="pb-3 pr-4 font-medium">Conseiller</th>
                    <th className="pb-3 pr-4 font-medium">MàJ</th>
                    <th className="pb-3 font-medium">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {query.data.items.map((row) => (
                    <TrackingRow key={row.pathwayId} row={row} />
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
