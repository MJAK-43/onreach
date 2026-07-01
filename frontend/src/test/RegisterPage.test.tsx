import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { fireEvent, render, screen, waitFor } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import { RegisterPage } from '@/pages/RegisterPage'
import * as api from '@/lib/api'

vi.mock('@/lib/api', async (importOriginal) => {
  const actual = await importOriginal<typeof import('@/lib/api')>()
  return {
    ...actual,
    register: vi.fn(),
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

function renderRegisterPage() {
  const queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  })

  return render(
    <QueryClientProvider client={queryClient}>
      <MemoryRouter>
        <RegisterPage />
      </MemoryRouter>
    </QueryClientProvider>,
  )
}

describe('RegisterPage', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('affiche le formulaire d\'inscription', () => {
    renderRegisterPage()

    expect(screen.getByRole('heading', { name: /créer un compte/i })).toBeInTheDocument()
    expect(screen.getByLabelText(/^prénom$/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/^nom$/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/adresse e-mail/i)).toBeInTheDocument()
    expect(screen.getByLabelText(/type de candidature/i)).toBeInTheDocument()
    expect(screen.getByRole('button', { name: /créer mon compte/i })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: /se connecter/i })).toHaveAttribute('href', '/login')
  })

  it('soumet l\'inscription et redirige après succès', async () => {
    vi.mocked(api.register).mockResolvedValue({
      token: 'access-token',
      refreshToken: 'refresh-token',
      user: {
        id: '1',
        email: 'nouveau@example.com',
        firstName: 'Nouveau',
        lastName: 'Candidat',
        fullName: 'Nouveau Candidat',
        roles: ['CANDIDATE'],
        permissions: ['candidates.view'],
        mfaEnabled: false,
        isActive: true,
      },
    })

    renderRegisterPage()

    fireEvent.change(screen.getByLabelText(/^prénom$/i), { target: { value: 'Nouveau' } })
    fireEvent.change(screen.getByLabelText(/^nom$/i), { target: { value: 'Candidat' } })
    fireEvent.change(screen.getByLabelText(/adresse e-mail/i), {
      target: { value: 'nouveau@example.com' },
    })
    fireEvent.change(screen.getByLabelText(/nationalité/i), { target: { value: 'Sénégal' } })
    fireEvent.change(screen.getByLabelText(/^mot de passe$/i), {
      target: { value: 'Candidate@OnReach12!' },
    })
    fireEvent.change(screen.getByLabelText(/confirmer le mot de passe/i), {
      target: { value: 'Candidate@OnReach12!' },
    })
    fireEvent.click(screen.getByRole('button', { name: /créer mon compte/i }))

    await waitFor(() => {
      expect(vi.mocked(api.register)).toHaveBeenCalled()
    })

    await waitFor(() => {
      expect(mockNavigate).toHaveBeenCalledWith('/', { replace: true })
    })
  })
})
