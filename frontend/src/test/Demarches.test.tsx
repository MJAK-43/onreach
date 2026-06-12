import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it, vi } from 'vitest'
import { DemarchesLayout } from '@/components/candidate/demarches/DemarchesLayout'
import { DemarchesOverviewPage } from '@/pages/candidate/demarches/DemarchesOverviewPage'

vi.mock('@/lib/demarches-api', () => ({
  fetchMyApplications: vi.fn().mockResolvedValue({
    candidateId: '1',
    referenceNumber: 'ONR-2026-001',
    completion: {
      global: 72,
      profile: 85,
      documents: 70,
      campusFrance: 50,
      parcoursup: 100,
      parisSaclay: 30,
      visa: 0,
    },
    summary: {
      documentsValidated: 2,
      documentsMissing: 3,
      paymentsPending: 1,
      upcomingAppointments: 0,
    },
    counselor: {
      id: 'c1',
      firstName: 'Marie',
      lastName: 'Kouassi',
      email: 'marie@example.fr',
      fullName: 'Marie Kouassi',
    },
    procedures: {
      campusFrance: {
        type: 'campus_france',
        label: 'Campus France',
        status: 'admission_obtained',
        statusLabel: 'Admission obtenue',
        progress: 75,
        updatedAt: new Date().toISOString(),
        nextAction: 'Lancer la demande de visa',
      },
      parcoursup: {
        type: 'parcoursup',
        label: 'Parcoursup',
        status: 'active',
        statusLabel: 'En cours',
        progress: 100,
        updatedAt: new Date().toISOString(),
        nextAction: 'Suivre vos réponses',
      },
      parisSaclay: {
        type: 'paris_saclay',
        label: 'Paris-Saclay',
        status: 'under_review',
        statusLabel: 'Étude du dossier',
        progress: 40,
        updatedAt: new Date().toISOString(),
        nextAction: 'Compléter vos documents',
      },
    },
    alerts: [],
  }),
}))

function wrapper(children: React.ReactNode) {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } })
  return (
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>{children}</MemoryRouter>
    </QueryClientProvider>
  )
}

describe('Mes démarches', () => {
  it('affiche la navigation des démarches', () => {
    render(wrapper(<DemarchesLayout />))
    expect(screen.getAllByText('Vue globale').length).toBeGreaterThan(0)
    expect(screen.getAllByText('Campus France').length).toBeGreaterThan(0)
    expect(screen.getAllByText('Historique').length).toBeGreaterThan(0)
  })

  it('affiche la vue globale avec KPIs', async () => {
    render(wrapper(<DemarchesOverviewPage />))
    expect(await screen.findByText('72%')).toBeInTheDocument()
    expect(screen.getByText('Vos procédures')).toBeInTheDocument()
    expect(screen.getByText('Marie Kouassi')).toBeInTheDocument()
  })
})
