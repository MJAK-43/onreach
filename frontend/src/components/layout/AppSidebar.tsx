import type { ReactNode } from 'react'
import { GraduationCap } from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { PermissionGate } from '@/components/auth/PermissionGate'
import type { NavItemConfig } from '@/config/navigation'
import { cn } from '@/lib/utils'

type AppSidebarProps = {
  items: NavItemConfig[]
  sections?: { title: string; items: NavItemConfig[] }[]
  footer?: ReactNode
}

function NavItemLink({ to, label, icon: Icon, end, disabled }: NavItemConfig) {
  if (disabled) {
    return (
      <span
        className="flex cursor-not-allowed items-center gap-2.5 rounded-lg border-l-[3px] border-transparent px-2.5 py-2 text-[13px] font-medium text-slate-500"
        title="Bientôt disponible"
      >
        <Icon className="h-4 w-4 shrink-0" />
        {label}
      </span>
    )
  }

  return (
    <NavLink
      to={to}
      end={end}
      className={({ isActive }) =>
        cn(
          'flex items-center gap-2.5 rounded-lg border-l-[3px] px-2.5 py-2 text-[13px] font-medium transition-colors',
          isActive
            ? 'border-blue-400 bg-white/10 text-white'
            : 'border-transparent text-slate-300 hover:bg-white/5 hover:text-white',
        )
      }
    >
      <Icon className="h-4 w-4 shrink-0" />
      {label}
    </NavLink>
  )
}

function GatedNavItem(item: NavItemConfig) {
  const link = <NavItemLink {...item} />

  if (item.permission) {
    return <PermissionGate permission={item.permission}>{link}</PermissionGate>
  }

  return link
}

export function AppSidebar({ items, sections = [], footer }: AppSidebarProps) {
  return (
    <aside className="fixed inset-y-0 left-0 z-30 flex w-64 flex-col bg-[#1a2744] text-slate-200">
      <div className="flex h-16 shrink-0 items-center gap-2 border-b border-white/10 px-5">
        <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500 text-white">
          <GraduationCap className="h-5 w-5" />
        </div>
        <span className="text-lg font-semibold text-white">On&apos;Reach</span>
      </div>

      <nav className="flex min-h-0 flex-1 flex-col gap-0.5 overflow-y-auto px-2.5 py-3">
        {items.map((item) => (
          <GatedNavItem key={`${item.to}-${item.label}`} {...item} />
        ))}

        {sections.map((section) => (
          <div key={section.title}>
            <p className="mb-1 mt-4 px-3 text-[10px] font-semibold uppercase tracking-wider text-slate-500">
              {section.title}
            </p>
            {section.items.map((item) => (
              <GatedNavItem key={`${section.title}-${item.to}-${item.label}`} {...item} />
            ))}
          </div>
        ))}
      </nav>

      {footer && <div className="shrink-0">{footer}</div>}
    </aside>
  )
}
