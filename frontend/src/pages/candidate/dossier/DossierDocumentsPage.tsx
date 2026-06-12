import { useQuery, useQueryClient } from '@tanstack/react-query'
import { ExternalLink, Trash2 } from 'lucide-react'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { DocumentDropzone } from '@/components/candidate/dossier/DocumentDropzone'
import { DossierError, DossierLoading, SectionCard } from '@/components/candidate/dossier/DossierComponents'
import {
  deleteMyDocument,
  documentDownloadUrl,
  fetchMyProfileDocuments,
  type MyDocument,
} from '@/lib/profile-api'
import { getAccessToken } from '@/lib/auth'

function statusVariant(status: string): 'default' | 'success' | 'warning' | 'danger' | 'info' {
  if (status === 'validated') return 'success'
  if (status === 'rejected') return 'danger'
  if (status === 'uploaded') return 'info'
  return 'warning'
}

export function DossierDocumentsPage() {
  const queryClient = useQueryClient()
  const { data, isLoading, isError } = useQuery({
    queryKey: ['my-documents'],
    queryFn: fetchMyProfileDocuments,
  })

  const onUploaded = (doc: MyDocument) => {
    queryClient.setQueryData<MyDocument[]>(['my-documents'], (prev) => {
      const list = prev ?? []
      const idx = list.findIndex((d) => d.id === doc.id)
      if (idx >= 0) {
        const next = [...list]
        next[idx] = doc
        return next
      }
      return [...list, doc]
    })
  }

  const handleDelete = async (id: string) => {
    await deleteMyDocument(id)
    queryClient.setQueryData<MyDocument[]>(['my-documents'], (prev) => (prev ?? []).filter((d) => d.id !== id))
  }

  const openPreview = async (doc: MyDocument) => {
    const url = documentDownloadUrl(doc.id)
    const token = getAccessToken()
    const response = await fetch(url, { headers: token ? { Authorization: `Bearer ${token}` } : {} })
    const blob = await response.blob()
    window.open(URL.createObjectURL(blob), '_blank')
  }

  if (isLoading) {
    return <DossierLoading />
  }
  if (isError) {
    return <DossierError />
  }

  return (
    <div className="space-y-6">
      <SectionCard title="Téléverser des documents" description="Glisser-déposer ou parcourir vos fichiers.">
        <DocumentDropzone onUploaded={onUploaded} />
      </SectionCard>

      <SectionCard title="Documents associés" description={`${data?.length ?? 0} document(s)`}>
        <div className="space-y-3">
          {(data ?? []).map((doc) => (
            <div
              key={doc.id}
              className="flex flex-col gap-3 rounded-lg border p-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700"
            >
              <div className="min-w-0">
                <p className="font-medium">{doc.typeLabel}</p>
                <p className="truncate text-sm text-muted-foreground">{doc.originalFilename ?? '—'}</p>
                <div className="mt-2 flex flex-wrap items-center gap-2">
                  <Badge variant={statusVariant(doc.status)}>{doc.statusLabel}</Badge>
                  <span className="text-xs text-muted-foreground">v{doc.version}</span>
                  {doc.rejectionReason ? (
                    <span className="text-xs text-red-600">{doc.rejectionReason}</span>
                  ) : null}
                </div>
              </div>
              <div className="flex shrink-0 gap-2">
                {doc.previewable ? (
                  <Button type="button" size="sm" variant="outline" onClick={() => void openPreview(doc)}>
                    <ExternalLink className="mr-1 h-4 w-4" />
                    Aperçu
                  </Button>
                ) : null}
                {doc.status !== 'validated' ? (
                  <Button type="button" size="sm" variant="outline" onClick={() => void handleDelete(doc.id)}>
                    <Trash2 className="mr-1 h-4 w-4" />
                    Supprimer
                  </Button>
                ) : null}
              </div>
            </div>
          ))}
          {!data?.length ? <p className="text-sm text-muted-foreground">Aucun document téléversé.</p> : null}
        </div>
      </SectionCard>
    </div>
  )
}
