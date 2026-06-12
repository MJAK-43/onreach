import { useState, useRef, useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { Bell, ChevronDown, Globe, LogOut, Menu, Shield, User } from 'lucide-react'
import { logout } from '@/lib/api'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { getRoleLabel, getPrimaryRole } from '@/lib/roles'

export function AppHeader() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const { data: user } = useCurrentUser()
  const [menuOpen, setMenuOpen] = useState(false)
  const menuRef = useRef<HTMLDivElement>(null)
  const role = getPrimaryRole(user?.roles)
  const isStaff = role !== 'CANDIDATE'

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
    <header className="flex h-16 shrink-0 items-center justify-between border-b border-slate-200 bg-white px-6 shadow-sm">
      <button type="button" className="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="Menu">
        <Menu className="h-5 w-5" />
      </button>
      <div className="hidden flex-1 lg:block" />

      <div className="flex items-center gap-3">
        <button type="button" className="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100" aria-label="Notifications">
          <Bell className="h-5 w-5" />
          <span className="absolute right-1 top-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">
            3
          </span>
        </button>

        <div className="flex items-center gap-1 rounded-lg border border-slate-200 px-2 py-1.5 text-sm text-slate-600">
          <Globe className="h-4 w-4" />
          FR
        </div>

        <div className="relative" ref={menuRef}>
          <button
            type="button"
            onClick={() => setMenuOpen((o) => !o)}
            className="flex items-center gap-2 rounded-lg py-1 pl-1 pr-2 hover:bg-slate-50"
          >
            <div className="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-sm font-semibold text-white">
              {initials}
            </div>
            <div className="hidden text-left sm:block">
              <p className="text-sm font-medium text-slate-800">{user?.fullName}</p>
              <p className="text-xs text-slate-500">{getRoleLabel(role)}</p>
            </div>
            <ChevronDown className="hidden h-4 w-4 text-slate-400 sm:block" />
          </button>

          {menuOpen && (
            <div className="absolute right-0 z-50 mt-2 w-52 rounded-lg border border-slate-200 bg-white py-1 shadow-lg">
              <button
                type="button"
                className="flex w-full items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50"
                onClick={() => { setMenuOpen(false); navigate('/profile') }}
              >
                <User className="h-4 w-4" />
                Mon profil
              </button>
              {isStaff && (
                <button
                  type="button"
                  className="flex w-full items-center gap-2 px-4 py-2 text-sm hover:bg-slate-50"
                  onClick={() => { setMenuOpen(false); navigate('/admin/security') }}
                >
                  <Shield className="h-4 w-4" />
                  Sécurité
                </button>
              )}
              <button
                type="button"
                className="flex w-full items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-slate-50"
                onClick={() => { setMenuOpen(false); logoutMutation.mutate() }}
              >
                <LogOut className="h-4 w-4" />
                Déconnexion
              </button>
            </div>
          )}
        </div>
      </div>
    </header>
  )
}
