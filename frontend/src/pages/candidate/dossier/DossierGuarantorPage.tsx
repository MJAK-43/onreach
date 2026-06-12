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
} from '@/components/candidate/dossier/DossierComponents'
import { useAutoSave } from '@/hooks/useAutoSave'
import { fetchMyProfile, type Guarantor, type MyProfile, updateMyProfile } from '@/lib/profile-api'

const EMPTY_FINANCING: NonNullable<MyProfile['financing']> = {
  type: 'guarantor',
  availableBudget: null,
  plannedAmount: null,
  description: null,
  guarantors: [],
}

const emptyGuarantor = (): Guarantor => ({
  firstName: '',
  lastName: '',
  fullName: '',
  profession: '',
  employer: '',
  phone: '',
  email: '',
  address: '',
  monthlyIncome: '',
})

export function DossierGuarantorPage() {
  const { data: profile, isLoading, isError } = useQuery({
    queryKey: ['my-profile'],
    queryFn: fetchMyProfile,
  })
  const [draft, setDraft] = useState<NonNullable<MyProfile['financing']> | null>(null)
  const financing = draft ?? profile?.financing ?? { ...EMPTY_FINANCING, guarantors: [emptyGuarantor()] }
  const patchFinancing = (next: NonNullable<MyProfile['financing']>) => setDraft(next)

  const autoSave = useAutoSave(financing, async (payload) => {
    await updateMyProfile({ financing: payload })
  })

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  const guarantors = financing.guarantors.length ? financing.guarantors : [emptyGuarantor()]

  const updateGuarantor = (index: number, patch: Partial<Guarantor>) => {
    const next = [...guarantors]
    next[index] = { ...next[index], ...patch }
    patchFinancing({ ...financing, guarantors: next })
  }

  return (
    <SectionCard title="Garant" footer={<SaveIndicator {...autoSave} />}>
      {guarantors.map((g, index) => (
        <div key={g.id ?? index} className="mb-4 rounded-lg border p-4 dark:border-slate-700">
          <FormGrid>
            <FormField label="Nom" id={`g-last-${index}`} value={g.lastName ?? ''} onChange={(v) => updateGuarantor(index, { lastName: v })} />
            <FormField label="Prénom" id={`g-first-${index}`} value={g.firstName ?? ''} onChange={(v) => updateGuarantor(index, { firstName: v })} />
            <FormField label="Profession" id={`g-prof-${index}`} value={g.profession ?? ''} onChange={(v) => updateGuarantor(index, { profession: v })} />
            <FormField label="Employeur" id={`g-emp-${index}`} value={g.employer ?? ''} onChange={(v) => updateGuarantor(index, { employer: v })} />
            <FormField label="Téléphone" id={`g-phone-${index}`} value={g.phone ?? ''} onChange={(v) => updateGuarantor(index, { phone: v })} />
            <FormField label="Email" id={`g-email-${index}`} value={g.email ?? ''} onChange={(v) => updateGuarantor(index, { email: v })} />
            <FormField label="Adresse" id={`g-addr-${index}`} value={g.address ?? ''} onChange={(v) => updateGuarantor(index, { address: v })} className="sm:col-span-2" />
            <FormField label="Revenus mensuels" id={`g-income-${index}`} value={g.monthlyIncome ?? ''} onChange={(v) => updateGuarantor(index, { monthlyIncome: v })} />
          </FormGrid>
        </div>
      ))}
      <Button type="button" size="sm" variant="outline" onClick={() => patchFinancing({ ...financing, guarantors: [...guarantors, emptyGuarantor()] })}>
        Ajouter un garant
      </Button>
    </SectionCard>
  )
}
