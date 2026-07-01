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
import { PathwayHero } from '@/components/candidate/demarches/PathwayComponents'
import { StaffEditablePathwayTimeline } from '@/components/staff/pathways/StaffEditablePathwayTimeline'
import { useCandidatePathways } from '@/hooks/useCandidatePathways'
import {
  fetchCandidateCampusFrance,
  fetchCandidateParisSaclay,
  fetchCandidateParcoursup,
} from '@/lib/candidate-demarches-api'
import type { CampusFranceData, ParisSaclayData, ParcoursupData } from '@/lib/demarches-api'
import { findPathwayByCode, type PathwayCode } from '@/lib/pathways-api'
import { Badge } from '@/components/ui/badge'

const TAB_CONFIG: Record<PathwayCode, Array<{ id: string; label: string }>> = {
  campus_france: [
    { id: 'steps', label: 'Étapes' },
    { id: 'info', label: 'Informations' },
    { id: 'documents', label: 'Documents' },
    { id: 'history', label: 'Historique' },
  ],
  parcoursup: [
    { id: 'steps', label: 'Étapes' },
    { id: 'info', label: 'Informations' },
    { id: 'wishes', label: 'Vœux' },
    { id: 'documents', label: 'Documents' },
    { id: 'history', label: 'Historique' },
  ],
  paris_saclay: [
    { id: 'steps', label: 'Étapes' },
    { id: 'info', label: 'Informations' },
    { id: 'documents', label: 'Documents' },
    { id: 'project', label: 'Projet' },
    { id: 'history', label: 'Historique' },
  ],
}

function wishBadgeVariant(status: string): 'success' | 'warning' | 'danger' | 'info' | 'default' {
  if (status === 'accepte') return 'success'
  if (status === 'liste_attente' || status === 'en_analyse') return 'warning'
  if (status === 'refuse') return 'danger'
  return 'default'
}

function useCandidateLegacyData(
  candidateId: string,
  pathwayCode: PathwayCode,
  tab: string,
  enabled: boolean,
) {
  const campusQuery = useQuery({
    queryKey: ['candidate-demarches', candidateId, 'campus_france'],
    queryFn: () => fetchCandidateCampusFrance(candidateId),
    enabled: enabled && pathwayCode === 'campus_france' && tab !== 'steps',
  })
  const parcoursupQuery = useQuery({
    queryKey: ['candidate-demarches', candidateId, 'parcoursup'],
    queryFn: () => fetchCandidateParcoursup(candidateId),
    enabled: enabled && pathwayCode === 'parcoursup' && tab !== 'steps',
  })
  const parisSaclayQuery = useQuery({
    queryKey: ['candidate-demarches', candidateId, 'paris_saclay'],
    queryFn: () => fetchCandidateParisSaclay(candidateId),
    enabled: enabled && pathwayCode === 'paris_saclay' && tab !== 'steps',
  })

  if (pathwayCode === 'campus_france') {
    return { data: campusQuery.data, isLoading: campusQuery.isLoading }
  }
  if (pathwayCode === 'parcoursup') {
    return { data: parcoursupQuery.data, isLoading: parcoursupQuery.isLoading }
  }
  return { data: parisSaclayQuery.data, isLoading: parisSaclayQuery.isLoading }
}

