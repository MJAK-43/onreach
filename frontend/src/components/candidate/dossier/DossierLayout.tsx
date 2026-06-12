import type { ReactNode } from 'react'
import { NavLink, Outlet } from 'react-router-dom'
import { cn } from '@/lib/utils'

const NAV_ITEMS: { to: string; label: string; end?: boolean }[] = [
  { to: '/my-file', label: 'Vue générale', end: true },
  { to: '/my-file/personal', label: 'Informations personnelles' },
  { to: '/my-file/contact', label: 'Coordonnées' },
  { to: '/my-file/academic', label: 'Parcours académique' },
  { to: '/my-file/languages', label: 'Langues' },
  { to: '/my-file/study-project', label: "Projet d'études" },
  { to: '/my-file/career-project', label: 'Projet professionnel' },
  { to: '/my-file/financing', label: 'Financement' },
  { to: '/my-file/guarantor', label: 'Garant' },
  { to: '/my-file/experiences', label: 'Expériences' },
  { to: '/my-file/documents', label: 'Documents associés' },
  { to: '/my-file/counselor', label: 'Conseiller attribué' },
  { to: '/my-file/history', label: 'Historique' },
]

export function DossierLayout({ children }: { children?: ReactNode }) {
  return (
    <div className="mx-auto max-w-[1200px] space-y-6">
      <div>
        <p className="text-xs font-semibold uppercase tracking-wider text-blue-600">Mon dossier</p>
        <h1 className="mt-1 text-xl font-bold tracking-tight text-slate-900 dark:text-slate-100 sm:text-2xl">
          Fiche administrative
        </h1>
        <p className="mt-1.5 max-w-2xl text-sm text-slate-500 dark:text-slate-400">
          Votre dossier étudiant numérique — consultez, complétez et suivez votre avancement.
        </p>
      </div>

      <nav className="flex gap-1 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50/80 p-1 dark:border-slate-700 dark:bg-slate-900/50 lg:hidden">
        {NAV_ITEMS.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            end={item.end}
            className={({ isActive }) =>
              cn(
                'shrink-0 rounded-lg px-3 py-2 text-xs font-medium transition-colors',
                isActive
                  ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-800 dark:text-slate-100'
                  : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200',
              )
            }
          >
            {item.label}
          </NavLink>
        ))}
      </nav>

      <div className="grid gap-6 lg:grid-cols-[240px_1fr] lg:items-stretch">
        <aside className="hidden lg:block lg:min-h-[calc(100vh-7rem)]">
          <nav className="sticky top-24 flex h-full min-h-[calc(100vh-7rem)] flex-col space-y-0.5 overflow-y-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            {NAV_ITEMS.map((item) => (
              <NavLink
                key={item.to}
                to={item.to}
                end={item.end}
                className={({ isActive }) =>
                  cn(
                    'flex rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                    isActive
                      ? 'bg-blue-50 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                      : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
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
