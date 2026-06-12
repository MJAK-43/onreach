import type { ReactNode } from 'react'

export function CandidatePageLayout({
  title,
  description,
  children,
}: {
  title: string
  description?: string
  children: ReactNode
}) {
  return (
    <div className="mx-auto max-w-[1200px] space-y-6">
      <div>
        <h1 className="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">{title}</h1>
        {description && (
          <p className="mt-1.5 max-w-2xl text-sm leading-relaxed text-slate-500">{description}</p>
        )}
      </div>
      {children}
    </div>
  )
}

export function CandidatePanel({
  children,
  className = '',
}: {
  children: ReactNode
  className?: string
}) {
  return (
    <section
      className={`rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 ${className}`}
    >
      {children}
    </section>
  )
}

export function CandidateSectionTitle({
  children,
  className = '',
}: {
  children: ReactNode
  className?: string
}) {
  return (
    <h2 className={`text-base font-semibold text-slate-900 ${className}`}>{children}</h2>
  )
}

export function PaymentDonut({ percent, paid }: { percent: number; paid: number }) {
  const r = 34
  const c = 2 * Math.PI * r
  const offset = c - (percent / 100) * c

  return (
    <div className="relative h-[96px] w-[96px] shrink-0">
      <svg className="h-full w-full -rotate-90" viewBox="0 0 100 100">
        <circle cx="50" cy="50" r={r} fill="none" stroke="#f1f5f9" strokeWidth="8" />
        <circle
          cx="50"
          cy="50"
          r={r}
          fill="none"
          stroke="#3b82f6"
          strokeWidth="8"
          strokeDasharray={c}
          strokeDashoffset={offset}
          strokeLinecap="round"
        />
      </svg>
      <div className="absolute inset-0 flex flex-col items-center justify-center">
        <span className="text-sm font-bold tabular-nums text-slate-900">{paid} €</span>
        <span className="text-[9px] text-slate-500">{percent}%</span>
      </div>
    </div>
  )
}

export function TrancheRow({
  label,
  amount,
  status,
  date,
}: {
  label: string
  amount: string
  status: 'paid' | 'pending'
  date?: string
}) {
  return (
    <div className="flex items-start justify-between gap-2 text-sm">
      <div className="min-w-0">
        <p className="font-medium leading-snug text-slate-800">{label}</p>
        <p className="mt-0.5 text-xs text-slate-400">
          {amount}
          {date ? ` · ${date}` : ''}
        </p>
      </div>
      <span
        className={`shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold ${
          status === 'paid'
            ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
            : 'bg-orange-50 text-orange-700 ring-1 ring-orange-100'
        }`}
      >
        {status === 'paid' ? 'Payé' : 'En attente'}
      </span>
    </div>
  )
}
