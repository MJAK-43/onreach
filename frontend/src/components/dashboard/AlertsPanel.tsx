import { AlertCircle, FileWarning, Users } from 'lucide-react'
import type { CandidateListItem } from '@/lib/api'

interface AlertsPanelProps {
  candidates: CandidateListItem[]
}

export function AlertsPanel({ candidates }: AlertsPanelProps) {
  const missingDocs = candidates.filter((c) => c.status === 'documents_pending').length
  const blocked = candidates.filter((c) => c.status === 'suspended').length
  const incomplete = candidates.filter((c) => c.status === 'profile_incomplete').length

  const alerts = [
    {
      icon: FileWarning,
      label: `${missingDocs} dossier(s) avec documents manquants`,
      tone: missingDocs > 0 ? 'text-amber-700' : 'text-muted-foreground',
    },
    {
      icon: Users,
      label: `${incomplete} profil(s) incomplet(s)`,
      tone: incomplete > 0 ? 'text-orange-700' : 'text-muted-foreground',
    },
    {
      icon: AlertCircle,
      label: `${blocked} dossier(s) suspendu(s)`,
      tone: blocked > 0 ? 'text-red-700' : 'text-muted-foreground',
    },
  ]

  return (
    <ul className="space-y-3">
      {alerts.map((alert) => (
        <li key={alert.label} className="flex items-start gap-3 text-sm">
          <alert.icon className={`mt-0.5 h-4 w-4 shrink-0 ${alert.tone}`} />
          <span>{alert.label}</span>
        </li>
      ))}
    </ul>
  )
}
