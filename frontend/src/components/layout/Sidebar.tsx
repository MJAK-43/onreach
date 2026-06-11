import {
  FileText,
  GraduationCap,
  KeyRound,
  LayoutDashboard,
  Settings,
  Shield,
  UserCog,
  Users,
} from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { PermissionGate } from '@/components/auth/PermissionGate'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { getPrimaryRole } from '@/lib/roles'
import { cn } from '@/lib/utils'

type NavItem = {
  to: string
  label: string
  icon: typeof LayoutDashboard
  permission?: string
  end?: boolean
}

export function Sidebar() {
  const { data: user } = useCurrentUser()
  const role = getPrimaryRole(user?.roles)

  const candidateNav: NavItem[] = [
    { to: '/', label: 'Tableau de bord', icon: LayoutDashboard, end: true },
    { to: '/my-file', label: 'Mon dossier', icon: FileText, permission: 'candidates.view' },
    { to: '/settings', label: 'Paramètres', icon: Settings },
  ]

  const staffNav: NavItem[] = [
    { to: '/', label: 'Tableau de bord', icon: LayoutDashboard, end: true },
    { to: '/candidates', label: 'Candidats', icon: GraduationCap, permission: 'candidates.view' },
    { to: '/settings', label: 'Paramètres', icon: Settings },
  ]

  const adminNavItems: NavItem[] = [
    { to: '/admin/users', label: 'Utilisateurs', icon: Users, permission: 'users.view' },
    { to: '/admin/roles', label: 'Rôles', icon: UserCog, permission: 'roles.view' },
    { to: '/admin/permissions', label: 'Permissions', icon: KeyRound, permission: 'permissions.view' },
    { to: '/admin/security', label: 'Sécurité', icon: Shield },
  ]

  const mainNav = role === 'CANDIDATE' ? candidateNav : staffNav
  const showAdmin = role === 'SUPER_ADMIN' || role === 'ADMIN'

  return (
    <aside className="flex h-full w-64 flex-col border-r border-border bg-sidebar">
      <div className="flex h-16 items-center border-b border-border px-6">
        <span className="text-lg font-semibold tracking-tight">On&apos;Reach</span>
      </div>
      <nav className="flex flex-1 flex-col gap-1 p-4">
        {mainNav.map(({ to, label, icon: Icon, permission, end }) => {
          const link = (
            <NavLink
              key={to}
              to={to}
              className={({ isActive }) =>
                cn(
                  'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                  isActive
                    ? 'bg-primary text-primary-foreground'
                    : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                )
              }
              end={end}
            >
              <Icon className="h-4 w-4" />
              {label}
            </NavLink>
          )

          if (permission) {
            return (
              <PermissionGate key={to} permission={permission}>
                {link}
              </PermissionGate>
            )
          }

          return link
        })}

        {showAdmin && (
          <>
            <p className="mb-1 mt-4 px-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Administration
            </p>
            {adminNavItems.map(({ to, label, icon: Icon, permission }) => {
              const link = (
                <NavLink
                  key={to}
                  to={to}
                  className={({ isActive }) =>
                    cn(
                      'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                      isActive
                        ? 'bg-primary text-primary-foreground'
                        : 'text-muted-foreground hover:bg-muted hover:text-foreground',
                    )
                  }
                >
                  <Icon className="h-4 w-4" />
                  {label}
                </NavLink>
              )

              if (permission) {
                return (
                  <PermissionGate key={to} permission={permission}>
                    {link}
                  </PermissionGate>
                )
              }

              return link
            })}
          </>
        )}

        {role === 'CANDIDATE' && (
          <div className="mt-auto rounded-lg border border-border bg-muted/50 p-3 text-xs text-muted-foreground">
            <p className="font-medium text-foreground">Besoin d&apos;aide ?</p>
            <p className="mt-1">Contactez votre conseillère depuis votre tableau de bord.</p>
          </div>
        )}
      </nav>
    </aside>
  )
}
