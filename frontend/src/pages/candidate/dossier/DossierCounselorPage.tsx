import { useQuery } from '@tanstack/react-query'
import { Link } from 'react-router-dom'
import { Mail, MessageCircle, Phone } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { DossierError, DossierLoading } from '@/components/candidate/dossier/DossierComponents'
import { fetchMyProfile } from '@/lib/profile-api'

export function DossierCounselorPage() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ['my-profile'],
    queryFn: fetchMyProfile,
  })

  if (isLoading) {
    return <DossierLoading />
  }
  if (isError || !data) {
    return <DossierError />
  }

  const counselor = data.counselor

  if (!counselor) {
    return (
      <Card>
        <CardHeader>
          <CardTitle className="text-base">Conseiller attribué</CardTitle>
        </CardHeader>
        <CardContent>
          <p className="text-sm text-muted-foreground">Aucun conseiller n&apos;est encore attribué à votre dossier.</p>
        </CardContent>
      </Card>
    )
  }

  const initials = `${counselor.firstName.charAt(0)}${counselor.lastName.charAt(0)}`.toUpperCase()

  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-base">Conseiller attribué</CardTitle>
      </CardHeader>
      <CardContent className="flex flex-col gap-6 sm:flex-row sm:items-start">
        <div className="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-lg font-semibold dark:bg-slate-800">
          {initials}
        </div>
        <div className="space-y-2">
          <p className="text-lg font-semibold">
            {counselor.firstName} {counselor.lastName}
          </p>
          <p className="flex items-center gap-2 text-sm text-muted-foreground">
            <Mail className="h-4 w-4" />
            {counselor.email}
          </p>
          {counselor.phone ? (
            <p className="flex items-center gap-2 text-sm text-muted-foreground">
              <Phone className="h-4 w-4" />
              {counselor.phone}
            </p>
          ) : null}
          <div className="flex flex-wrap gap-2 pt-2">
            <Button variant="outline" size="sm" asChild>
              <a href={`mailto:${counselor.email}`}>
                <MessageCircle className="mr-1 h-4 w-4" />
                Envoyer message
              </a>
            </Button>
            <Button variant="outline" size="sm" asChild>
              <Link to="/appointments">Prendre rendez-vous</Link>
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>
  )
}
