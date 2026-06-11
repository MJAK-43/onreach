import { CheckCircle2, Circle, Lock } from 'lucide-react'
import { STATUS_LABELS } from '@/lib/candidate-utils'

const STEPS = [
  'lead',
  'documents_pending',
  'in_progress',
  'admission_obtained',
  'visa_obtained',
  'completed',
] as const

function stepIndex(status: string): number {
  const idx = STEPS.indexOf(status as (typeof STEPS)[number])
  return idx >= 0 ? idx : 2
}

export function CandidateTimelineStepper({ status }: { status: string }) {
  const current = stepIndex(status)

  return (
    <div className="overflow-x-auto pb-2">
      <div className="flex min-w-[640px] items-center justify-between gap-2">
        {STEPS.map((step, index) => {
          const done = index < current
          const active = index === current
          const Icon = done ? CheckCircle2 : active ? Lock : Circle

          return (
            <div key={step} className="flex flex-1 flex-col items-center text-center">
              <Icon
                className={`h-6 w-6 ${
                  done ? 'text-green-600' : active ? 'text-orange-500' : 'text-muted-foreground'
                }`}
              />
              <p className={`mt-2 text-xs font-medium ${active ? 'text-foreground' : 'text-muted-foreground'}`}>
                {STATUS_LABELS[step]}
              </p>
            </div>
          )
        })}
      </div>
    </div>
  )
}
