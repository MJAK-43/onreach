import { AppSidebar } from '@/components/layout/AppSidebar'
import {
  adminExtraNavItems,
  counselorNavSections,
  staffDashboardItem,
  systemAdminNavItems,
} from '@/config/navigation'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { getPrimaryRole } from '@/lib/roles'

export function StaffSidebar() {
  const { data: user } = useCurrentUser()
  const role = getPrimaryRole(user?.roles)
  const showAdmin = role === 'SUPER_ADMIN' || role === 'ADMIN'

  const sections = [
    ...counselorNavSections,
    ...(showAdmin
      ? [
          {
            title: 'Administration',
            items: [...adminExtraNavItems, ...systemAdminNavItems],
          },
        ]
      : []),
  ]

  return (
    <AppSidebar
      items={[staffDashboardItem]}
      sections={sections}
      footer={
        <div className="m-3 rounded-xl bg-white/5 p-4">
          <p className="text-sm font-medium text-white">
            {showAdmin ? 'Espace administration' : 'Espace conseiller'}
          </p>
          <p className="mt-1 text-xs text-slate-400">
            {showAdmin
              ? 'Gérez la plateforme et accompagnez vos candidats.'
              : 'Planifiez vos disponibilités et suivez vos candidats.'}
          </p>
        </div>
      }
    />
  )
}
