import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { fireEvent, render, screen, waitFor } from '@testing-library/react'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import { CandidateDetailPage } from '@/pages/CandidateDetailPage'
import * as api from '@/lib/api'
import * as pathwaysApi from '@/lib/pathways-api'

vi.mock('@/lib/api', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/lib/api')>()
  return {
    ...actual,
    fetchCandidate: vi.fn(),
    fetchCandidateCompletion: vi.fn(),
    fetchCandidateDocuments: vi.fn(),
    fetchCandidateTimeline: vi.fn(),
    fetchCandidateNotes: vi.fn(),
  }
})

vi.mock('@/lib/notifications-api', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/lib/notifications-api')>()
  return {
    ...actual,
    fetchCandidatePathwayAudit: vi.fn().mockResolvedValue({ items: [] }),
  }
})

vi.mock('@/lib/pathways-api', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/lib/pathways-api')>()
  return {
    ...actual,
    fetchCandidatePathways: vi.fn(),
    patchCandidatePathwaySubStep: vi.fn(),
  }
})

vi.mock('@/hooks/useCurrentUser', () => ({
  useCurrentUser: () => ({
    data: {
      id: 'staff-1',
      email: 'marie@example.fr',
      firstName: 'Marie',
      lastName: 'Kouassi',
      fullName: 'Marie Kouassi',
      roles: ['COUNSELOR'],
      permissions: ['applications.view', 'applications.edit', 'candidates.view'],
      mfaEnabled: false,
      isActive: true,
    },
    isLoading: false,
    isError: false,
  }),
}))

const mockCandidate = {
  id: 'candidate-1',
  referenceNumber: 'ONR-2026-001',
  firstName: 'Mohamed',
  lastName: 'Koffi',
  email: 'mohamed.koffi@onreach.inovixora.fr',
  nationality: 'CI',
  status: 'active',
  phone: null,
  city: null,
  country: null,
}

const mockPathways = {
  studyApplicationType: 'first_year' as const,
  studyApplicationTypeLabel: "Première année d'études en France",
  pathways: [
    {
      id: 'pathway-1',
      code: 'parcoursup' as const,
      name: 'Parcoursup',
      status: 'in_progress',
      statusLabel: 'En cours',
      progressPercent: 10,
      blockedReason: null,
      doubleValidationEnabled: false,
      updatedAt: new Date().toISOString(),
      stages: [
        {
          id: 'stage-1',
          title: 'Création du dossier',
          description: null,
          sortOrder: 1,
          progressPercent: 20,
          subSteps: [
            {
              id: 'substep-1',
              title: 'Compte Parcoursup créé',
              description: null,
              required: true,
              sortOrder: 1,
              dueDate: '2026-02-01',
              validated: false,
              counselorValidatedAt: null,
              counselorValidatedBy: null,
              adminValidatedAt: null,
              adminValidatedBy: null,
              updatedAt: new Date().toISOString(),
            },
          ],
        },
      ],
    },
  ],
}

function renderPage() {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } })
  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter initialEntries={['/candidates/candidate-1']}>
        <Routes>
          <Route path="/candidates/:id" element={<CandidateDetailPage />} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>,
  )
}

describe('CandidateDetailPage — parcours staff', () => {
  beforeEach(() => {
    vi.mocked(api.fetchCandidate).mockResolvedValue(mockCandidate as never)
    vi.mocked(api.fetchCandidateCompletion).mockResolvedValue({
      profile: 80,
      documents: 70,
      financing: 50,
      campusFrance: 40,
      checklist: { percent: 40, items: [] },
    } as never)
    vi.mocked(pathwaysApi.fetchCandidatePathways).mockResolvedValue(mockPathways)
  })

  it('affiche l’onglet Parcours avec les étapes du candidat', async () => {
    renderPage()

    await waitFor(() => {
      expect(screen.getByText('Mohamed Koffi')).toBeInTheDocument()
    })

    fireEvent.click(screen.getByRole('button', { name: 'Parcours' }))

    expect(await screen.findByText('Parcours de candidature')).toBeInTheDocument()
    expect(screen.getAllByText('Compte Parcoursup créé').length).toBeGreaterThan(0)
    expect(screen.getByText('Validation conseiller — 1 parcours assigné(s)')).toBeInTheDocument()
  })

  it('permet au conseiller de valider une sous-étape', async () => {
    vi.mocked(pathwaysApi.patchCandidatePathwaySubStep).mockResolvedValue({
      pathway: {
        ...mockPathways.pathways[0],
        progressPercent: 20,
        stages: [
          {
            ...mockPathways.pathways[0].stages[0],
            progressPercent: 100,
            subSteps: [
              {
                ...mockPathways.pathways[0].stages[0].subSteps[0],
                validated: true,
                counselorValidatedAt: new Date().toISOString(),
                counselorValidatedBy: {
                  id: 'staff-1',
                  firstName: 'Marie',
                  lastName: 'Kouassi',
                  email: 'marie@example.fr',
                },
              },
            ],
          },
        ],
      },
    })

    renderPage()

    await waitFor(() => {
      expect(screen.getByText('Mohamed Koffi')).toBeInTheDocument()
    })

    fireEvent.click(screen.getByRole('button', { name: 'Parcours' }))
    fireEvent.click(await screen.findByRole('button', { name: 'Valider conseiller' }))

    await waitFor(() => {
      expect(pathwaysApi.patchCandidatePathwaySubStep).toHaveBeenCalledWith(
        'candidate-1',
        'pathway-1',
        'substep-1',
        { counselorValidated: true },
      )
    })

    expect(await screen.findByText(/Validé conseiller par Marie Kouassi/)).toBeInTheDocument()
  })
})
