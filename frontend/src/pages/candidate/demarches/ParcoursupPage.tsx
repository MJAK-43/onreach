import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { CandidatePanel, CandidateSectionTitle } from '@/components/candidate/CandidatePageLayout'
import {
  DocumentStatusList,
  ErrorState,
  LoadingState,
  ProcedureTabs,
  TimelineList,
} from '@/components/candidate/demarches/DemarchesComponents'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { fetchMyParcoursup } from '@/lib/demarches-api'

const TABS = [
  { id: 'info', label: 'Informations' },
  { id: 'wishes', label: 'Vœux' },
  { id: 'documents', label: 'Documents' },
  { id: 'history', label: 'Historique' },
  { id: 'messages', label: 'Messages' },
]

function wishBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'info' | 'default' {
  if (status === 'accepte') return 'success'
  if (status === 'liste_attente' || status === 'en_analyse') return 'warning'
  if (status === 'refuse') return 'danger'
  return 'default'
}

export function ParcoursupPage() {
  const [tab, setTab] = useState('wishes')
  const { data, isLoading, isError } = useQuery({
    queryKey: ['me-parcoursup'],
    queryFn: fetchMyParcoursup,
  })

  if (isLoading) return <LoadingState />
  if (isError || !data) return <ErrorState />

  return (
    <div className="space-y-6">
      <CandidatePanel>
        <div className="flex flex-wrap items-start justify-between gap-4">
          <div>
            <h2 className="text-lg font-bold text-slate-900">Parcoursup</h2>
            <Badge className="mt-2">{data.card.statusLabel}</Badge>
          </div>
          <div className="w-full sm:w-48">
            <p className="mb-1 text-right text-sm font-semibold text-slate-900">{data.card.progress}%</p>
            <Progress value={data.card.progress} />
          </div>
        </div>
      </CandidatePanel>

      <ProcedureTabs tabs={TABS} active={tab} onChange={setTab} />

      {tab === 'info' && (
        <CandidatePanel>
          <CandidateSectionTitle>Informations</CandidateSectionTitle>
          {data.information ? (
            <dl className="mt-4 space-y-3 text-sm">
              <div>
                <dt className="text-slate-500">N° INE</dt>
                <dd className="font-medium text-slate-900">{String(data.information.ineNumber ?? '—')}</dd>
              </div>
              <div>
                <dt className="text-slate-500">Lycée</dt>
                <dd className="font-medium text-slate-900">{String(data.information.highSchool ?? '—')}</dd>
              </div>
            </dl>
          ) : (
            <p className="mt-3 text-sm text-slate-500">Dossier Parcoursup non initialisé.</p>
          )}
        </CandidatePanel>
      )}

      {tab === 'wishes' && (
        <CandidatePanel>
          <CandidateSectionTitle>Vœux Parcoursup</CandidateSectionTitle>
          <ul className="mt-4 divide-y divide-slate-100">
            {data.wishes.map((wish) => (
              <li key={wish.id} className="flex flex-wrap items-start justify-between gap-3 py-4 first:pt-0">
                <div>
                  <p className="font-medium text-slate-900">{wish.formation}</p>
                  <p className="text-sm text-slate-500">{wish.university}</p>
                  {wish.submittedAt && (
                    <p className="mt-1 text-xs text-slate-400">
                      Dépôt : {new Date(wish.submittedAt).toLocaleDateString('fr-FR')}
                    </p>
                  )}
                </div>
                <Badge variant={wishBadgeVariant(wish.status)}>{wish.statusLabel}</Badge>
              </li>
            ))}
            {data.wishes.length === 0 && (
              <li className="py-4 text-sm text-slate-500">Aucun vœu enregistré.</li>
            )}
          </ul>
        </CandidatePanel>
      )}

      {tab === 'documents' && (
        <CandidatePanel>
          <CandidateSectionTitle>Documents Parcoursup</CandidateSectionTitle>
          <div className="mt-4">
            <DocumentStatusList documents={data.documents} />
          </div>
        </CandidatePanel>
      )}

      {tab === 'history' && (
        <CandidatePanel>
          <CandidateSectionTitle>Historique</CandidateSectionTitle>
          <div className="mt-4">
            <TimelineList entries={data.history} />
          </div>
        </CandidatePanel>
      )}

      {tab === 'messages' && (
        <CandidatePanel>
          <CandidateSectionTitle>Messages</CandidateSectionTitle>
          <p className="mt-3 text-sm text-slate-500">Messagerie en lecture seule — bientôt disponible.</p>
        </CandidatePanel>
      )}
    </div>
  )
}
