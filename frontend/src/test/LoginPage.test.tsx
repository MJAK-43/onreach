import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { fireEvent, render, screen, waitFor } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import { LoginPage } from '@/pages/LoginPage'
import * as api from '@/lib/api'

vi.mock('@/lib/api', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/lib/api')>()
  return {
    ...actual,
    login: vi.fn(),
  }
})

const mockNavigate = vi.fn()
vi.mock('react-router-dom', async (importOriginal) => {
  const actual = await importOriginal<typeof import('react-router-dom')>()
  return {
    ...actual,
    useNavigate: () => mockNavigate,
  }
})

function renderLoginPage() {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  })

  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>
        <LoginPage />
      </MemoryRouter>
    </QueryClientProvider>,
  )
}

describe('LoginPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('affiche le formulaire de connexion', () => {
    renderLoginPage()

    expect(screen.getByRole('heading', { name: /connexion/i })).toBeInTheDocument()
    expect(screen.getByLabelText(/adresse e-mail/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/^mot de passe$/i)).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /se connecter/i })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: /mot de passe oublié/i })).toHaveAttribute(
      'href',
      '/forgot-password',
    )
  })

  it('soumet les identifiants et redirige après connexion', async () => {
    vi.mocked(api.login).mockResolvedValue({
      token: 'access-token',
      refreshToken: 'refresh-token',
      user: {
        id: '1',
        email: 'admin@onreach.inovixora.fr',
        firstName: 'Admin',
        lastName: 'OnReach',
        fullName: 'Admin OnReach',
        roles: ['ADMIN'],
        permissions: ['users.view'],
        mfaEnabled: false,
        isActive: true,
      },
    })

    renderLoginPage()

    fireEvent.change(screen.getByLabelText(/adresse e-mail/i), {
      target: { value: 'admin@onreach.inovixora.fr' },
    })
    fireEvent.change(screen.getByLabelText(/^mot de passe$/i), {
      target: { value: 'Admin@OnReach12!' },
    })
    fireEvent.click(screen.getByRole('button', { name: /se connecter/i }))

    await waitFor(() => {
      expect(vi.mocked(api.login).mock.calls[0]?.[0]).toMatchObject({
        email: 'admin@onreach.inovixora.fr',
        password: 'Admin@OnReach12!',
        rememberMe: false,
      })
    })

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith('/', { replace: true })
    })
  })

  it('affiche le champ MFA quand requis', async () => {
    vi.mocked(api.login).mockResolvedValue({ requiresMfa: true })

    renderLoginPage()

    fireEvent.change(screen.getByLabelText(/adresse e-mail/i), {
      target: { value: 'admin@onreach.inovixora.fr' },
    })
    fireEvent.change(screen.getByLabelText(/^mot de passe$/i), {
      target: { value: 'Admin@OnReach12!' },
    })
    fireEvent.click(screen.getByRole('button', { name: /se connecter/i }))

    await waitFor(() => {
      expect(screen.getByLabelText(/code d'authentification/i)).toBeInTheDocument()
    })
  })

  it('affiche un message d\'erreur en cas d\'échec', async () => {
    vi.mocked(api.login).mockRejectedValue(
      new api.ApiError('Identifiants invalides.', 401),
    )

    renderLoginPage()

    fireEvent.change(screen.getByLabelText(/adresse e-mail/i), {
      target: { value: 'wrong@example.com' },
    })
    fireEvent.change(screen.getByLabelText(/^mot de passe$/i), {
      target: { value: 'wrong-password' },
    })
    fireEvent.click(screen.getByRole('button', { name: /se connecter/i }))

    await waitFor(() => {
      expect(screen.getByRole('alert')).toHaveTextContent('Identifiants invalides.')
    })
  })
})
