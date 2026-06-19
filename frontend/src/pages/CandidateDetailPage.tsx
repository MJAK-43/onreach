import { useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Link, useParams } from 'react-router-dom'
import {
  createCandidateNote,
  fetchCandidate,
  fetchCandidateCompletion,
  fetchCandidateDocuments,
  fetchCandidateNotes,
  fetchCandidateTimeline,
  rejectCandidateDocument,
  validateCandidateDocument,
  type CandidateDocumentItem,
} from '@/lib/api'
import { PermissionGate } from '@/components/auth/PermissionGate'
import { StaffPathwaysTab } from '@/components/staff/pathways/StaffPathwaysTab'
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

const TABS = [
  { id: 'info', label: 'Informations' },
  { id: 'documents', label: 'Documents' },
  { id: 'pathways', label: 'Parcours' },
  { id: 'timeline', label: 'Historique' },
  { id: 'notes', label: 'Notes' },
] as const

type TabId = (typeof TABS)[number]['id']

export function CandidateDetailPage() {
  const { id = '' } = useParams()
  const [tab, setTab] = useState<TabId>('info')

  const candidateQuery = useQuery({
    queryKey: ['candidate', id],
    queryFn: () => fetchCandidate(id),
    enabled: Boolean(id),
  })

  const completionQuery = useQuery({
    queryKey: ['candidate-completion', id],
    queryFn: () => fetchCandidateCompletion(id),
    enabled: Boolean(id),
  })

  const documentsQuery = useQuery({
    queryKey: ['candidate-documents', id],
    queryFn: () => fetchCandidateDocuments(id),
    enabled: Boolean(id) && tab === 'documents',
  })

  const timelineQuery = useQuery({
    queryKey: ['candidate-timeline', id],
    queryFn: () => fetchCandidateTimeline(id),
    enabled: Boolean(id) && tab === 'timeline',
  })

  const notesQuery = useQuery({
    queryKey: ['candidate-notes', id],
    queryFn: () => fetchCandidateNotes(id),
    enabled: Boolean(id) && tab === 'notes',
  })

  const candidate = candidateQuery.data

  if (candidateQuery.isLoading) {
    return <p className="text-sm text-muted-foreground">Chargement du dossier...</p>
  }

  if (candidateQuery.isError || !candidate) {
    return (
      <div className="space-y-4">
        <p className="text-sm text-red-600">Candidat introuvable.</p>
        <Button variant="outline" asChild>
          <Link to="/candidates">Retour</Link>
        </Button>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <div>
          <p className="text-sm text-muted-foreground">{candidate.referenceNumber}</p>
          <h2 className="text-xl font-semibold">
            {candidate.firstName} {candidate.lastName}
          </h2>
          <p className="text-sm text-muted-foreground">{candidate.email}</p>
        </div>
        <Button variant="outline" asChild>
          <Link to="/candidates">← Retour à la liste</Link>
        </Button>
      </div>

      {completionQuery.data && (
        <Card>
          <CardHeader>
            <CardTitle className="text-base">Complétude du dossier</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <CompletionBar label="Profil" value={completionQuery.data.profile} />
              <CompletionBar label="Documents" value={completionQuery.data.documents} />
              <CompletionBar label="Financement" value={completionQuery.data.financing} />
              <CompletionBar label="Campus France" value={completionQuery.data.campusFrance} />
            </div>
          </CardContent>
        </Card>
      )}

      <div className="flex flex-wrap gap-2 border-b border-border pb-2">
        {TABS.map((t) => (
          <button
            key={t.id}
            type="button"
            onClick={() => setTab(t.id)}
            className={`rounded-lg px-3 py-1.5 text-sm font-medium transition-colors ${
              tab === t.id
                ? 'bg-primary text-primary-foreground'
                : 'text-muted-foreground hover:bg-muted'
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>

      {tab === 'info' && (
        <Card>
          <CardHeader>
            <CardTitle>Informations personnelles</CardTitle>
          </CardHeader>
          <CardContent className="grid gap-3 text-sm sm:grid-cols-2">
            <InfoRow label="Nationalité" value={candidate.nationality} />
            <InfoRow label="Statut" value={candidate.status} />
            <InfoRow label="Téléphone" value={candidate.phone ?? '—'} />
            <InfoRow label="Ville" value={candidate.city ?? '—'} />
            <InfoRow label="Pays" value={candidate.country ?? '—'} />
          </CardContent>
        </Card>
      )}

      {tab === 'documents' && id && (
        <DocumentsTab
          candidateId={id}
          documents={documentsQuery.data ?? []}
          loading={documentsQuery.isLoading}
        />
      )}

      {tab === 'pathways' && id && (
        <PermissionGate permission="applications.view">
          <StaffPathwaysTab candidateId={id} />
        </PermissionGate>
      )}

      {tab === 'timeline' && (
        <Card>
          <CardHeader>
            <CardTitle>Historique</CardTitle>
          </CardHeader>
          <CardContent>
            {timelineQuery.isLoading && <p className="text-sm">Chargement...</p>}
            {timelineQuery.data && (
              <ul className="space-y-3 text-sm">
                {timelineQuery.data.map((entry) => (
                  <li key={entry.id} className="border-l-2 border-primary pl-3">
                    <p className="font-medium">{entry.description}</p>
                    <p className="text-xs text-muted-foreground">
                      {new Date(entry.occurredAt).toLocaleString('fr-FR')}
                      {entry.actor ? ` — ${entry.actor}` : ''}
                    </p>
                  </li>
                ))}
              </ul>
            )}
          </CardContent>
        </Card>
      )}

      {tab === 'notes' && (
        <PermissionGate permission="candidates.notes">
          <NotesTab candidateId={id} notes={notesQuery.data ?? []} loading={notesQuery.isLoading} />
        </PermissionGate>
      )}
    </div>
  )
}

const DOC_STATUS_COLORS: Record<string, string> = {
  missing: 'bg-slate-100 text-slate-600',
  uploaded: 'bg-blue-50 text-blue-700',
  validated: 'bg-emerald-50 text-emerald-700',
  rejected: 'bg-red-50 text-red-700',
}

function DocumentsTab({
  candidateId,
  documents,
  loading,
}: {
  candidateId: string
  documents: CandidateDocumentItem[]
  loading: boolean
}) {
  const queryClient = useQueryClient()
  const [rejectingId, setRejectingId] = useState<string | null>(null)
  const [rejectReason, setRejectReason] = useState('')

  const validateMutation = useMutation({
    mutationFn: (documentId: string) => validateCandidateDocument(candidateId, documentId),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['candidate-documents', candidateId] })
      void queryClient.invalidateQueries({ queryKey: ['candidate-timeline', candidateId] })
    },
  })

  const rejectMutation = useMutation({
    mutationFn: ({ documentId, reason }: { documentId: string; reason: string }) =>
      rejectCandidateDocument(candidateId, documentId, reason),
    onSuccess: () => {
      setRejectingId(null)
      setRejectReason('')
      void queryClient.invalidateQueries({ queryKey: ['candidate-documents', candidateId] })
      void queryClient.invalidateQueries({ queryKey: ['candidate-timeline', candidateId] })
    },
  })

  return (
    <Card>
      <CardHeader>
        <CardTitle>Documents</CardTitle>
        <CardDescription>{documents.length} document(s) — validation conseiller</CardDescription>
      </CardHeader>
      <CardContent>
        {loading && <p className="text-sm">Chargement...</p>}
        {!loading && documents.length === 0 && (
          <p className="text-sm text-muted-foreground">Aucun document téléversé.</p>
        )}
        <ul className="space-y-3">
          {documents.map((doc) => (
            <li key={doc.id} className="rounded-lg border border-border p-4">
              <div className="flex flex-wrap items-start justify-between gap-3">
                <div>
                  <p className="font-medium">{doc.typeLabel ?? doc.type}</p>
                  <p className="text-sm text-muted-foreground">{doc.originalFilename ?? '—'}</p>
                  {doc.rejectionReason && (
                    <p className="mt-1 text-sm text-red-600">Motif : {doc.rejectionReason}</p>
                  )}
                </div>
                <span
                  className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${
                    DOC_STATUS_COLORS[doc.status] ?? DOC_STATUS_COLORS.missing
                  }`}
                >
                  {doc.statusLabel ?? doc.status}
                </span>
              </div>

              <PermissionGate permission="documents.validate">
                {doc.status === 'uploaded' && (
                  <div className="mt-3 flex flex-wrap gap-2">
                    <Button
                      type="button"
                      size="sm"
                      onClick={() => validateMutation.mutate(doc.id)}
                      disabled={validateMutation.isPending}
                    >
                      Valider
                    </Button>
                    <Button
                      type="button"
                      size="sm"
                      variant="outline"
                      onClick={() => setRejectingId(doc.id)}
                    >
                      Refuser
                    </Button>
                  </div>
                )}
              </PermissionGate>

              {rejectingId === doc.id && (
                <div className="mt-3 space-y-2 rounded-md bg-muted/50 p-3">
                  <Label htmlFor={`reject-${doc.id}`}>Motif de refus (obligatoire)</Label>
                  <textarea
                    id={`reject-${doc.id}`}
                    className="flex min-h-20 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                    value={rejectReason}
                    onChange={(e) => setRejectReason(e.target.value)}
                  />
                  <div className="flex gap-2">
                    <Button
                      type="button"
                      size="sm"
                      className="bg-red-600 text-white hover:bg-red-700"
                      disabled={!rejectReason.trim() || rejectMutation.isPending}
                      onClick={() =>
                        rejectMutation.mutate({ documentId: doc.id, reason: rejectReason.trim() })
                      }
                    >
                      Confirmer le refus
                    </Button>
                    <Button type="button" size="sm" variant="ghost" onClick={() => setRejectingId(null)}>
                      Annuler
                    </Button>
                  </div>
                </div>
              )}
            </li>
          ))}
        </ul>
      </CardContent>
    </Card>
  )
}

function CompletionBar({ label, value }: { label: string; value: number }) {
  return (
    <div>
      <div className="mb-1 flex justify-between text-sm">
        <span>{label}</span>
        <span className="font-medium">{value}%</span>
      </div>
      <div className="h-2 overflow-hidden rounded-full bg-muted">
        <div className="h-full rounded-full bg-primary" style={{ width: `${value}%` }} />
      </div>
    </div>
  )
}

function InfoRow({ label, value }: { label: string; value: string }) {
  return (
    <div>
      <span className="text-muted-foreground">{label}</span>
      <p className="font-medium">{value}</p>
    </div>
  )
}

function NotesTab({
  candidateId,
  notes,
  loading,
}: {
  candidateId: string
  notes: Array<{ id: string; title: string; content: string; author: string; createdAt: string }>
  loading: boolean
}) {
  const queryClient = useQueryClient()
  const [title, setTitle] = useState('')
  const [content, setContent] = useState('')

  const mutation = useMutation({
    mutationFn: (payload: { title: string; content: string }) =>
      createCandidateNote(candidateId, payload),
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['candidate-notes', candidateId] })
      setTitle('')
      setContent('')
    },
  })

  return (
    <div className="space-y-4">
      <Card>
        <CardHeader>
          <CardTitle>Notes conseiller</CardTitle>
        </CardHeader>
        <CardContent>
          {loading && <p className="text-sm">Chargement...</p>}
          {notes.length === 0 && !loading && (
            <p className="text-sm text-muted-foreground">Aucune note.</p>
          )}
          <ul className="space-y-4">
            {notes.map((note) => (
              <li key={note.id} className="rounded-lg border border-border p-3 text-sm">
                <p className="font-medium">{note.title}</p>
                <p className="mt-1 whitespace-pre-wrap">{note.content}</p>
                <p className="mt-2 text-xs text-muted-foreground">
                  {note.author} — {new Date(note.createdAt).toLocaleString('fr-FR')}
                </p>
              </li>
            ))}
          </ul>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Ajouter une note</CardTitle>
        </CardHeader>
        <CardContent>
          <form
            className="space-y-3"
            onSubmit={(e) => {
              e.preventDefault()
              mutation.mutate({ title, content })
            }}
          >
            <div className="space-y-2">
              <Label htmlFor="note-title">Titre</Label>
              <Input id="note-title" value={title} onChange={(e) => setTitle(e.target.value)} required />
            </div>
            <div className="space-y-2">
              <Label htmlFor="note-content">Contenu</Label>
              <textarea
                id="note-content"
                className="flex min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                value={content}
                onChange={(e) => setContent(e.target.value)}
                required
              />
            </div>
            <Button type="submit" disabled={mutation.isPending}>
              Enregistrer
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  )
}
