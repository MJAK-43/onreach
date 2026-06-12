import type { ReactNode } from 'react'
import { cn } from '@/lib/utils'

const variants = {
  default: 'bg-slate-100 text-slate-700 ring-slate-200',
  success: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
  warning: 'bg-orange-50 text-orange-700 ring-orange-100',
  danger: 'bg-red-50 text-red-700 ring-red-100',
  info: 'bg-blue-50 text-blue-700 ring-blue-100',
} as const

export function Badge({
  children,
  variant = 'default',
  className,
}: {
  children: ReactNode
  variant?: keyof typeof variants
  className?: string
}) {
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1',
        variants[variant],
        className,
      )}
    >
      {children}
    </span>
  )
}
