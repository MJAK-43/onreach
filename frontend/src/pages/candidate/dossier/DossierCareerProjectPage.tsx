import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import {
  DossierError,
  DossierLoading,
  FormField,
  FormGrid,
  SaveIndicator,
  SectionCard,
  TextAreaField,
} from '@/components/candidate/dossier/DossierComponents'
import { useAutoSave } from '@/hooks/useAutoSave'
import { fetchMyProfile, type MyProfile, updateMyProfile } from '@/lib/profile-api'

const EMPTY_CAREER: MyProfile['careerProject'] = {
  targetJob: null,
  objectives: null,
  sector: null,
  description: null,
}

export function DossierCareerProjectPage() {
  const { data: profile, isLoading, isError } = useQuery({
    queryKey: ['my-profile'],
    queryFn: fetchMyProfile,
  })
  const [draft, setDraft] = useState<MyProfile['careerProject'] | null>(null)
  const careerProject = draft ?? profile?.careerProject ?? EMPTY_CAREER
  const patchCareer = (next: MyProfile['careerProject']) => setDraft(next)

  const autoSave = useAutoSave(careerProject, async (payload) => {
    await updateMyProfile({ careerProject: payload })
  })

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  return (
    <SectionCard title="Projet professionnel" footer={<SaveIndicator {...autoSave} />}>
      <FormGrid>
        <FormField label="Métier visé" id="targetJob" value={careerProject.targetJob ?? ''} onChange={(v) => patchCareer({ ...careerProject, targetJob: v })} />
        <FormField label="Secteur d'activité" id="sector" value={careerProject.sector ?? ''} onChange={(v) => patchCareer({ ...careerProject, sector: v })} />
        <FormField label="Objectifs" id="objectives" value={careerProject.objectives ?? ''} onChange={(v) => patchCareer({ ...careerProject, objectives: v })} className="sm:col-span-2" />
        <TextAreaField label="Description détaillée" id="description" value={careerProject.description ?? ''} onChange={(v) => patchCareer({ ...careerProject, description: v })} />
      </FormGrid>
    </SectionCard>
  )
}
