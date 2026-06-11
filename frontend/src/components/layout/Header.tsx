import { Bell } from 'lucide-react'
import { Button } from '@/components/ui/button'

export function Header() {
  return (
    <header className="flex h-16 items-center justify-between border-b border-border bg-background px-6">
      <div>
        <h1 className="text-lg font-semibold">Tableau de bord</h1>
        <p className="text-sm text-muted-foreground">
          Fondation technique — Sprint 0
        </p>
      </div>
      <div className="flex items-center gap-2">
        <Button variant="ghost" size="icon" aria-label="Notifications">
          <Bell className="h-4 w-4" />
        </Button>
        <div className="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-medium text-primary-foreground">
          OR
        </div>
      </div>
    </header>
  )
}
