import { STATUS_COLORS, STATUS_LABELS } from '@/lib/candidate-utils'

export function CandidateStatusBadge({ status }: { status: string }) {
  const label = STATUS_LABELS[status] ?? status
  const color = STATUS_COLORS[status] ?? 'bg-slate-100 text-slate-800'

  return (
    <span className={`inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium ${color}`}>
      {label}
    </span>
  )
}
