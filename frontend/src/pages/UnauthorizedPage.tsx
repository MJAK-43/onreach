import { Link } from 'react-router-dom'
import { ShieldX } from 'lucide-react'
import { Button } from '@/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'

export function UnauthorizedPage() {
  return (
    <div className="flex min-h-screen items-center justify-center p-4">
      <Card className="w-full max-w-md text-center">
        <CardHeader>
          <div className="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-red-50">
            <ShieldX className="h-6 w-6 text-red-600" />
          </div>
          <CardTitle>Accès refusé</CardTitle>
          <CardDescription>
            Vous n&apos;avez pas les permissions nécessaires pour accéder à cette page.
          </CardDescription>
        </CardHeader>
        <CardContent>
          <Link to="/">
            <Button className="w-full">Retour au tableau de bord</Button>
          </Link>
        </CardContent>
      </Card>
    </div>
  )
}
