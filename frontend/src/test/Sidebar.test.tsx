import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import { describe, expect, it } from 'vitest'
import { Sidebar } from '@/components/layout/Sidebar'

describe('Sidebar', () => {
  it('affiche la navigation principale', () => {
    render(
      <MemoryRouter>
        <Sidebar />
      </MemoryRouter>,
    )

    expect(screen.getByText("On'Reach")).toBeInTheDocument()
    expect(screen.getByText('Dashboard')).toBeInTheDocument()
    expect(screen.getByText('Paramètres')).toBeInTheDocument()
  })
})
