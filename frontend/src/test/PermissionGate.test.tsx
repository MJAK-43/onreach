import { render, screen } from '@testing-library/react'
import { describe, expect, it, vi, beforeEach } from 'vitest'
import { PermissionGate } from '@/components/auth/PermissionGate'
import * as useCurrentUserHook from '@/hooks/useCurrentUser'

vi.mock('@/hooks/useCurrentUser', () => ({
  useCurrentUser: vi.fn(),
}))

describe('PermissionGate', () => {
  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('affiche les enfants si la permission est accordée', () => {
    vi.mocked(useCurrentUserHook.useCurrentUser).mockReturnValue({
      data: {
        id: '1',
        email: 'admin@onreach.inovixora.fr',
        firstName: 'Admin',
        lastName: 'User',
        fullName: 'Admin User',
        roles: ['SUPER_ADMIN'],
        permissions: ['users.view'],
        mfaEnabled: false,
        isActive: true,
      },
      isLoading: false,
      isError: false,
    } as ReturnType<typeof useCurrentUserHook.useCurrentUser>)

    render(
      <PermissionGate permission="users.view">
        <button type="button">Action admin</button>
      </PermissionGate>,
    )

    expect(screen.getByRole('button', { name: 'Action admin' })).toBeInTheDocument()
  })

  it('masque les enfants si la permission est absente', () => {
    vi.mocked(useCurrentUserHook.useCurrentUser).mockReturnValue({
      data: {
        id: '1',
        email: 'user@example.com',
        firstName: 'User',
        lastName: 'Test',
        fullName: 'User Test',
        roles: ['CANDIDATE'],
        permissions: [],
        mfaEnabled: false,
        isActive: true,
      },
      isLoading: false,
      isError: false,
    } as ReturnType<typeof useCurrentUserHook.useCurrentUser>)

    render(
      <PermissionGate permission="users.view">
        <button type="button">Action admin</button>
      </PermissionGate>,
    )

    expect(screen.queryByRole('button', { name: 'Action admin' })).not.toBeInTheDocument()
  })
})
