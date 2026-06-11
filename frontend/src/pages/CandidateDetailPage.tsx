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
} from '@/lib/api'
import { PermissionGate } from '@/components/auth/PermissionGate'
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
  { id: 'campus', label: 'Campus France' },
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

      {tab === 'documents' && (
        <Card>
          <CardHeader>
            <CardTitle>Documents</CardTitle>
            <CardDescription>
              {documentsQuery.data?.length ?? 0} document(s)
            </CardDescription>
          </CardHeader>
          <CardContent>
            {documentsQuery.isLoading && <p className="text-sm">Chargement...</p>}
            {documentsQuery.data?.length === 0 && (
              <p className="text-sm text-muted-foreground">Aucun document.</p>
            )}
            {documentsQuery.data && documentsQuery.data.length > 0 && (
              <ul className="space-y-2 text-sm">
                {documentsQuery.data.map((doc) => (
                  <li key={doc.id} className="flex justify-between border-b border-border py-2">
                    <span>{doc.type}</span>
                    <span className="text-muted-foreground">{doc.status}</span>
                  </li>
                ))}
              </ul>
            )}
          </CardContent>
        </Card>
      )}

      {tab === 'campus' && completionQuery.data && (
        <Card>
          <CardHeader>
            <CardTitle>Checklist Campus France</CardTitle>
            <CardDescription>{completionQuery.data.checklist.percent}% complété</CardDescription>
          </CardHeader>
          <CardContent>
            <ul className="space-y-2 text-sm">
              {completionQuery.data.checklist.items.map((item) => (
                <li key={item.id} className="flex items-center gap-2">
                  <span>{item.completed ? '✓' : '✗'}</span>
                  <span>{item.label}</span>
                  {item.required && (
                    <span className="text-xs text-muted-foreground">(requis)</span>
                  )}
                </li>
              ))}
            </ul>
          </CardContent>
        </Card>
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
