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

const EMPTY_STUDY: MyProfile['studyProject'] = {
  domain: null,
  specialty: null,
  level: null,
  targetCountry: null,
  universities: [],
  description: null,
}

export function DossierStudyProjectPage() {
  const { data: profile, isLoading, isError } = useQuery({
    queryKey: ['my-profile'],
    queryFn: fetchMyProfile,
  })
  const [draft, setDraft] = useState<MyProfile['studyProject'] | null>(null)
  const studyProject = draft ?? profile?.studyProject ?? EMPTY_STUDY
  const patchStudy = (next: MyProfile['studyProject']) => setDraft(next)

  const autoSave = useAutoSave(studyProject, async (payload) => {
    await updateMyProfile({ studyProject: payload })
  })

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  return (
    <SectionCard title="Projet d'études" footer={<SaveIndicator {...autoSave} />}>
      <FormGrid>
        <FormField label="Domaine souhaité" id="domain" value={studyProject.domain ?? ''} onChange={(v) => patchStudy({ ...studyProject, domain: v })} />
        <FormField label="Spécialité" id="specialty" value={studyProject.specialty ?? ''} onChange={(v) => patchStudy({ ...studyProject, specialty: v })} />
        <FormField label="Niveau souhaité" id="level" value={studyProject.level ?? ''} onChange={(v) => patchStudy({ ...studyProject, level: v })} />
        <FormField label="Pays visé" id="targetCountry" value={studyProject.targetCountry ?? ''} onChange={(v) => patchStudy({ ...studyProject, targetCountry: v })} />
        <FormField
          label="Universités (séparées par des virgules)"
          id="universities"
          value={(studyProject.universities ?? []).join(', ')}
          onChange={(v) => patchStudy({ ...studyProject, universities: v.split(',').map((s) => s.trim()).filter(Boolean) })}
          className="sm:col-span-2"
        />
        <TextAreaField label="Description" id="description" value={studyProject.description ?? ''} onChange={(v) => patchStudy({ ...studyProject, description: v })} />
      </FormGrid>
    </SectionCard>
  )
}
