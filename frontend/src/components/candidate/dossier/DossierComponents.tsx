import type { ReactNode } from 'react'
import { Link } from 'react-router-dom'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import type { AutoSaveStatus } from '@/hooks/useAutoSave'
import type { MyProfile, ProfileCompletion } from '@/lib/profile-api'
import { cn } from '@/lib/utils'

export function SaveIndicator({ status, error }: { status: AutoSaveStatus; error: string | null }) {
  const label =
    status === 'saving'
      ? 'En cours…'
      : status === 'saved'
        ? 'Sauvegardé'
        : status === 'error'
          ? 'Erreur'
          : status === 'pending'
            ? 'Modifications en attente…'
            : null

  if (!label) {
    return null
  }

  return (
    <p
      className={cn(
        'text-xs font-medium',
        status === 'saved' && 'text-emerald-600',
        status === 'saving' && 'text-blue-600',
        status === 'pending' && 'text-amber-600',
        status === 'error' && 'text-red-600',
      )}
      role="status"
    >
      {status === 'error' && error ? `${label} — ${error}` : label}
    </p>
  )
}

export function CompletionPanel({ completion }: { completion: ProfileCompletion }) {
  const items = [
    { label: 'Profil', value: completion.profile },
    { label: 'Documents', value: completion.documents },
    { label: 'Académique', value: completion.academic },
    { label: 'Financement', value: completion.financing },
  ]

  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-base">Indicateurs de complétude</CardTitle>
        <CardDescription>Complétude globale : {completion.global}%</CardDescription>
      </CardHeader>
      <CardContent className="space-y-4">
        <div>
          <div className="mb-1 flex justify-between text-sm">
            <span className="font-medium">Globale</span>
            <span>{completion.global}%</span>
          </div>
          <Progress value={completion.global} className="h-2" />
        </div>
        <div className="grid gap-3 sm:grid-cols-2">
          {items.map((item) => (
            <div key={item.label}>
              <div className="mb-1 flex justify-between text-xs text-muted-foreground">
                <span>{item.label}</span>
                <span>{item.value}%</span>
              </div>
              <Progress value={item.value} className="h-1.5" />
            </div>
          ))}
        </div>
      </CardContent>
    </Card>
  )
}

export function OverviewHero({ profile }: { profile: MyProfile }) {
  const initials = `${profile.personal.firstName.charAt(0)}${profile.personal.lastName.charAt(0)}`.toUpperCase()
  const counselorName = profile.counselor
    ? `${profile.counselor.firstName} ${profile.counselor.lastName}`
    : 'Non attribué'

  return (
    <Card>
      <CardContent className="flex flex-col gap-4 p-6 sm:flex-row sm:items-center">
        <div className="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-2xl font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300">
          {initials}
        </div>
        <div className="min-w-0 flex-1 space-y-1">
          <h2 className="text-xl font-semibold">
            {profile.personal.firstName} {profile.personal.lastName}
          </h2>
          <p className="text-sm text-muted-foreground">Réf. {profile.referenceNumber}</p>
          <p className="text-sm">Nationalité : {profile.personal.nationality}</p>
          <p className="text-sm">Conseiller : {counselorName}</p>
          <p className="text-xs text-muted-foreground">
            Créé le {new Date(profile.createdAt).toLocaleDateString('fr-FR')} — Mis à jour le{' '}
            {new Date(profile.updatedAt).toLocaleDateString('fr-FR')}
          </p>
        </div>
        <div className="flex flex-wrap gap-2">
          <Button variant="outline" size="sm" asChild>
            <Link to="/my-file/personal">Modifier le profil</Link>
          </Button>
          <Button variant="outline" size="sm" asChild>
            <Link to="/my-file/documents">Documents</Link>
          </Button>
        </div>
      </CardContent>
    </Card>
  )
}

export function SectionCard({
  title,
  description,
  children,
  footer,
}: {
  title: string
  description?: string
  children: ReactNode
  footer?: ReactNode
}) {
  return (
    <Card>
      <CardHeader className="flex flex-row items-start justify-between gap-4 space-y-0">
        <div>
          <CardTitle className="text-base">{title}</CardTitle>
          {description ? <CardDescription>{description}</CardDescription> : null}
        </div>
        {footer}
      </CardHeader>
      <CardContent>{children}</CardContent>
    </Card>
  )
}

export function FormGrid({ children }: { children: ReactNode }) {
  return <div className="grid gap-4 sm:grid-cols-2">{children}</div>
}

export function FormField({
  label,
  id,
  value,
  onChange,
  type = 'text',
  className,
  disabled,
}: {
  label: string
  id: string
  value: string
  onChange: (value: string) => void
  type?: string
  className?: string
  disabled?: boolean
}) {
  return (
    <div className={cn('space-y-2', className)}>
      <Label htmlFor={id}>{label}</Label>
      <Input id={id} type={type} value={value} disabled={disabled} onChange={(e) => onChange(e.target.value)} />
    </div>
  )
}

export function TextAreaField({
  label,
  id,
  value,
  onChange,
  className,
}: {
  label: string
  id: string
  value: string
  onChange: (value: string) => void
  className?: string
}) {
  return (
    <div className={cn('space-y-2 sm:col-span-2', className)}>
      <Label htmlFor={id}>{label}</Label>
      <textarea
        id={id}
        rows={4}
        className="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        value={value}
        onChange={(e) => onChange(e.target.value)}
      />
    </div>
  )
}

export function DossierLoading() {
  return <p className="text-sm text-muted-foreground">Chargement de votre dossier…</p>
}

export function DossierError() {
  return <p className="text-sm text-red-600">Impossible de charger votre dossier.</p>
}
