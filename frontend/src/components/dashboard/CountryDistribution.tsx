import type { CandidateStats } from '@/lib/candidate-utils'

export function CountryDistribution({ stats }: { stats: CandidateStats }) {
  const entries = Object.entries(stats.byCountry).sort((a, b) => b[1] - a[1])
  const max = entries[0]?.[1] ?? 1

  if (entries.length === 0) {
    return <p className="text-sm text-muted-foreground">Aucune donnée.</p>
  }

  return (
    <div className="space-y-3">
      {entries.map(([country, count]) => (
        <div key={country}>
          <div className="mb-1 flex justify-between text-sm">
            <span>{country}</span>
            <span className="font-medium">{count}</span>
          </div>
          <div className="h-2 overflow-hidden rounded-full bg-muted">
            <div
              className="h-full rounded-full bg-blue-500"
              style={{ width: `${Math.round((count / max) * 100)}%` }}
            />
          </div>
        </div>
      ))}
    </div>
  )
}
