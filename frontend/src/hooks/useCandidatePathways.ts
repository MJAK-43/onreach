import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  fetchCandidatePathways,
  patchCandidatePathway,
  patchCandidatePathwaySubStep,
  type MyPathwaysResponse,
  type PatchPathwayPayload,
  type PatchSubStepPayload,
} from '@/lib/pathways-api'

export function useCandidatePathways(candidateId: string, enabled = true) {
  return useQuery({
    queryKey: ['candidate-pathways', candidateId],
    queryFn: () => fetchCandidatePathways(candidateId),
    enabled: Boolean(candidateId) && enabled,
  })
}

export function usePatchCandidatePathwaySubStep(candidateId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({
      pathwayId,
      subStepId,
      payload,
    }: {
      pathwayId: string
      subStepId: string
      payload: PatchSubStepPayload
    }) => patchCandidatePathwaySubStep(candidateId, pathwayId, subStepId, payload),
    onSuccess: (data) => {
      queryClient.setQueryData<MyPathwaysResponse>(
        ['candidate-pathways', candidateId],
        (previous) => {
          if (!previous) return previous
          return {
            ...previous,
            pathways: previous.pathways.map((pathway) =>
              pathway.id === data.pathway.id ? data.pathway : pathway,
            ),
          }
        },
      )
      void queryClient.invalidateQueries({ queryKey: ['candidate-timeline', candidateId] })
      void queryClient.invalidateQueries({ queryKey: ['candidate-completion', candidateId] })
      void queryClient.invalidateQueries({ queryKey: ['candidate-pathway-audit', candidateId] })
      void queryClient.invalidateQueries({ queryKey: ['me-notifications'] })
      void queryClient.invalidateQueries({ queryKey: ['pathway-tracking'] })
    },
  })
}

export function usePatchCandidatePathway(candidateId: string) {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({
      pathwayId,
      payload,
    }: {
      pathwayId: string
      payload: PatchPathwayPayload
    }) => patchCandidatePathway(candidateId, pathwayId, payload),
    onSuccess: (data) => {
      queryClient.setQueryData<MyPathwaysResponse>(
        ['candidate-pathways', candidateId],
        (previous) => {
          if (!previous) return previous
          return {
            ...previous,
            pathways: previous.pathways.map((pathway) =>
              pathway.id === data.pathway.id ? data.pathway : pathway,
            ),
          }
        },
      )
      void queryClient.invalidateQueries({ queryKey: ['candidate-pathway-audit', candidateId] })
      void queryClient.invalidateQueries({ queryKey: ['pathway-tracking'] })
      void queryClient.invalidateQueries({ queryKey: ['me-notifications'] })
    },
  })
}
