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
import { type AcademicRecord, type MyProfile } from '@/lib/profile-api'

const EMPTY_ACADEMIC: NonNullable<MyProfile['academic']> = {
  highestDiploma: null,
  institutionName: null,
  graduationYear: null,
  overallAverage: null,
  ranking: null,
  specialty: null,
  academicAchievements: null,
  records: [],
}

const emptyRecord = (): AcademicRecord => ({
  diplomaType: 'licence',
  diploma: '',
  institution: '',
  country: '',
  year: new Date().getFullYear(),
  mention: '',
  average: '',
  ranking: '',
  description: '',
})

export function DossierAcademicPage() {
  const { profile, data: academic, setData: patchAcademic, isLoading, isError, autoSave, saveNow, isSaving } =
    useDossierSectionSave(
      'academic',
      (value) => value.academic ?? EMPTY_ACADEMIC,
      EMPTY_ACADEMIC,
    )

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  const updateRecord = (index: number, patch: Partial<AcademicRecord>) => {
    patchAcademic((base) => {
      const records = [...base.records]
      records[index] = { ...records[index], ...patch }
      return { ...base, records }
    })
  }

  return (
    <div className="space-y-6">
      <SectionCard
        title="Parcours académique"
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
          <FormField label="Diplôme le plus élevé" id="highestDiploma" value={academic.highestDiploma ?? ''} onChange={(v) => patchAcademic((prev) => ({ ...prev, highestDiploma: v }))} />
          <FormField label="Établissement" id="institutionName" value={academic.institutionName ?? ''} onChange={(v) => patchAcademic((prev) => ({ ...prev, institutionName: v }))} />
          <FormField label="Année" id="graduationYear" type="number" value={String(academic.graduationYear ?? '')} onChange={(v) => patchAcademic((prev) => ({ ...prev, graduationYear: v ? Number(v) : null }))} />
          <FormField label="Moyenne" id="overallAverage" value={academic.overallAverage ?? ''} onChange={(v) => patchAcademic((prev) => ({ ...prev, overallAverage: v }))} />
        </FormGrid>

        <div className="mt-6 space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="text-sm font-semibold">Diplômes</h3>
            <Button type="button" size="sm" variant="outline" onClick={() => patchAcademic((prev) => ({ ...prev, records: [...prev.records, emptyRecord()] }))}>
              Ajouter un diplôme
            </Button>
          </div>
          {academic.records.map((record, index) => (
            <div key={record.id ?? index} className="rounded-lg border p-4 dark:border-slate-700">
              <FormGrid>
                <FormField label="Type" id={`type-${index}`} value={record.diplomaType ?? ''} onChange={(v) => updateRecord(index, { diplomaType: v })} />
                <FormField label="Nom diplôme" id={`diploma-${index}`} value={record.diploma} onChange={(v) => updateRecord(index, { diploma: v })} />
                <FormField label="Établissement" id={`inst-${index}`} value={record.institution} onChange={(v) => updateRecord(index, { institution: v })} />
                <FormField label="Pays" id={`country-${index}`} value={record.country ?? ''} onChange={(v) => updateRecord(index, { country: v })} />
                <FormField label="Année" id={`year-${index}`} type="number" value={String(record.year)} onChange={(v) => updateRecord(index, { year: Number(v) })} />
                <FormField label="Mention" id={`mention-${index}`} value={record.mention ?? ''} onChange={(v) => updateRecord(index, { mention: v })} />
                <FormField label="Moyenne" id={`avg-${index}`} value={record.average ?? ''} onChange={(v) => updateRecord(index, { average: v })} />
                <TextAreaField label="Description" id={`desc-${index}`} value={record.description ?? ''} onChange={(v) => updateRecord(index, { description: v })} />
              </FormGrid>
            </div>
          ))}
        </div>
      </SectionCard>
    </div>
  )
}
