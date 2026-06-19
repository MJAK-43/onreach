import type { ReactNode } from 'react'
import { NavLink, Outlet } from 'react-router-dom'
import { useMyPathways } from '@/hooks/useMyPathways'
import { PATHWAY_LABELS, PATHWAY_ROUTES, type PathwayCode } from '@/lib/pathways-api'
import { cn } from '@/lib/utils'

const STATIC_NAV = [
  { to: '/demarches', label: 'Vue globale', end: true as const },
  { to: '/demarches/historique', label: 'Historique' },
]

const PATHWAY_ORDER: PathwayCode[] = ['parcoursup', 'campus_france', 'paris_saclay']

function buildNavItems(pathwayCodes: PathwayCode[]) {
  const pathwayItems = PATHWAY_ORDER.filter((code) => pathwayCodes.includes(code)).map((code) => ({
    to: PATHWAY_ROUTES[code],
    label: PATHWAY_LABELS[code],
  }))

  return [STATIC_NAV[0], ...pathwayItems, STATIC_NAV[1]]
}

function NavItems({ items, mobile }: { items: { to: string; label: string; end?: boolean }[]; mobile?: boolean }) {
  return (
    <>
      {items.map((item) => (
        <NavLink
          key={item.to}
          to={item.to}
          end={item.end}
          className={({ isActive }) =>
            cn(
              mobile
                ? 'shrink-0 rounded-lg px-3 py-2 text-xs font-medium transition-colors'
                : 'flex rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
              isActive
                ? mobile
                  ? 'bg-white text-slate-900 shadow-sm'
                  : 'bg-blue-50 text-blue-700'
                : mobile
                  ? 'text-slate-500 hover:text-slate-800'
                  : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
            )
          }
        >
          {item.label}
        </NavLink>
      ))}
    </>
  )
}

export function DemarchesLayout({ children }: { children?: ReactNode }) {
  const { data } = useMyPathways()
  const pathwayCodes = data?.pathways.map((p) => p.code) ?? PATHWAY_ORDER
  const navItems = buildNavItems(pathwayCodes)

  return (
    <div className="mx-auto max-w-[1200px] space-y-6">
      <div>
        <p className="text-xs font-semibold uppercase tracking-wider text-blue-600">Mes démarches</p>
        <h1 className="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
          Suivi de vos candidatures
        </h1>
        <p className="mt-1.5 max-w-2xl text-sm text-slate-500">
          {data?.studyApplicationTypeLabel
            ? `Parcours ${data.studyApplicationTypeLabel.toLowerCase()} — Campus France, Parcoursup et Paris-Saclay.`
            : 'Campus France, Parcoursup et Paris-Saclay — une vue unifiée de votre parcours.'}
        </p>
      </div>

      <nav className="flex gap-1 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50/80 p-1 lg:hidden">
        <NavItems items={navItems} mobile />
      </nav>

      <div className="grid gap-6 lg:grid-cols-[220px_1fr]">
        <aside className="hidden lg:block">
          <nav className="sticky top-24 space-y-0.5 rounded-2xl border border-slate-200 bg-white p-2 shadow-sm">
            <NavItems items={navItems} />
          </nav>
        </aside>

        <div className="min-w-0">{children ?? <Outlet />}</div>
      </div>
    </div>
  )
}
