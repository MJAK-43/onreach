import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { Button } from '@/components/ui/button'
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
import { fetchMyProfile, type AcademicRecord, type MyProfile, updateMyProfile } from '@/lib/profile-api'

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
  const { data: profile, isLoading, isError } = useQuery({
    queryKey: ['my-profile'],
    queryFn: fetchMyProfile,
  })
  const [draft, setDraft] = useState<NonNullable<MyProfile['academic']> | null>(null)
  const academic = draft ?? profile?.academic ?? EMPTY_ACADEMIC

  const autoSave = useAutoSave(academic, async (payload) => {
    await updateMyProfile({ academic: payload })
  })

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  const patchAcademic = (next: NonNullable<MyProfile['academic']>) => setDraft(next)

  const updateRecord = (index: number, patch: Partial<AcademicRecord>) => {
    const base = draft ?? profile.academic ?? EMPTY_ACADEMIC
    const records = [...base.records]
    records[index] = { ...records[index], ...patch }
    patchAcademic({ ...base, records })
  }

  return (
    <div className="space-y-6">
      <SectionCard title="Parcours académique" footer={<SaveIndicator {...autoSave} />}>
        <FormGrid>
          <FormField label="Diplôme le plus élevé" id="highestDiploma" value={academic.highestDiploma ?? ''} onChange={(v) => patchAcademic({ ...academic, highestDiploma: v })} />
          <FormField label="Établissement" id="institutionName" value={academic.institutionName ?? ''} onChange={(v) => patchAcademic({ ...academic, institutionName: v })} />
          <FormField label="Année" id="graduationYear" type="number" value={String(academic.graduationYear ?? '')} onChange={(v) => patchAcademic({ ...academic, graduationYear: v ? Number(v) : null })} />
          <FormField label="Moyenne" id="overallAverage" value={academic.overallAverage ?? ''} onChange={(v) => patchAcademic({ ...academic, overallAverage: v })} />
        </FormGrid>

        <div className="mt-6 space-y-4">
          <div className="flex items-center justify-between">
            <h3 className="text-sm font-semibold">Diplômes</h3>
            <Button type="button" size="sm" variant="outline" onClick={() => patchAcademic({ ...academic, records: [...academic.records, emptyRecord()] })}>
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
