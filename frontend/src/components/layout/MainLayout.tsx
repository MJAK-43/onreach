import { Outlet } from 'react-router-dom'
import { CandidateShell } from '@/components/layout/candidate/CandidateShell'
import { useCurrentUser } from '@/hooks/useCurrentUser'
import { getPrimaryRole } from '@/lib/roles'
import { Header } from './Header'
import { Sidebar } from './Sidebar'

export function MainLayout() {
  const { data: user, isLoading } = useCurrentUser()
  const isCandidate = !isLoading && getPrimaryRole(user?.roles) === 'CANDIDATE'

  if (isCandidate) {
    return (
      <CandidateShell>
        <Outlet />
      </CandidateShell>
    )
  }

  return (
    <div className="flex min-h-screen">
      <Sidebar />
      <div className="flex flex-1 flex-col">
        <Header />
        <main className="flex-1 p-6">
          <Outlet />
        </main>
      </div>
    </div>
  )
}
