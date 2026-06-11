import { CheckCircle2, Circle, Lock } from 'lucide-react'

export interface ProcedureStep {
  label: string
  date?: string
  state: 'done' | 'current' | 'upcoming'
}

const DEFAULT_STEPS: ProcedureStep[] = [
  { label: 'Dossier créé', date: '15/02/2024', state: 'done' },
  { label: 'Dossier complet', date: '20/02/2024', state: 'done' },
  { label: 'Dépôt Campus France', date: '05/03/2024', state: 'done' },
  { label: 'Entretien CF', date: '22/03/2024', state: 'done' },
  { label: 'Admission obtenue', date: '10/05/2024', state: 'current' },
  { label: 'Demande de visa', state: 'upcoming' },
  { label: 'Visa obtenu', state: 'upcoming' },
  { label: 'Voyage & Install.', state: 'upcoming' },
]

export function buildStepsFromStatus(status: string): ProcedureStep[] {
  const order = [
    'lead',
    'profile_incomplete',
    'documents_pending',
    'in_progress',
    'admission_obtained',
    'visa_obtained',
    'completed',
  ]
  const idx = Math.max(order.indexOf(status), 4)

  return DEFAULT_STEPS.map((step, i) => {
    if (i < idx) return { ...step, state: 'done' as const }
    if (i === idx) return { ...step, state: 'current' as const }
    return { ...step, state: 'upcoming' as const, date: undefined }
  })
}

export function CandidateProcedureTimeline({ status }: { status: string }) {
  const steps = buildStepsFromStatus(status)
  const currentIndex = steps.findIndex((s) => s.state === 'current')
  const progressPct = currentIndex >= 0 ? (currentIndex / (steps.length - 1)) * 100 : 0

  return (
    <div className="overflow-x-auto pb-1">
      <div className="relative min-w-[720px] px-2">
        <div className="absolute left-8 right-8 top-5 h-1 rounded-full bg-slate-100">
          <div
            className="h-full rounded-full bg-gradient-to-r from-green-500 to-orange-400 transition-all duration-500"
            style={{ width: `${progressPct}%` }}
          />
        </div>

        <div className="relative flex justify-between gap-1">
          {steps.map((step) => (
            <div
              key={step.label}
              className="flex w-[11%] min-w-[72px] flex-col items-center text-center"
            >
              <div
                className={`flex h-10 w-10 items-center justify-center rounded-full border-2 bg-white shadow-sm ${
                  step.state === 'done'
                    ? 'border-green-500 text-green-600'
                    : step.state === 'current'
                      ? 'border-orange-400 text-orange-500 ring-4 ring-orange-100'
                      : 'border-slate-200 text-slate-300'
                }`}
              >
                {step.state === 'done' && <CheckCircle2 className="h-5 w-5" />}
                {step.state === 'current' && <Lock className="h-4 w-4" />}
                {step.state === 'upcoming' && <Circle className="h-4 w-4" />}
              </div>
              <p
                className={`mt-2.5 text-[11px] font-medium leading-snug ${
                  step.state === 'current' ? 'text-slate-900' : 'text-slate-500'
                }`}
              >
                {step.label}
              </p>
              {step.date && (
                <p className="mt-0.5 text-[10px] text-slate-400">{step.date}</p>
              )}
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
