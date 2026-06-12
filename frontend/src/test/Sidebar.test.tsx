import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it } from 'vitest'
import { StaffSidebar } from '@/components/layout/staff/StaffSidebar'

describe('StaffSidebar', () => {
  it('affiche la navigation principale', () => {
    const queryClient = new QueryClient({
      defaultOptions: { queries: { retry: false } },
    })

    render(
      <QueryClientProvider client={queryClient}>
        <MemoryRouter>
          <StaffSidebar />
        </MemoryRouter>
      </QueryClientProvider>,
    )

    expect(screen.getByText("On'Reach")).toBeInTheDocument()
    expect(screen.getByText('Tableau de bord')).toBeInTheDocument()
    expect(screen.getByText('Gestion des dossiers')).toBeInTheDocument()
    expect(screen.getByText('Recherche de logement')).toBeInTheDocument()
    expect(screen.getByText('Agenda & rendez-vous')).toBeInTheDocument()
  })
})
