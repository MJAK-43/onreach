import { useState, useRef, useEffect } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { Bell, ChevronDown, LogOut, Shield, User } from 'lucide-react'
import { logout } from '@/lib/api'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { getPrimaryRole, getRoleLabel } from '@/lib/roles'
import { Button } from '@/components/ui/button'

const pageTitles: Record<string, { title: string; subtitle: string }> = {
  '/': { title: 'Tableau de bord', subtitle: 'Vue d\'ensemble' },
  '/my-file': { title: 'Mon dossier', subtitle: 'Votre dossier étudiant unifié' },
  '/candidates': { title: 'Candidats', subtitle: 'Gestion des dossiers étudiants' },
  '/candidates/new': { title: 'Nouveau candidat', subtitle: 'Création d\'un dossier' },
  '/settings': { title: 'Paramètres', subtitle: 'Configuration de l\'application' },
  '/profile': { title: 'Mon profil', subtitle: 'Informations du compte' },
  '/admin/users': { title: 'Utilisateurs', subtitle: 'Gestion des comptes' },
  '/admin/roles': { title: 'Rôles', subtitle: 'Gestion des rôles' },
  '/admin/permissions': { title: 'Permissions', subtitle: 'Contrôle d\'accès granulaire' },
  '/admin/security': { title: 'Sécurité', subtitle: 'Mot de passe et MFA' },
}

export function Header() {
  const location = useLocation()
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { data: user } = useCurrentUser()
  const [menuOpen, setMenuOpen] = useState(false)
  const menuRef = useRef<HTMLDivElement>(null)

  const role = getPrimaryRole(user?.roles)
  const pageInfo = pageTitles[location.pathname] ?? (
    location.pathname.startsWith('/candidates/')
      ? { title: 'Fiche candidat', subtitle: 'Dossier étudiant unifié' }
      : { title: 'On\'Reach', subtitle: getRoleLabel(role) }
  )

  const logoutMutation = useMutation({
    mutationFn: logout,
    onSettled: () => {
      queryClient.clear()
      navigate('/login', { replace: true })
    },
  })

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (menuRef.current && !menuRef.current.contains(event.target as Node)) {
        setMenuOpen(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  const initials = user
    ? `${user.firstName.charAt(0)}${user.lastName.charAt(0)}`.toUpperCase()
    : 'OR'

  return (
    <header className="flex h-16 items-center justify-between border-b border-border bg-background px-6">
      <div>
        <h1 className="text-lg font-semibold">{pageInfo.title}</h1>
        <p className="text-sm text-muted-foreground">{pageInfo.subtitle}</p>
      </div>
      <div className="flex items-center gap-2">
        <Button variant="ghost" size="icon" aria-label="Notifications">
          <Bell className="h-4 w-4" />
        </Button>

        <div className="relative" ref={menuRef}>
          <button
            type="button"
            onClick={() => setMenuOpen((open) => !open)}
            className="flex items-center gap-2 rounded-lg px-2 py-1.5 transition-colors hover:bg-muted"
            aria-expanded={menuOpen}
            aria-haspopup="menu"
          >
            <div className="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-medium text-primary-foreground">
              {initials}
            </div>
            <span className="hidden text-sm font-medium sm:inline">
              {user?.fullName ?? 'Utilisateur'}
            </span>
            <ChevronDown className="hidden h-4 w-4 text-muted-foreground sm:inline" />
          </button>

          {menuOpen && (
            <div
              role="menu"
              className="absolute right-0 z-50 mt-2 w-56 rounded-lg border border-border bg-background py-1 shadow-lg"
            >
              <div className="border-b border-border px-4 py-2">
                <p className="text-sm font-medium">{user?.fullName}</p>
                <p className="truncate text-xs text-muted-foreground">{user?.email}</p>
                <p className="text-xs text-primary">{getRoleLabel(role)}</p>
              </div>
              <button
                type="button"
                role="menuitem"
                className="flex w-full items-center gap-2 px-4 py-2 text-sm hover:bg-muted"
                onClick={() => {
                  setMenuOpen(false)
                  navigate('/profile')
                }}
              >
                <User className="h-4 w-4" />
                Mon profil
              </button>
              <button
                type="button"
                role="menuitem"
                className="flex w-full items-center gap-2 px-4 py-2 text-sm hover:bg-muted"
                onClick={() => {
                  setMenuOpen(false)
                  navigate('/admin/security')
                }}
              >
                <Shield className="h-4 w-4" />
                Sécurité
              </button>
              <button
                type="button"
                role="menuitem"
                className="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-muted"
                onClick={() => {
                  setMenuOpen(false)
                  logoutMutation.mutate()
                }}
                disabled={logoutMutation.isPending}
              >
                <LogOut className="h-4 w-4" />
                {logoutMutation.isPending ? 'Déconnexion...' : 'Se déconnecter'}
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  )
}
