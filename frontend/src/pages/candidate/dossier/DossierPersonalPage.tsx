import {
  DossierError,
  DossierLoading,
  FormField,
  FormGrid,
  SaveFooter,
  SectionCard,
} from '@/components/candidate/dossier/DossierComponents'
import { useDossierSectionSave } from '@/hooks/useDossierSectionSave'
import type { MyProfile } from '@/lib/profile-api'

const EMPTY_PERSONAL: MyProfile['personal'] = {
  firstName: '',
  lastName: '',
  gender: null,
  dateOfBirth: null,
  placeOfBirth: null,
  nationality: '',
  maritalStatus: null,
  passportNumber: null,
  passportIssuedAt: null,
  passportExpiresAt: null,
  passportCountry: null,
  identityCardNumber: null,
}

export function DossierPersonalPage() {
  const { profile, data: personal, setData, isLoading, isError, autoSave, saveNow, isSaving } =
    useDossierSectionSave('personal', (value) => value.personal, EMPTY_PERSONAL)

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  const set = (key: keyof typeof personal, value: string) =>
    setData((prev) => ({ ...prev, [key]: value }))

  return (
    <SectionCard
      title="Informations personnelles"
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
        <FormField label="Nom" id="lastName" value={personal.lastName} onChange={(v) => set('lastName', v)} />
        <FormField label="Prénom" id="firstName" value={personal.firstName} onChange={(v) => set('firstName', v)} />
        <FormField label="Sexe" id="gender" value={personal.gender ?? ''} onChange={(v) => set('gender', v)} />
        <FormField
          label="Date de naissance"
          id="dateOfBirth"
          type="date"
          value={personal.dateOfBirth ?? ''}
          onChange={(v) => set('dateOfBirth', v)}
        />
        <FormField
          label="Lieu de naissance"
          id="placeOfBirth"
          value={personal.placeOfBirth ?? ''}
          onChange={(v) => set('placeOfBirth', v)}
        />
        <FormField
          label="Nationalité"
          id="nationality"
          value={personal.nationality}
          onChange={(v) => set('nationality', v)}
        />
        <FormField
          label="Situation matrimoniale"
          id="maritalStatus"
          value={personal.maritalStatus ?? ''}
          onChange={(v) => set('maritalStatus', v)}
        />
        <FormField
          label="N° passeport"
          id="passportNumber"
          value={personal.passportNumber ?? ''}
          onChange={(v) => set('passportNumber', v)}
        />
        <FormField
          label="Délivrance passeport"
          id="passportIssuedAt"
          type="date"
          value={personal.passportIssuedAt ?? ''}
          onChange={(v) => set('passportIssuedAt', v)}
        />
        <FormField
          label="Expiration passeport"
          id="passportExpiresAt"
          type="date"
          value={personal.passportExpiresAt ?? ''}
          onChange={(v) => set('passportExpiresAt', v)}
        />
        <FormField
          label="Pays délivrance"
          id="passportCountry"
          value={personal.passportCountry ?? ''}
          onChange={(v) => set('passportCountry', v)}
        />
        <FormField
          label="N° CNI"
          id="identityCardNumber"
          value={personal.identityCardNumber ?? ''}
          onChange={(v) => set('identityCardNumber', v)}
        />
      </FormGrid>
    </SectionCard>
  )
}
