import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { DashboardPage } from '@/pages/DashboardPage'
import * as api from '@/lib/api'
import * as auth from '@/lib/auth'

vi.mock('@/lib/auth', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/lib/auth')>()
  return {
    ...actual,
    isAuthenticated: vi.fn(() => true),
    clearTokens: vi.fn(),
  }
})

vi.mock('@/lib/api', () => ({
  getCurrentUser: vi.fn(),
  fetchCandidates: vi.fn(),
}))

describe('DashboardPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
    vi.mocked(auth.isAuthenticated).mockReturnValue(true)
    vi.mocked(api.getCurrentUser).mockResolvedValue({
      id: '1',
      email: 'admin@onreach.inovixora.fr',
      firstName: 'Admin',
      lastName: 'User',
      fullName: 'Admin User',
      roles: ['SUPER_ADMIN'],
      permissions: ['candidates.view'],
      mfaEnabled: false,
      isActive: true,
    })
    vi.mocked(api.fetchCandidates).mockResolvedValue([])
  })

  it('affiche le tableau de bord staff', async () => {
    const queryClient = new QueryClient({
      defaultOptions: { queries: { retry: false } },
    })

    render(
      <QueryClientProvider client={queryClient}>
        <MemoryRouter>
          <DashboardPage />
        </MemoryRouter>
      </QueryClientProvider>,
    )

    expect(await screen.findByText(/Bonjour Admin/i)).toBeInTheDocument()
  })
})
