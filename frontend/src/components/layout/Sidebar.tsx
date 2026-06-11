import {
  KeyRound,
  LayoutDashboard,
  Settings,
  Shield,
  UserCog,
  Users,
} from 'lucide-react'
import { NavLink } from 'react-router-dom'
import { PermissionGate } from '@/components/auth/PermissionGate'
import { cn } from '@/lib/utils'

const mainNavItems = [
  { to: '/', label: 'Dashboard', icon: LayoutDashboard },
  { to: '/settings', label: 'Paramètres', icon: Settings },
]

const adminNavItems = [
  { to: '/admin/users', label: 'Utilisateurs', icon: Users, permission: 'users.view' },
  { to: '/admin/roles', label: 'Rôles', icon: UserCog, permission: 'roles.view' },
  { to: '/admin/permissions', label: 'Permissions', icon: KeyRound, permission: 'permissions.view' },
  { to: '/admin/security', label: 'Sécurité', icon: Shield, permission: null },
]

export function Sidebar() {
  return (
    <aside className="flex h-full w-64 flex-col border-r border-border bg-sidebar">
      <div className="flex h-16 items-center border-b border-border px-6">
        <span className="text-lg font-semibold tracking-tight">On&apos;Reach</span>
      </div>
      <nav className="flex flex-1 flex-col gap-1 p-4">
        {mainNavItems.map(({ to, label, icon: Icon }) => (
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
            end={to === '/'}
          >
            <Icon className="h-4 w-4" />
            {label}
          </NavLink>
        ))}

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
      </nav>
    </aside>
  )
}
