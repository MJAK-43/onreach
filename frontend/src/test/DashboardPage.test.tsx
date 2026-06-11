import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import { DashboardPage } from '@/pages/DashboardPage'

describe('DashboardPage', () => {
  it('affiche le titre de bienvenue', () => {
    const queryClient = new QueryClient({
      defaultOptions: { queries: { retry: false } },
    })

    render(
      <QueryClientProvider client={queryClient}>
        <DashboardPage />
      </QueryClientProvider>,
    )

    expect(screen.getByText(/Bienvenue sur On'Reach/i)).toBeInTheDocument()
  })
})
