import { useEffect, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import {
  fetchMyNotifications,
  markAllNotificationsRead,
  markNotificationRead,
} from '@/lib/notifications-api'

export function useNotifications(enabled = true) {
  return useQuery({
    queryKey: ['me-notifications'],
    queryFn: fetchMyNotifications,
    enabled,
    staleTime: 60_000,
    refetchInterval: enabled ? 60_000 : false,
  })
}

export function useDeferredNotifications() {
  const [enabled, setEnabled] = useState(false)

  useEffect(() => {
    const timeoutId = window.setTimeout(() => setEnabled(true), 300)
    return () => window.clearTimeout(timeoutId)
  }, [])

  return useNotifications(enabled)
}

export function useMarkNotificationRead() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: markNotificationRead,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['me-notifications'] })
    },
  })
}

export function useMarkAllNotificationsRead() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: markAllNotificationsRead,
    onSuccess: () => {
      void queryClient.invalidateQueries({ queryKey: ['me-notifications'] })
    },
  })
}
