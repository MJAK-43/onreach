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
        status: 'in_progress',
        statusLabel: 'En cours',
        progress: 50,
        updatedAt: new Date().toISOString(),
        nextAction: 'Compléter le dossier',
      },
      parcoursup: {
        type: 'parcoursup',
        label: 'Parcoursup',
        status: 'in_progress',
        statusLabel: 'En cours',
        progress: 100,
        updatedAt: new Date().toISOString(),
        nextAction: 'Suivre vos réponses',
      },
      parisSaclay: {
        type: 'paris_saclay',
        label: 'Paris-Saclay',
        status: 'not_started',
        statusLabel: 'Non démarré',
        progress: 0,
        updatedAt: new Date().toISOString(),
        nextAction: '—',
      },
    },
    alerts: [],
  }),
}))

vi.mock('@/lib/pathways-api', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/lib/pathways-api')>()
  return {
    ...actual,
    fetchMyPathways: vi.fn().mockResolvedValue({
      studyApplicationType: 'first_year',
      studyApplicationTypeLabel: "Première année d'études en France",
      pathways: [
        {
          id: 'p1',
          code: 'parcoursup',
          name: 'Parcoursup',
          status: 'in_progress',
          statusLabel: 'En cours',
          progressPercent: 25,
          blockedReason: null,
          doubleValidationEnabled: false,
          updatedAt: new Date().toISOString(),
          stages: [
            {
              id: 's1',
              title: 'Création du dossier',
              description: null,
              sortOrder: 1,
              progressPercent: 50,
              subSteps: [
                {
                  id: 'ss1',
                  title: 'Compte Parcoursup créé',
                  description: null,
                  required: true,
                  sortOrder: 1,
                  dueDate: '2026-02-01',
                  validated: true,
                  counselorValidatedAt: new Date().toISOString(),
                  counselorValidatedBy: { id: 'u1', firstName: 'Marie', lastName: 'Kouassi', email: 'm@x.fr' },
                  adminValidatedAt: null,
                  adminValidatedBy: null,
                  updatedAt: new Date().toISOString(),
                },
              ],
            },
          ],
        },
        {
          id: 'p2',
          code: 'campus_france',
          name: 'Campus France',
          status: 'not_started',
          statusLabel: 'Non démarré',
          progressPercent: 0,
          blockedReason: null,
          doubleValidationEnabled: false,
          updatedAt: new Date().toISOString(),
          stages: [],
        },
      ],
    }),
  }
})

function wrapper(children: React.ReactNode) {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } })
  return (
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>{children}</MemoryRouter>
    </QueryClientProvider>
  )
}

describe('Mes démarches', () => {
  it('affiche la navigation des démarches', async () => {
    render(wrapper(<DemarchesLayout />))
    expect((await screen.findAllByText('Vue globale')).length).toBeGreaterThan(0)
    expect(screen.getAllByText('Campus France').length).toBeGreaterThan(0)
    expect(screen.getAllByText('Historique').length).toBeGreaterThan(0)
  })

  it('affiche la vue globale avec parcours', async () => {
    render(wrapper(<DemarchesOverviewPage />))
    expect(await screen.findByText('Vos parcours')).toBeInTheDocument()
    expect(screen.getByText('Marie Kouassi')).toBeInTheDocument()
    expect(screen.getByText('Parcoursup')).toBeInTheDocument()
  })
})
