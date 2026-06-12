import type { ReactNode } from 'react'
import { AppHeader } from '@/components/layout/AppHeader'
import { CandidateSidebar } from '@/components/layout/candidate/CandidateSidebar'

export function CandidateShell({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-100 via-[#eef1f6] to-slate-100">
      <CandidateSidebar />
      <div className="flex min-h-screen flex-col pl-64">
        <AppHeader />
        <main className="flex-1 overflow-y-auto px-4 py-6 sm:px-6 lg:px-8">
          {children}
        </main>
        <footer className="shrink-0 border-t border-slate-200/80 bg-white/80 py-3 text-center text-xs text-slate-500 backdrop-blur-sm">
          © 2026 On&apos;Reach — Tous droits réservés.
        </footer>
      </div>
    </div>
  )
}
