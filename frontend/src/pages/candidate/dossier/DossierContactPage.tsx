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

const EMPTY_CONTACT: MyProfile['contact'] = {
  address: null,
  city: null,
  region: null,
  postalCode: null,
  country: null,
  phone: null,
  whatsapp: null,
  email: '',
  secondaryEmail: null,
}

export function DossierContactPage() {
  const { profile, data: contact, setData, isLoading, isError, autoSave, saveNow, isSaving } =
    useDossierSectionSave('contact', (value) => value.contact, EMPTY_CONTACT)

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  const set = (key: keyof typeof contact, value: string) =>
    setData((prev) => ({ ...prev, [key]: value }))

  return (
    <SectionCard
      title="Coordonnées"
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
        <FormField label="Adresse" id="address" value={contact.address ?? ''} onChange={(v) => set('address', v)} className="sm:col-span-2" />
        <FormField label="Ville" id="city" value={contact.city ?? ''} onChange={(v) => set('city', v)} />
        <FormField label="Région" id="region" value={contact.region ?? ''} onChange={(v) => set('region', v)} />
        <FormField label="Code postal" id="postalCode" value={contact.postalCode ?? ''} onChange={(v) => set('postalCode', v)} />
        <FormField label="Pays" id="country" value={contact.country ?? ''} onChange={(v) => set('country', v)} />
        <FormField label="Téléphone" id="phone" value={contact.phone ?? ''} onChange={(v) => set('phone', v)} />
        <FormField label="WhatsApp" id="whatsapp" value={contact.whatsapp ?? ''} onChange={(v) => set('whatsapp', v)} />
        <FormField label="Email principal" id="email" value={contact.email} onChange={(v) => set('email', v)} disabled />
        <FormField label="Email secondaire" id="secondaryEmail" value={contact.secondaryEmail ?? ''} onChange={(v) => set('secondaryEmail', v)} />
      </FormGrid>
    </SectionCard>
  )
}
