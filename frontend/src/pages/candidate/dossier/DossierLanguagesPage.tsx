import { Button } from '@/components/ui/button'
import {
  DossierError,
  DossierLoading,
  FormField,
  FormGrid,
  SaveFooter,
  SectionCard,
} from '@/components/candidate/dossier/DossierComponents'
import { useDossierSectionSave } from '@/hooks/useDossierSectionSave'
import { type LanguageCertificate, type MyProfile } from '@/lib/profile-api'

const EMPTY_LANGUAGES: NonNullable<MyProfile['languages']> = {
  frenchLevel: null,
  englishLevel: null,
  otherLanguages: [],
  certificates: [],
}

export function DossierLanguagesPage() {
  const { profile, data: languages, setData: patchLanguages, isLoading, isError, autoSave, saveNow, isSaving } =
    useDossierSectionSave(
      'languages',
      (value) => value.languages ?? EMPTY_LANGUAGES,
      EMPTY_LANGUAGES,
    )

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  const addCert = () => {
    const cert: LanguageCertificate = { type: 'tcf', score: '', issueDate: '', expirationDate: '' }
    patchLanguages((prev) => ({ ...prev, certificates: [...prev.certificates, cert] }))
  }

  return (
    <SectionCard
      title="Langues"
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
        <FormField label="Français" id="frenchLevel" value={languages.frenchLevel ?? ''} onChange={(v) => patchLanguages({ ...languages, frenchLevel: v })} />
        <FormField label="Anglais" id="englishLevel" value={languages.englishLevel ?? ''} onChange={(v) => patchLanguages({ ...languages, englishLevel: v })} />
        <FormField
          label="Autres langues (séparées par des virgules)"
          id="otherLanguages"
          value={(languages.otherLanguages ?? []).join(', ')}
          onChange={(v) => patchLanguages({ ...languages, otherLanguages: v.split(',').map((s) => s.trim()).filter(Boolean) })}
          className="sm:col-span-2"
        />
      </FormGrid>

      <div className="mt-6 space-y-4">
        <div className="flex items-center justify-between">
          <h3 className="text-sm font-semibold">Certificats</h3>
          <Button type="button" size="sm" variant="outline" onClick={addCert}>
            Ajouter un certificat
          </Button>
        </div>
        {languages.certificates.map((cert, index) => (
          <div key={cert.id ?? index} className="rounded-lg border p-4 dark:border-slate-700">
            <FormGrid>
              <FormField label="Type" id={`cert-type-${index}`} value={cert.type} onChange={(v) => {
                const certificates = [...languages.certificates]
                certificates[index] = { ...cert, type: v }
                patchLanguages({ ...languages, certificates })
              }} />
              <FormField label="Score" id={`cert-score-${index}`} value={cert.score ?? ''} onChange={(v) => {
                const certificates = [...languages.certificates]
                certificates[index] = { ...cert, score: v }
                patchLanguages({ ...languages, certificates })
              }} />
              <FormField label="Date obtention" id={`cert-issue-${index}`} type="date" value={cert.issueDate ?? ''} onChange={(v) => {
                const certificates = [...languages.certificates]
                certificates[index] = { ...cert, issueDate: v }
                patchLanguages({ ...languages, certificates })
              }} />
              <FormField label="Expiration" id={`cert-exp-${index}`} type="date" value={cert.expirationDate ?? ''} onChange={(v) => {
                const certificates = [...languages.certificates]
                certificates[index] = { ...cert, expirationDate: v }
                patchLanguages({ ...languages, certificates })
              }} />
            </FormGrid>
          </div>
        ))}
      </div>
    </SectionCard>
  )
}
