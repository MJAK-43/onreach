import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import {
  DossierError,
  DossierLoading,
  FormField,
  FormGrid,
  SaveIndicator,
  SectionCard,
} from '@/components/candidate/dossier/DossierComponents'
import { useAutoSave } from '@/hooks/useAutoSave'
import { fetchMyProfile, type MyProfile, updateMyProfile } from '@/lib/profile-api'

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
  const { data: profile, isLoading, isError } = useQuery({
    queryKey: ['my-profile'],
    queryFn: fetchMyProfile,
  })
  const [draft, setDraft] = useState<MyProfile['contact'] | null>(null)
  const contact = draft ?? profile?.contact ?? EMPTY_CONTACT

  const autoSave = useAutoSave(contact, async (payload) => {
    await updateMyProfile({ contact: payload })
  })

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  const set = (key: keyof typeof contact, value: string) =>
    setDraft((prev) => ({ ...(prev ?? profile?.contact ?? EMPTY_CONTACT), [key]: value }))

  return (
    <SectionCard title="Coordonnées" footer={<SaveIndicator {...autoSave} />}>
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
