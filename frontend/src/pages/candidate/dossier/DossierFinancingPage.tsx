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

const EMPTY_FINANCING: NonNullable<MyProfile['financing']> = {
  type: 'self_funded',
  availableBudget: null,
  plannedAmount: null,
  description: null,
  guarantors: [],
}

const FINANCING_TYPES = [
  { value: 'self_funded', label: 'Personnel' },
  { value: 'parent', label: 'Parent' },
  { value: 'sponsor', label: 'Garant' },
  { value: 'scholarship', label: 'Bourse' },
  { value: 'company', label: 'Entreprise' },
]

export function DossierFinancingPage() {
  const { data: profile, isLoading, isError } = useQuery({
    queryKey: ['my-profile'],
    queryFn: fetchMyProfile,
  })
  const [draft, setDraft] = useState<NonNullable<MyProfile['financing']> | null>(null)
  const financing = draft ?? profile?.financing ?? EMPTY_FINANCING
  const patchFinancing = (next: NonNullable<MyProfile['financing']>) => setDraft(next)

  const autoSave = useAutoSave(financing, async (payload) => {
    await updateMyProfile({ financing: payload })
  })

  if (isLoading || !profile) {
    return isError ? <DossierError /> : <DossierLoading />
  }

  return (
    <SectionCard title="Financement" footer={<SaveIndicator {...autoSave} />}>
      <FormGrid>
        <div className="space-y-2 sm:col-span-2">
          <label htmlFor="fin-type" className="text-sm font-medium">
            Type de financement
          </label>
          <select
            id="fin-type"
            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
            value={financing.type}
            onChange={(e) => patchFinancing({ ...financing, type: e.target.value })}
          >
            {FINANCING_TYPES.map((t) => (
              <option key={t.value} value={t.value}>
                {t.label}
              </option>
            ))}
          </select>
        </div>
        <FormField label="Budget disponible" id="availableBudget" value={financing.availableBudget ?? ''} onChange={(v) => patchFinancing({ ...financing, availableBudget: v })} />
        <FormField label="Montant prévu" id="plannedAmount" value={financing.plannedAmount ?? ''} onChange={(v) => patchFinancing({ ...financing, plannedAmount: v })} />
        <TextAreaField label="Description" id="description" value={financing.description ?? ''} onChange={(v) => patchFinancing({ ...financing, description: v })} />
      </FormGrid>
    </SectionCard>
  )
}
