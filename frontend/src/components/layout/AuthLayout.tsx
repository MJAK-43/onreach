import { Outlet } from 'react-router-dom'

export function AuthLayout() {
  return (
    <div className="flex min-h-screen flex-col items-center justify-center bg-background p-4">
      <div className="mb-8 text-center">
        <h1 className="text-2xl font-semibold tracking-tight">On&apos;Reach</h1>
        <p className="mt-1 text-sm text-muted-foreground">
          Plateforme d&apos;accompagnement des étudiants internationaux
        </p>
      </div>
      <div className="w-full max-w-md">
        <Outlet />
      </div>
    </div>
  )
}
