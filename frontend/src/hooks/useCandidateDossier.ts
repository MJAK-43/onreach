import { useMyDashboard } from '@/hooks/useMyDashboard'

export function useCandidateDossier() {
  const query = useMyDashboard()

  return {
    ...query,
    dossier: query.data
      ? {
          id: query.data.id,
          status: query.data.status,
        }
      : undefined,
  }
}
