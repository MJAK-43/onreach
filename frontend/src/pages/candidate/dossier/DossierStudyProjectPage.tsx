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

const EMPTY_STUDY: MyProfile['studyProject'] = {
  domain: null,
  specialty: null,
  level: null,
  targetCountry: null,
  universities: [],
  description: null,
}

export function DossierStudyProjectPage() {
  const { profile, data: studyProject, setData, isLoading, isError, autoSave, saveNow, isSaving } =
    useDossierSectionSave('studyProject', (value) => value.studyProject, EMPTY_STUDY)

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  return (
    <SectionCard
      title="Projet d'études"
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
        <FormField label="Domaine souhaité" id="domain" value={studyProject.domain ?? ''} onChange={(v) => setData((prev) => ({ ...prev, domain: v }))} />
        <FormField label="Spécialité" id="specialty" value={studyProject.specialty ?? ''} onChange={(v) => setData((prev) => ({ ...prev, specialty: v }))} />
        <FormField label="Niveau souhaité" id="level" value={studyProject.level ?? ''} onChange={(v) => setData((prev) => ({ ...prev, level: v }))} />
        <FormField label="Pays visé" id="targetCountry" value={studyProject.targetCountry ?? ''} onChange={(v) => setData((prev) => ({ ...prev, targetCountry: v }))} />
        <FormField
          label="Universités (séparées par des virgules)"
          id="universities"
          value={(studyProject.universities ?? []).join(', ')}
          onChange={(v) => setData((prev) => ({ ...prev, universities: v.split(',').map((s) => s.trim()).filter(Boolean) }))}
          className="sm:col-span-2"
        />
        <TextAreaField label="Description" id="description" value={studyProject.description ?? ''} onChange={(v) => setData((prev) => ({ ...prev, description: v }))} />
      </FormGrid>
    </SectionCard>
  )
}
