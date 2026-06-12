import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { CandidatePanel, CandidateSectionTitle } from '@/components/candidate/CandidatePageLayout'
import {
  DocumentStatusList,
  ErrorState,
  LoadingState,
  ProcedureTabs,
  TimelineList,
  WorkflowStepper,
} from '@/components/candidate/demarches/DemarchesComponents'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { fetchMyCampusFrance } from '@/lib/demarches-api'

const TABS = [
  { id: 'info', label: 'Informations' },
  { id: 'documents', label: 'Documents' },
  { id: 'workflow', label: 'Workflow' },
  { id: 'history', label: 'Historique' },
  { id: 'messages', label: 'Messages' },
]

export function CampusFrancePage() {
  const [tab, setTab] = useState('info')
  const { data, isLoading, isError } = useQuery({
    queryKey: ['me-campus-france'],
    queryFn: fetchMyCampusFrance,
  })

  if (isLoading) return <LoadingState />
  if (isError || !data) return <ErrorState />

  return (
    <div className="space-y-6">
      <CandidatePanel>
        <div className="flex flex-wrap items-start justify-between gap-4">
          <div>
            <h2 className="text-lg font-bold text-slate-900">Campus France</h2>
            <Badge variant="info" className="mt-2">
              {data.card.statusLabel}
            </Badge>
          </div>
          <div className="w-full sm:w-48">
            <p className="mb-1 text-right text-sm font-semibold text-slate-900">{data.card.progress}%</p>
            <Progress value={data.card.progress} />
          </div>
        </div>
        <p className="mt-4 text-sm text-blue-600">{data.card.nextAction}</p>
      </CandidatePanel>

      <ProcedureTabs tabs={TABS} active={tab} onChange={setTab} />

      {tab === 'info' && (
        <CandidatePanel>
          <CandidateSectionTitle>Informations</CandidateSectionTitle>
          <dl className="mt-4 space-y-3 text-sm">
            <div>
              <dt className="text-slate-500">Projet d&apos;études</dt>
              <dd className="font-medium text-slate-900">
                {(data.information.studyProject as string) ?? '—'}
              </dd>
            </div>
            <div>
              <dt className="text-slate-500">Projet professionnel</dt>
              <dd className="font-medium text-slate-900">
                {(data.information.professionalProject as string) ?? '—'}
              </dd>
            </div>
          </dl>
        </CandidatePanel>
      )}

      {tab === 'documents' && (
        <CandidatePanel>
          <CandidateSectionTitle>Documents Campus France</CandidateSectionTitle>
          <div className="mt-4">
            <DocumentStatusList documents={data.documents} />
          </div>
        </CandidatePanel>
      )}

      {tab === 'workflow' && (
        <CandidatePanel>
          <CandidateSectionTitle>Workflow Campus France</CandidateSectionTitle>
          <div className="mt-4">
            <WorkflowStepper steps={data.workflow} />
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
          <p className="mt-3 text-sm text-slate-500">
            {data.messages.readOnly
              ? 'Lecture seule — la messagerie avec votre conseiller sera bientôt disponible.'
              : null}
          </p>
        </CandidatePanel>
      )}
    </div>
  )
}
