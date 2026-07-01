import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { ApiError, register, type CurrentUser } from '@/lib/api'
import { fetchMyDashboard } from '@/lib/dashboard-api'
import { Button } from '@/components/ui/button'
import { PasswordInput } from '@/components/ui/password-input'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

const STUDY_TYPE_OPTIONS = [
  { value: 'first_year', label: "Première année d'études en France" },
  { value: 'continuing', label: "Poursuite d'études en France" },
] as const

const PASSWORD_HINT =
  '12 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial.'

export function RegisterPage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()

  const [form, setForm] = useState({
    firstName: '',
    lastName: '',
    email: '',
    password: '',
    passwordConfirm: '',
    nationality: '',
    studyApplicationType: 'first_year',
    phone: '',
    city: '',
    country: '',
  })
  const [error, setError] = useState<string | null>(null)

  const mutation = useMutation({
    mutationFn: register,
    onSuccess: (data) => {
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
      navigate('/', { replace: true })
    },
    onError: (err: Error) => {
      if (err instanceof ApiError) {
        setError(err.message)
      } else {
        setError('Une erreur est survenue. Veuillez réessayer.')
      }
    },
  })

  function updateField<K extends keyof typeof form>(key: K, value: (typeof form)[K]) {
    setForm((current) => ({ ...current, [key]: value }))
  }

  function handleSubmit(event: React.FormEvent) {
    event.preventDefault()
    setError(null)

    if (form.password !== form.passwordConfirm) {
      setError('Les mots de passe ne correspondent pas.')
      return
    }

    mutation.mutate({
      firstName: form.firstName.trim(),
      lastName: form.lastName.trim(),
      email: form.email.trim(),
      password: form.password,
      nationality: form.nationality.trim(),
      studyApplicationType: form.studyApplicationType as 'first_year' | 'continuing',
      phone: form.phone.trim() || undefined,
      city: form.city.trim() || undefined,
      country: form.country.trim() || undefined,
    })
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle>Créer un compte</CardTitle>
        <CardDescription>
          Inscrivez-vous pour accéder à votre espace candidat On&apos;Reach
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          {error && (
            <p className="rounded-md bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
              {error}
            </p>
          )}

          <div className="grid gap-4 sm:grid-cols-2">
            <div className="space-y-2">
              <Label htmlFor="firstName">Prénom</Label>
              <Input
                id="firstName"
                required
                value={form.firstName}
                onChange={(e) => updateField('firstName', e.target.value)}
                disabled={mutation.isPending}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="lastName">Nom</Label>
              <Input
                id="lastName"
                required
                value={form.lastName}
                onChange={(e) => updateField('lastName', e.target.value)}
                disabled={mutation.isPending}
              />
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="email">Adresse e-mail</Label>
            <Input
              id="email"
              type="email"
              autoComplete="email"
              required
              value={form.email}
              onChange={(e) => updateField('email', e.target.value)}
              disabled={mutation.isPending}
            />
          </div>

          <div className="space-y-2">
            <Label htmlFor="studyApplicationType">Type de candidature</Label>
            <select
              id="studyApplicationType"
              required
              className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
              value={form.studyApplicationType}
              onChange={(e) => updateField('studyApplicationType', e.target.value)}
              disabled={mutation.isPending}
            >
              {STUDY_TYPE_OPTIONS.map((option) => (
                <option key={option.value} value={option.value}>
                  {option.label}
                </option>
              ))}
            </select>
          </div>

          <div className="space-y-2">
            <Label htmlFor="nationality">Nationalité</Label>
            <Input
              id="nationality"
              required
              value={form.nationality}
              onChange={(e) => updateField('nationality', e.target.value)}
              disabled={mutation.isPending}
            />
          </div>

          <div className="grid gap-4 sm:grid-cols-3">
            <div className="space-y-2">
              <Label htmlFor="phone">Téléphone</Label>
              <Input
                id="phone"
                type="tel"
                value={form.phone}
                onChange={(e) => updateField('phone', e.target.value)}
                disabled={mutation.isPending}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="city">Ville</Label>
              <Input
                id="city"
                value={form.city}
                onChange={(e) => updateField('city', e.target.value)}
                disabled={mutation.isPending}
              />
            </div>
            <div className="space-y-2">
              <Label htmlFor="country">Pays de résidence</Label>
              <Input
                id="country"
                value={form.country}
                onChange={(e) => updateField('country', e.target.value)}
                disabled={mutation.isPending}
              />
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="password">Mot de passe</Label>
            <PasswordInput
              id="password"
              autoComplete="new-password"
              required
              value={form.password}
              onChange={(e) => updateField('password', e.target.value)}
              disabled={mutation.isPending}
            />
            <p className="text-xs text-muted-foreground">{PASSWORD_HINT}</p>
          </div>

          <div className="space-y-2">
            <Label htmlFor="passwordConfirm">Confirmer le mot de passe</Label>
            <PasswordInput
              id="passwordConfirm"
              autoComplete="new-password"
              required
              value={form.passwordConfirm}
              onChange={(e) => updateField('passwordConfirm', e.target.value)}
              disabled={mutation.isPending}
            />
          </div>

          <Button type="submit" className="w-full" disabled={mutation.isPending}>
            {mutation.isPending ? 'Création du compte...' : 'Créer mon compte'}
          </Button>

          <p className="text-center text-sm text-muted-foreground">
            Déjà un compte ?{' '}
            <Link to="/login" className="text-primary hover:underline">
              Se connecter
            </Link>
          </p>
        </form>
      </CardContent>
    </Card>
  )
}
