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
import { fetchMyCampusFrance } from '@/lib/demarches-api'

const TABS = [
  { id: 'steps', label: 'Étapes' },
  { id: 'info', label: 'Informations' },
  { id: 'documents', label: 'Documents' },
  { id: 'history', label: 'Historique' },
  { id: 'messages', label: 'Messages' },
]

export function CampusFrancePage() {
  const [tab, setTab] = useState('steps')
  const { pathway, isLoading: pathwaysLoading, isError: pathwaysError } = usePathway('campus_france')
  const legacyQuery = useQuery({
    queryKey: ['me-campus-france'],
    queryFn: fetchMyCampusFrance,
    enabled: tab !== 'steps',
  })

  if (pathwaysLoading) return <LoadingState />
  if (pathwaysError || !pathway) {
    return (
      <ErrorState message="Parcours Campus France non disponible. Vérifiez votre type de candidature dans Mon dossier." />
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
          ) : legacy ? (
            <dl className="mt-4 space-y-3 text-sm">
              <div>
                <dt className="text-slate-500">Projet d&apos;études</dt>
                <dd className="font-medium text-slate-900">
                  {(legacy.information.studyProject as string) ?? '—'}
                </dd>
              </div>
              <div>
                <dt className="text-slate-500">Projet professionnel</dt>
                <dd className="font-medium text-slate-900">
                  {(legacy.information.professionalProject as string) ?? '—'}
                </dd>
              </div>
            </dl>
          ) : (
            <p className="mt-3 text-sm text-slate-500">Informations complémentaires indisponibles.</p>
          )}
        </CandidatePanel>
      )}

      {tab === 'documents' && (
        <CandidatePanel>
          <CandidateSectionTitle>Documents Campus France</CandidateSectionTitle>
          <div className="mt-4">
            {legacyQuery.isLoading ? (
              <p className="text-sm text-slate-500">Chargement…</p>
            ) : legacy ? (
              <DocumentStatusList documents={legacy.documents} />
            ) : (
              <p className="text-sm text-slate-500">Aucun document.</p>
            )}
          </div>
        </CandidatePanel>
      )}

      {tab === 'history' && (
        <CandidatePanel>
          <CandidateSectionTitle>Historique</CandidateSectionTitle>
          <div className="mt-4">
            {legacyQuery.isLoading ? (
              <p className="text-sm text-slate-500">Chargement…</p>
            ) : legacy ? (
              <TimelineList entries={legacy.history} />
            ) : (
              <p className="text-sm text-slate-500">Aucun événement.</p>
            )}
          </div>
        </CandidatePanel>
      )}

      {tab === 'messages' && (
        <CandidatePanel>
          <CandidateSectionTitle>Messages</CandidateSectionTitle>
          <p className="mt-3 text-sm text-slate-500">
            Lecture seule — la messagerie avec votre conseiller sera bientôt disponible.
          </p>
        </CandidatePanel>
      )}
    </div>
  )
}
