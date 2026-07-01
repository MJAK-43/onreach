import { Button } from '@/components/ui/button'
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
import type { ProfessionalExperience } from '@/lib/profile-api'

const emptyExp = (): ProfessionalExperience => ({
  company: '',
  position: '',
  startDate: '',
  endDate: '',
  description: '',
})

export function DossierExperiencesPage() {
  const { profile, data: experiences, setData, isLoading, isError, autoSave, saveNow, isSaving } =
    useDossierSectionSave('experiences', (value) => value.experiences, [])

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  const updateExp = (index: number, patch: Partial<ProfessionalExperience>) => {
    setData((prev) => {
      const next = [...prev]
      next[index] = { ...next[index], ...patch }
      return next
    })
  }

  return (
    <SectionCard
      title="Expériences professionnelles"
      footer={
        <SaveFooter
          status={autoSave.status}
          error={autoSave.error}
          onSave={saveNow}
          isSaving={isSaving}
        />
      }
    >
      <div className="space-y-4">
        {experiences.map((exp, index) => (
          <div key={exp.id ?? index} className="rounded-lg border p-4 dark:border-slate-700">
            <FormGrid>
              <FormField label="Entreprise" id={`co-${index}`} value={exp.company} onChange={(v) => updateExp(index, { company: v })} />
              <FormField label="Poste" id={`pos-${index}`} value={exp.position} onChange={(v) => updateExp(index, { position: v })} />
              <FormField label="Date début" id={`start-${index}`} type="date" value={exp.startDate} onChange={(v) => updateExp(index, { startDate: v })} />
              <FormField label="Date fin" id={`end-${index}`} type="date" value={exp.endDate ?? ''} onChange={(v) => updateExp(index, { endDate: v })} />
              <TextAreaField label="Description" id={`desc-${index}`} value={exp.description ?? ''} onChange={(v) => updateExp(index, { description: v })} />
            </FormGrid>
          </div>
        ))}
        <Button type="button" size="sm" variant="outline" onClick={() => setData((prev) => [...prev, emptyExp()])}>
          Ajouter une expérience
        </Button>
      </div>
    </SectionCard>
  )
}
