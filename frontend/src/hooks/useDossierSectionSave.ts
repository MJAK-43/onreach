import { useCallback, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { useAutoSave } from '@/hooks/useAutoSave'
import { fetchMyProfile, type MyProfile, updateMyProfile } from '@/lib/profile-api'

type DossierSectionKey = keyof Pick<
  MyProfile,
  'personal' | 'contact' | 'studyProject' | 'careerProject' | 'academic' | 'languages' | 'financing' | 'experiences'
>

export function useDossierSectionSave<T>(
  sectionKey: DossierSectionKey,
  selector: (profile: MyProfile) => T,
  empty: T,
) {
  const queryClient = useQueryClient()
  const query = useQuery({
    queryKey: ['my-profile'],
    queryFn: fetchMyProfile,
  })

  const [draft, setDraft] = useState<T | null>(null)
  const serverValue = query.data ? selector(query.data) : empty
  const data = draft ?? serverValue

  const mutation = useMutation({
    mutationFn: (payload: Record<string, unknown>) => updateMyProfile(payload),
    onSuccess: (saved) => {
      queryClient.setQueryData(['my-profile'], saved)
      setDraft(null)
    },
  })

  const save = useCallback(
    async (payload: T) => {
      await mutation.mutateAsync({ [sectionKey]: payload })
    },
    [mutation, sectionKey],
  )

  const autoSave = useAutoSave(data, save, 2000)

  const setData = useCallback(
    (updater: T | ((prev: T) => T)) => {
      setDraft((prev) => {
        const base = prev ?? serverValue
        return typeof updater === 'function' ? (updater as (value: T) => T)(base) : updater
      })
    },
    [serverValue],
  )

  const saveNow = useCallback(() => {
    void autoSave.flush()
  }, [autoSave])

  return {
    profile: query.data,
    data,
    setData,
    isLoading: query.isLoading,
    isError: query.isError,
    autoSave,
    saveNow,
    isSaving: mutation.isPending,
  }
}