export function StaffCandidatePathwayView({
  candidateId,
  pathwayCode,
}: {
  candidateId: string
  pathwayCode: PathwayCode
}) {
  const [tab, setTab] = useState('steps')
  const pathwaysQuery = useCandidatePathways(candidateId)
  const pathway = findPathwayByCode(pathwaysQuery.data?.pathways ?? [], pathwayCode)
  const legacyQuery = useCandidateLegacyData(
    candidateId,
    pathwayCode,
    tab,
    Boolean(pathway),
  )

  if (pathwaysQuery.isLoading) return <LoadingState />
  if (pathwaysQuery.isError || !pathway) {
    return (
      <ErrorState message="Ce parcours n'est pas assigné à ce candidat ou est inaccessible." />
    )
  }

  const tabs = TAB_CONFIG[pathwayCode]
  const campusLegacy = legacyQuery.data as CampusFranceData | undefined
  const parcoursupLegacy = legacyQuery.data as ParcoursupData | undefined
  const parisLegacy = legacyQuery.data as ParisSaclayData | undefined

  return (
    <div className="space-y-6">
      <PathwayHero pathway={pathway} />
      <ProcedureTabs tabs={tabs} active={tab} onChange={setTab} />

      {tab === 'steps' && (
        <StaffEditablePathwayTimeline pathway={pathway} candidateId={candidateId} />
      )}

      {tab === 'info' && pathwayCode === 'campus_france' && (
        <CandidatePanel>
          <CandidateSectionTitle>Informations</CandidateSectionTitle>
          {legacyQuery.isLoading ? (
            <p className="mt-3 text-sm text-slate-500">Chargement…</p>
          ) : campusLegacy?.information ? (
            <dl className="mt-4 space-y-3 text-sm">
              <div>
                <dt className="text-slate-500">Projet d&apos;études</dt>
                <dd className="font-medium text-slate-900">
                  {String(campusLegacy.information.studyProject ?? '—')}
                </dd>
              </div>
              <div>
                <dt className="text-slate-500">Projet professionnel</dt>
                <dd className="font-medium text-slate-900">
                  {String(campusLegacy.information.professionalProject ?? '—')}
                </dd>
              </div>
            </dl>
          ) : (
            <p className="mt-3 text-sm text-slate-500">Informations indisponibles.</p>
          )}
        </CandidatePanel>
      )}

      {tab === 'info' && pathwayCode === 'parcoursup' && (
        <CandidatePanel>
          <CandidateSectionTitle>Informations</CandidateSectionTitle>
          {legacyQuery.isLoading ? (
            <p className="mt-3 text-sm text-slate-500">Chargement…</p>
          ) : parcoursupLegacy?.information ? (
            <dl className="mt-4 space-y-3 text-sm">
              <div>
                <dt className="text-slate-500">N° INE</dt>
                <dd className="font-medium text-slate-900">
                  {String(parcoursupLegacy.information.ineNumber ?? '—')}
                </dd>
              </div>
              <div>
                <dt className="text-slate-500">Lycée</dt>
                <dd className="font-medium text-slate-900">
                  {String(parcoursupLegacy.information.highSchool ?? '—')}
                </dd>
              </div>
            </dl>
          ) : (
            <p className="mt-3 text-sm text-slate-500">Dossier Parcoursup non initialisé.</p>
          )}
        </CandidatePanel>
      )}

      {tab === 'info' && pathwayCode === 'paris_saclay' && (
        <CandidatePanel>
          <CandidateSectionTitle>Informations</CandidateSectionTitle>
          {legacyQuery.isLoading ? (
            <p className="mt-3 text-sm text-slate-500">Chargement…</p>
          ) : parisLegacy?.information ? (
            <dl className="mt-4 space-y-3 text-sm">
              <div>
                <dt className="text-slate-500">Niveau</dt>
                <dd className="font-medium text-slate-900">
                  {String(parisLegacy.information.degreeLevelLabel ?? '—')}
                </dd>
              </div>
            </dl>
          ) : (
            <p className="mt-3 text-sm text-slate-500">Candidature non initialisée.</p>
          )}
        </CandidatePanel>
      )}

      {tab === 'wishes' && pathwayCode === 'parcoursup' && (
        <CandidatePanel>
          <CandidateSectionTitle>Vœux Parcoursup</CandidateSectionTitle>
          {legacyQuery.isLoading ? (
            <p className="mt-3 text-sm text-slate-500">Chargement…</p>
          ) : (
            <ul className="mt-4 divide-y divide-slate-100">
              {parcoursupLegacy?.wishes.map((wish) => (
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
          <CandidateSectionTitle>Documents</CandidateSectionTitle>
          <div className="mt-4">
            {legacyQuery.isLoading ? (
              <p className="text-sm text-slate-500">Chargement…</p>
            ) : legacyQuery.data ? (
              <DocumentStatusList documents={legacyQuery.data.documents} />
            ) : (
              <p className="text-sm text-slate-500">Aucun document.</p>
            )}
          </div>
        </CandidatePanel>
      )}

      {tab === 'project' && pathwayCode === 'paris_saclay' && (
        <CandidatePanel>
          <CandidateSectionTitle>Projet de recherche</CandidateSectionTitle>
          <p className="mt-4 text-sm text-slate-700">
            {legacyQuery.isLoading
              ? 'Chargement…'
              : String(parisLegacy?.project.researchProject ?? 'Aucun projet renseigné.')}
          </p>
        </CandidatePanel>
      )}

      {tab === 'history' && (
        <CandidatePanel>
          <CandidateSectionTitle>Historique</CandidateSectionTitle>
          <div className="mt-4">
            {legacyQuery.isLoading ? (
              <p className="text-sm text-slate-500">Chargement…</p>
            ) : legacyQuery.data ? (
              <TimelineList entries={legacyQuery.data.history} />
            ) : (
              <p className="text-sm text-slate-500">Aucun événement.</p>
            )}
          </div>
        </CandidatePanel>
      )}
    </div>
  )
}
