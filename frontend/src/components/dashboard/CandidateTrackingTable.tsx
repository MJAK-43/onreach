import { Link } from 'react-router-dom'
import type { CandidateListItem } from '@/lib/api'
import { formatRelativeDate } from '@/lib/candidate-utils'
import { CandidateStatusBadge } from '@/components/dashboard/CandidateStatusBadge'
import { Button } from '@/components/ui/button'

interface CandidateTrackingTableProps {
  candidates: CandidateListItem[]
  limit?: number
}

export function CandidateTrackingTable({ candidates, limit = 8 }: CandidateTrackingTableProps) {
  const rows = candidates.slice(0, limit)

  if (rows.length === 0) {
    return <p className="text-sm text-muted-foreground">Aucun candidat pour le moment.</p>
  }

  return (
    <div className="overflow-x-auto">
      <table className="w-full text-sm">
        <thead>
          <tr className="border-b border-border text-left text-muted-foreground">
            <th className="pb-3 pr-4 font-medium">Candidat</th>
            <th className="pb-3 pr-4 font-medium">Nationalité</th>
            <th className="pb-3 pr-4 font-medium">Statut</th>
            <th className="pb-3 pr-4 font-medium">Complétude</th>
            <th className="pb-3 pr-4 font-medium">Dernière MàJ</th>
            <th className="pb-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody>
          {rows.map((candidate) => (
            <tr key={candidate.id} className="border-b border-border last:border-0">
              <td className="py-3 pr-4">
                <div className="font-medium">
                  {candidate.firstName} {candidate.lastName}
                </div>
                <div className="text-xs text-muted-foreground">{candidate.email}</div>
              </td>
              <td className="py-3 pr-4">{candidate.nationality}</td>
              <td className="py-3 pr-4">
                <CandidateStatusBadge status={candidate.status} />
              </td>
              <td className="py-3 pr-4">{candidate.completionPercent ?? 0}%</td>
              <td className="py-3 pr-4 text-muted-foreground">
                {formatRelativeDate(candidate.updatedAt)}
              </td>
              <td className="py-3">
                <Button variant="outline" size="sm" asChild>
                  <Link to={`/candidates/${candidate.id}`}>Voir</Link>
                </Button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  )
}
