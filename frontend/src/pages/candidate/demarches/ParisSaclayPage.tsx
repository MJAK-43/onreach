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
import { fetchMyParisSaclay } from '@/lib/demarches-api'

const TABS = [
  { id: 'steps', label: 'Étapes' },
  { id: 'info', label: 'Informations' },
  { id: 'documents', label: 'Documents' },
  { id: 'project', label: 'Projet' },
  { id: 'history', label: 'Historique' },
  { id: 'messages', label: 'Messages' },
]

export function ParisSaclayPage() {
  const [tab, setTab] = useState('steps')
  const { pathway, isLoading: pathwaysLoading, isError: pathwaysError } = usePathway('paris_saclay')
  const legacyQuery = useQuery({
    queryKey: ['me-paris-saclay'],
    queryFn: fetchMyParisSaclay,
    enabled: tab !== 'steps',
  })

  if (pathwaysLoading) return <LoadingState />
  if (pathwaysError || !pathway) {
    return (
      <ErrorState message="Parcours Paris-Saclay non disponible pour votre profil (poursuite d'études uniquement)." />
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
                <dt className="text-slate-500">Niveau</dt>
                <dd className="font-medium text-slate-900">
                  {String(legacy.information.degreeLevelLabel ?? '—')}
                </dd>
              </div>
            </dl>
          ) : (
            <p className="mt-3 text-sm text-slate-500">Candidature non initialisée.</p>
          )}
        </CandidatePanel>
      )}

      {tab === 'documents' && (
        <CandidatePanel>
          <CandidateSectionTitle>Documents Paris-Saclay</CandidateSectionTitle>
          <div className="mt-4">
            {legacy?.documents ? (
              <DocumentStatusList documents={legacy.documents} />
            ) : (
              <p className="text-sm text-slate-500">Chargement…</p>
            )}
          </div>
        </CandidatePanel>
      )}

      {tab === 'project' && (
        <CandidatePanel>
          <CandidateSectionTitle>Projet de recherche</CandidateSectionTitle>
          <p className="mt-4 text-sm text-slate-700">
            {legacy ? String(legacy.project.researchProject ?? 'Aucun projet renseigné.') : 'Chargement…'}
          </p>
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
