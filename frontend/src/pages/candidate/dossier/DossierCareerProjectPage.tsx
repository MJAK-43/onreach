import {
  DossierError,
  DossierLoading,
  FormField,
  FormGrid,
  SaveFooter,
  SectionCard,
  TextAreaField,
} from '@/components/candidate/dossier/DossierComponents'
import { useDossierSectionSave } from '@/hooks/useDossierSectionSave'
import type { MyProfile } from '@/lib/profile-api'

const EMPTY_CAREER: MyProfile['careerProject'] = {
  targetJob: null,
  objectives: null,
  sector: null,
  description: null,
}

export function DossierCareerProjectPage() {
  const { profile, data: careerProject, setData, isLoading, isError, autoSave, saveNow, isSaving } =
    useDossierSectionSave('careerProject', (value) => value.careerProject, EMPTY_CAREER)

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  return (
    <SectionCard
      title="Projet professionnel"
      footer={
        <SaveFooter
          status={autoSave.status}
          error={autoSave.error}
          onSave={saveNow}
          isSaving={isSaving}
        />
      }
    >
      <FormGrid>
        <FormField label="Métier visé" id="targetJob" value={careerProject.targetJob ?? ''} onChange={(v) => setData((prev) => ({ ...prev, targetJob: v }))} />
        <FormField label="Secteur d'activité" id="sector" value={careerProject.sector ?? ''} onChange={(v) => setData((prev) => ({ ...prev, sector: v }))} />
        <FormField label="Objectifs" id="objectives" value={careerProject.objectives ?? ''} onChange={(v) => setData((prev) => ({ ...prev, objectives: v }))} className="sm:col-span-2" />
        <TextAreaField label="Description détaillée" id="description" value={careerProject.description ?? ''} onChange={(v) => setData((prev) => ({ ...prev, description: v }))} />
      </FormGrid>
    </SectionCard>
  )
}
