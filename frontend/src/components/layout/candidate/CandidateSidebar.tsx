import {
  BookOpen,
  Calendar,
  CreditCard,
  FileText,
  FolderOpen,
  GraduationCap,
  Home,
  LayoutDashboard,
  Link2,
  ListChecks,
  Map,
} from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'

const navItems = [
  { to: '/', label: 'Tableau de bord', icon: LayoutDashboard, end: true },
  { to: '/my-file', label: 'Mon dossier', icon: FolderOpen },
  { to: '/my-file', label: 'Mes candidatures', icon: GraduationCap },
  { to: '#', label: 'Paiements', icon: CreditCard, disabled: true },
  { to: '#', label: 'Rendez-vous', icon: Calendar, disabled: true },
  { to: '#', label: 'Recherche de logement', icon: Home, disabled: true },
  { to: '/my-file', label: 'Documents', icon: FileText },
  { to: '#', label: 'Calendrier Campus France', icon: Calendar, disabled: true },
  { to: '#', label: 'Guides & Ressources', icon: BookOpen, disabled: true },
  { to: '#', label: 'Procédure & Étapes', icon: ListChecks, disabled: true },
  { to: '#', label: 'Parcoursup / Paris Saclay', icon: Map, disabled: true },
  { to: '#', label: 'Liens utiles', icon: Link2, disabled: true },
]

export function CandidateSidebar() {
  return (
    <aside className="flex h-screen w-64 shrink-0 flex-col bg-[#1a2744] text-slate-200">
      <div className="flex h-16 items-center gap-2 border-b border-white/10 px-5">
        <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-500 text-white">
          <GraduationCap className="h-5 w-5" />
        </div>
        <span className="text-lg font-semibold text-white">On&apos;Reach</span>
      </div>

      <nav className="flex flex-1 flex-col gap-0.5 overflow-y-auto px-2.5 py-3">
        {navItems.map(({ to, label, icon: Icon, end, disabled }) => {
          if (disabled) {
            return (
              <span
                key={label}
                className="flex cursor-not-allowed items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-500"
                title="Bientôt disponible"
              >
                <Icon className="h-4 w-4 shrink-0" />
                {label}
              </span>
            )
          }

          return (
            <NavLink
              key={label}
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
        })}
      </nav>

      <div className="m-3 rounded-xl bg-white/5 p-4">
        <p className="text-sm font-medium text-white">Besoin d&apos;aide ?</p>
        <p className="mt-1 text-xs text-slate-400">
          Votre conseillère est disponible pour vous accompagner.
        </p>
        <Button
          type="button"
          className="mt-3 w-full bg-blue-500 text-white hover:bg-blue-600"
          size="sm"
        >
          Contacter mon conseiller
        </Button>
      </div>
    </aside>
  )
}
