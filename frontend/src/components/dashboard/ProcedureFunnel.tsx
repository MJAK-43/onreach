import type { CandidateStats } from '@/lib/candidate-utils'

const STAGES = [
  { key: 'lead', label: 'Nouveau dossier' },
  { key: 'documents_pending', label: 'Documents en attente' },
  { key: 'in_progress', label: 'En cours' },
  { key: 'admission_obtained', label: 'Admission' },
  { key: 'visa_obtained', label: 'Visa' },
  { key: 'completed', label: 'Clôturé' },
] as const

export function ProcedureFunnel({ stats }: { stats: CandidateStats }) {
  const max = Math.max(...STAGES.map((s) => stats.byStatus[s.key] ?? 0), 1)

  return (
    <div className="space-y-3">
      {STAGES.map((stage) => {
        const count = stats.byStatus[stage.key] ?? 0
        const width = Math.round((count / max) * 100)

        return (
          <div key={stage.key}>
            <div className="mb-1 flex justify-between text-sm">
              <span>{stage.label}</span>
              <span className="font-medium">{count}</span>
            </div>
            <div className="h-2 overflow-hidden rounded-full bg-muted">
              <div
                className="h-full rounded-full bg-primary transition-all"
                style={{ width: `${width}%` }}
              />
            </div>
          </div>
        )
      })}
    </div>
  )
}
