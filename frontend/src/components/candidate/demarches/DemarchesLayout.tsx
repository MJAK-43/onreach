import type { ReactNode } from 'react'
import { NavLink, Outlet } from 'react-router-dom'
import { cn } from '@/lib/utils'

const NAV_ITEMS: { to: string; label: string; end?: boolean }[] = [
  { to: '/demarches', label: 'Vue globale', end: true },
  { to: '/demarches/campus-france', label: 'Campus France' },
  { to: '/demarches/parcoursup', label: 'Parcoursup' },
  { to: '/demarches/paris-saclay', label: 'Paris-Saclay' },
  { to: '/demarches/historique', label: 'Historique' },
]

export function DemarchesLayout({ children }: { children?: ReactNode }) {
  return (
    <div className="mx-auto max-w-[1200px] space-y-6">
      <div>
        <p className="text-xs font-semibold uppercase tracking-wider text-blue-600">Mes démarches</p>
        <h1 className="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
          Suivi de vos candidatures
        </h1>
        <p className="mt-1.5 max-w-2xl text-sm text-slate-500">
          Campus France, Parcoursup et Paris-Saclay — une vue unifiée de votre parcours.
        </p>
      </div>

      <nav className="flex gap-1 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50/80 p-1 lg:hidden">
        {NAV_ITEMS.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            end={item.end}
            className={({ isActive }) =>
              cn(
                'shrink-0 rounded-lg px-3 py-2 text-xs font-medium transition-colors',
                isActive ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800',
              )
            }
          >
            {item.label}
          </NavLink>
        ))}
      </nav>

      <div className="grid gap-6 lg:grid-cols-[220px_1fr]">
        <aside className="hidden lg:block">
          <nav className="sticky top-24 space-y-0.5 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
            {NAV_ITEMS.map((item) => (
              <NavLink
                key={item.to}
                to={item.to}
                end={item.end}
                className={({ isActive }) =>
                  cn(
                    'flex rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                    isActive
                      ? 'bg-blue-50 text-blue-700'
                      : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
                  )
                }
              >
                {item.label}
              </NavLink>
            ))}
          </nav>
        </aside>

        <div className="min-w-0">{children ?? <Outlet />}</div>
      </div>
    </div>
  )
}
