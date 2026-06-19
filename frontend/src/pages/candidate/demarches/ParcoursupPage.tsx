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
import { PathwayHero, PathwayStageTimeline } from '@/components/candidate/demarches/PathwayComponents'
import { usePathway } from '@/hooks/useMyPathways'
import { fetchMyParcoursup } from '@/lib/demarches-api'
import { Badge } from '@/components/ui/badge'

const TABS = [
  { id: 'steps', label: 'Étapes' },
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
  const [tab, setTab] = useState('steps')
  const { pathway, isLoading: pathwaysLoading, isError: pathwaysError } = usePathway('parcoursup')
  const legacyQuery = useQuery({
    queryKey: ['me-parcoursup'],
    queryFn: fetchMyParcoursup,
    enabled: tab !== 'steps',
  })

  if (pathwaysLoading) return <LoadingState />
  if (pathwaysError || !pathway) {
    return (
      <ErrorState message="Parcours Parcoursup non disponible pour votre profil (première année uniquement)." />
    )
  }

  const legacy = legacyQuery.data

  return (
    <div className="space-y-6">
      <PathwayHero pathway={pathway} />
      <ProcedureTabs tabs={TABS} active={tab} onChange={setTab} />

      {tab === 'steps' && <PathwayStageTimeline pathway={pathway} />}

      {tab === 'info' && (
        <CandidatePanel>
          <CandidateSectionTitle>Informations</CandidateSectionTitle>
          {legacyQuery.isLoading ? (
            <p className="mt-3 text-sm text-slate-500">Chargement…</p>
          ) : legacy?.information ? (
            <dl className="mt-4 space-y-3 text-sm">
              <div>
                <dt className="text-slate-500">N° INE</dt>
                <dd className="font-medium text-slate-900">{String(legacy.information.ineNumber ?? '—')}</dd>
              </div>
              <div>
                <dt className="text-slate-500">Lycée</dt>
                <dd className="font-medium text-slate-900">{String(legacy.information.highSchool ?? '—')}</dd>
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
          {legacyQuery.isLoading ? (
            <p className="mt-3 text-sm text-slate-500">Chargement…</p>
          ) : (
            <ul className="mt-4 divide-y divide-slate-100">
              {legacy?.wishes.map((wish) => (
                <li key={wish.id} className="flex flex-wrap items-start justify-between gap-3 py-4 first:pt-0">
                  <div>
                    <p className="font-medium text-slate-900">{wish.formation}</p>
                    <p className="text-sm text-slate-500">{wish.university}</p>
                  </div>
                  <Badge variant={wishBadgeVariant(wish.status)}>{wish.statusLabel}</Badge>
                </li>
              )) ?? (
                <li className="py-4 text-sm text-slate-500">Aucun vœu enregistré.</li>
              )}
            </ul>
          )}
        </CandidatePanel>
      )}

      {tab === 'documents' && (
        <CandidatePanel>
          <CandidateSectionTitle>Documents Parcoursup</CandidateSectionTitle>
          <div className="mt-4">
            {legacy?.documents ? (
              <DocumentStatusList documents={legacy.documents} />
            ) : (
              <p className="text-sm text-slate-500">Chargement…</p>
            )}
          </div>
        </CandidatePanel>
      )}

      {tab === 'history' && (
        <CandidatePanel>
          <CandidateSectionTitle>Historique</CandidateSectionTitle>
          <div className="mt-4">
            {legacy ? <TimelineList entries={legacy.history} /> : <p className="text-sm text-slate-500">Chargement…</p>}
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
