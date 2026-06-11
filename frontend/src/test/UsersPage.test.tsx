import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen, waitFor } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it, vi } from 'vitest'
import { UsersPage } from '@/pages/UsersPage'
import * as api from '@/lib/api'

vi.mock('@/lib/api', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/lib/api')>()
  return {
    ...actual,
    fetchUsers: vi.fn(),
  }
})

describe('UsersPage', () => {
  it('affiche la liste des utilisateurs', async () => {
    vi.mocked(api.fetchUsers).mockResolvedValue([
      {
        id: '1',
        email: 'admin@onreach.inovixora.fr',
        firstName: 'Admin',
        lastName: 'OnReach',
        isActive: true,
        mfaEnabled: false,
      },
    ])

    const queryClient = new QueryClient({
      defaultOptions: { queries: { retry: false } },
    })

    render(
      <QueryClientProvider client={queryClient}>
        <MemoryRouter>
          <UsersPage />
        </MemoryRouter>
      </QueryClientProvider>,
    )

    await waitFor(() => {
      expect(screen.getByText('admin@onreach.inovixora.fr')).toBeInTheDocument()
    })
    expect(screen.getByText('1 utilisateur(s)')).toBeInTheDocument()
  })

  it('affiche une erreur si le chargement échoue', async () => {
    vi.mocked(api.fetchUsers).mockRejectedValue(new Error('API error'))

    const queryClient = new QueryClient({
      defaultOptions: { queries: { retry: false } },
    })

    render(
      <QueryClientProvider client={queryClient}>
        <MemoryRouter>
          <UsersPage />
        </MemoryRouter>
      </QueryClientProvider>,
    )

    await waitFor(() => {
      expect(screen.getByText(/impossible de charger les utilisateurs/i)).toBeInTheDocument()
    })
  })
})
