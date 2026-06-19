import { useState } from 'react'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { ApiError, login, type CurrentUser } from '@/lib/api'
import { fetchMyDashboard } from '@/lib/dashboard-api'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

export function LoginPage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const location = useLocation()
  const from = (location.state as { from?: { pathname: string } } | null)?.from
    ?.pathname ?? '/'

  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [rememberMe, setRememberMe] = useState(false)
  const [mfaCode, setMfaCode] = useState('')
  const [requiresMfa, setRequiresMfa] = useState(false)
  const [error, setError] = useState<string | null>(null)

  const mutation = useMutation({
    mutationFn: login,
    onSuccess: (data) => {
      if (data.requiresMfa) {
        setRequiresMfa(true)
        setError(null)
        return
      }
      if (data.user) {
        queryClient.setQueryData<CurrentUser>(['currentUser'], {
          ...data.user,
          isActive: true,
        })
        void queryClient.prefetchQuery({
          queryKey: ['my-dashboard'],
          queryFn: fetchMyDashboard,
          staleTime: 2 * 60_000,
        })
      }
      navigate(from, { replace: true })
    },
    onError: (err: Error) => {
      if (err instanceof ApiError) {
        setError(err.message)
      } else {
        setError('Une erreur est survenue. Veuillez réessayer.')
      }
    },
  })

  function handleSubmit(e: React.FormEvent) {
    e.preventDefault()
    setError(null)
    mutation.mutate({
      email,
      password,
      rememberMe,
      mfaCode: requiresMfa ? mfaCode : undefined,
    })
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Connexion</CardTitle>
        <CardDescription>
          Accédez à votre espace On&apos;Reach
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          {error && (
            <p className="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
              {error}
            </p>
          )}

          <div className="space-y-2">
            <Label htmlFor="email">Adresse e-mail</Label>
            <Input
              id="email"
              type="email"
              autoComplete="email"
              required
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              disabled={mutation.isPending}
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="password">Mot de passe</Label>
            <Input
              id="password"
              type="password"
              autoComplete="current-password"
              required
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              disabled={mutation.isPending}
            />
          </div>

          {requiresMfa && (
            <div className="space-y-2">
              <Label htmlFor="mfaCode">Code d&apos;authentification</Label>
              <Input
                id="mfaCode"
                type="text"
                inputMode="numeric"
                autoComplete="one-time-code"
                required
                placeholder="000000"
                value={mfaCode}
                onChange={(e) => setMfaCode(e.target.value)}
                disabled={mutation.isPending}
              />
            </div>
          )}

          <div className="flex items-center gap-2">
            <input
              id="rememberMe"
              type="checkbox"
              checked={rememberMe}
              onChange={(e) => setRememberMe(e.target.checked)}
              className="h-4 w-4 rounded border-border"
              disabled={mutation.isPending}
            />
            <Label htmlFor="rememberMe" className="font-normal">
              Se souvenir de moi
            </Label>
          </div>

          <Button type="submit" className="w-full" disabled={mutation.isPending}>
            {mutation.isPending ? 'Connexion...' : 'Se connecter'}
          </Button>

          <p className="text-center text-sm text-muted-foreground">
            <Link to="/forgot-password" className="text-primary hover:underline">
              Mot de passe oublié ?
            </Link>
          </p>
        </form>
      </CardContent>
    </Card>
  )
}
