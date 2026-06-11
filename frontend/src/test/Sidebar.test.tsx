import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it } from 'vitest'
import { Sidebar } from '@/components/layout/Sidebar'

describe('Sidebar', () => {
  it('affiche la navigation principale', () => {
    const queryClient = new QueryClient({
      defaultOptions: { queries: { retry: false } },
    })

    render(
      <QueryClientProvider client={queryClient}>
        <MemoryRouter>
          <Sidebar />
        </MemoryRouter>
      </QueryClientProvider>,
    )

    expect(screen.getByText("On'Reach")).toBeInTheDocument()
    expect(screen.getByText('Tableau de bord')).toBeInTheDocument()
    expect(screen.getByText('Paramètres')).toBeInTheDocument()
  })
})
