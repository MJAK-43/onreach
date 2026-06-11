import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen } from '@testing-library/react'
import { MemoryRouter, Route, Routes } from 'react-router-dom'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import { ProtectedRoute } from '@/components/auth/ProtectedRoute'
import * as auth from '@/lib/auth'
import * as api from '@/lib/api'

vi.mock('@/lib/auth', () => ({
  isAuthenticated: vi.fn(),
  clearTokens: vi.fn(),
}))

vi.mock('@/lib/api', () => ({
  getCurrentUser: vi.fn(),
}))

function renderProtected(permission?: string) {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  })

  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter initialEntries={['/protected']}>
        <Routes>
          <Route
            path="/protected"
            element={
              <ProtectedRoute permission={permission}>
                <p>Contenu protégé</p>
              </ProtectedRoute>
            }
          />
          <Route path="/login" element={<p>Page login</p>} />
          <Route path="/unauthorized" element={<p>Accès refusé</p>} />
        </Routes>
      </MemoryRouter>
    </QueryClientProvider>,
  )
}

describe('ProtectedRoute', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('redirige vers login sans token', () => {
    vi.mocked(auth.isAuthenticated).mockReturnValue(false)

    renderProtected()

    expect(screen.getByText('Page login')).toBeInTheDocument()
  })

  it('affiche le contenu quand authentifié', async () => {
    vi.mocked(auth.isAuthenticated).mockReturnValue(true)
    vi.mocked(api.getCurrentUser).mockResolvedValue({
      id: '1',
      email: 'admin@onreach.inovixora.fr',
      firstName: 'Admin',
      lastName: 'User',
      fullName: 'Admin User',
      roles: ['SUPER_ADMIN'],
      permissions: ['users.view'],
      mfaEnabled: false,
      isActive: true,
    })

    renderProtected()

    expect(await screen.findByText('Contenu protégé')).toBeInTheDocument()
  })

  it('redirige vers unauthorized sans permission', async () => {
    vi.mocked(auth.isAuthenticated).mockReturnValue(true)
    vi.mocked(api.getCurrentUser).mockResolvedValue({
      id: '1',
      email: 'user@example.com',
      firstName: 'User',
      lastName: 'Test',
      fullName: 'User Test',
      roles: ['CANDIDATE'],
      permissions: [],
      mfaEnabled: false,
      isActive: true,
    })

    renderProtected('users.view')

    expect(await screen.findByText('Accès refusé')).toBeInTheDocument()
  })
})
