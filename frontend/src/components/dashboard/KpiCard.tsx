import { Card, CardContent } from '@/components/ui/card'
import type { LucideIcon } from 'lucide-react'

interface KpiCardProps {
  label: string
  value: string | number
  hint?: string
  icon?: LucideIcon
  accent?: string
}

export function KpiCard({ label, value, hint, icon: Icon, accent = 'text-primary' }: KpiCardProps) {
  return (
    <Card>
      <CardContent className="flex items-start justify-between p-5">
        <div>
          <p className="text-sm text-muted-foreground">{label}</p>
          <p className="mt-1 text-2xl font-bold">{value}</p>
          {hint && <p className="mt-1 text-xs text-muted-foreground">{hint}</p>}
        </div>
        {Icon && (
          <div className={`rounded-lg bg-muted p-2 ${accent}`}>
            <Icon className="h-5 w-5" />
          </div>
        )}
      </CardContent>
    </Card>
  )
}
