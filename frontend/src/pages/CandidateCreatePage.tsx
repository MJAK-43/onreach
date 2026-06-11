import { useState } from 'react'
import { useMutation, useQueryClient } from '@tanstack/react-query'
import { Link, useNavigate } from 'react-router-dom'
import { createCandidate } from '@/lib/api'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

export function CandidateCreatePage() {
  const navigate = useNavigate()
  const queryClient = useQueryClient()
  const [form, setForm] = useState({
    firstName: '',
    lastName: '',
    email: '',
    nationality: '',
    phone: '',
    city: '',
    country: '',
  })

  const mutation = useMutation({
    mutationFn: createCandidate,
    onSuccess: (candidate) => {
      void queryClient.invalidateQueries({ queryKey: ['candidates'] })
      navigate(`/candidates/${candidate.id}`)
    },
  })

  function handleSubmit(event: React.FormEvent) {
    event.preventDefault()
    mutation.mutate(form)
  }

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div>
        <h2 className="text-xl font-semibold">Nouveau candidat</h2>
        <p className="mt-1 text-sm text-muted-foreground">
          Créer un dossier étudiant unifié
        </p>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Informations personnelles</CardTitle>
          <CardDescription>Champs obligatoires marqués *</CardDescription>
        </CardHeader>
        <CardContent>
          <form onSubmit={handleSubmit} className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="Prénom *" id="firstName">
                <Input
                  id="firstName"
                  required
                  value={form.firstName}
                  onChange={(e) => setForm({ ...form, firstName: e.target.value })}
                />
              </Field>
              <Field label="Nom *" id="lastName">
                <Input
                  id="lastName"
                  required
                  value={form.lastName}
                  onChange={(e) => setForm({ ...form, lastName: e.target.value })}
                />
              </Field>
            </div>
            <Field label="E-mail *" id="email">
              <Input
                id="email"
                type="email"
                required
                value={form.email}
                onChange={(e) => setForm({ ...form, email: e.target.value })}
              />
            </Field>
            <Field label="Nationalité *" id="nationality">
              <Input
                id="nationality"
                required
                value={form.nationality}
                onChange={(e) => setForm({ ...form, nationality: e.target.value })}
              />
            </Field>
            <div className="grid gap-4 sm:grid-cols-2">
              <Field label="Téléphone" id="phone">
                <Input
                  id="phone"
                  value={form.phone}
                  onChange={(e) => setForm({ ...form, phone: e.target.value })}
                />
              </Field>
              <Field label="Ville" id="city">
                <Input
                  id="city"
                  value={form.city}
                  onChange={(e) => setForm({ ...form, city: e.target.value })}
                />
              </Field>
            </div>
            <Field label="Pays" id="country">
              <Input
                id="country"
                value={form.country}
                onChange={(e) => setForm({ ...form, country: e.target.value })}
              />
            </Field>

            {mutation.isError && (
              <p className="text-sm text-red-600">
                {mutation.error instanceof Error ? mutation.error.message : 'Erreur'}
              </p>
            )}

            <div className="flex gap-3 pt-2">
              <Button type="submit" disabled={mutation.isPending}>
                {mutation.isPending ? 'Création...' : 'Créer le dossier'}
              </Button>
              <Button variant="outline" asChild>
                <Link to="/candidates">Annuler</Link>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  )
}

function Field({
  label,
  id,
  children,
}: {
  label: string
  id: string
  children: React.ReactNode
}) {
  return (
    <div className="space-y-2">
      <Label htmlFor={id}>{label}</Label>
      {children}
    </div>
  )
}
