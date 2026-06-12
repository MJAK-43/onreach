import { useQuery } from '@tanstack/react-query'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { DossierError, DossierLoading } from '@/components/candidate/dossier/DossierComponents'
import { fetchMyHistory } from '@/lib/profile-api'

export function DossierHistoryPage() {
  const { data, isLoading, isError } = useQuery({
    queryKey: ['my-history'],
    queryFn: fetchMyHistory,
  })

  if (isLoading) {
    return <DossierLoading />
  }
  if (isError) {
    return <DossierError />
  }

  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-base">Historique du dossier</CardTitle>
      </CardHeader>
      <CardContent>
        <ol className="relative border-l border-slate-200 dark:border-slate-700">
          {(data ?? []).map((entry) => (
            <li key={entry.id} className="mb-6 ml-4">
              <span className="absolute -left-1.5 mt-1.5 h-3 w-3 rounded-full border border-white bg-blue-500 dark:border-slate-900" />
              <time className="text-xs text-muted-foreground">{entry.date}</time>
              <p className="text-sm font-medium">{entry.description}</p>
              <p className="text-xs text-muted-foreground">{entry.author}</p>
            </li>
          ))}
        </ol>
        {!data?.length ? <p className="text-sm text-muted-foreground">Aucun événement enregistré.</p> : null}
      </CardContent>
    </Card>
  )
}
