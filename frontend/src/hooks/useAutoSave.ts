import { useCallback, useEffect, useRef, useState } from 'react'

export type AutoSaveStatus = 'idle' | 'pending' | 'saving' | 'saved' | 'error'

export function useAutoSave<T>(
  data: T,
  save: (data: T) => Promise<unknown>,
  delayMs = 5000,
) {
  const [status, setStatus] = useState<AutoSaveStatus>('idle')
  const [error, setError] = useState<string | null>(null)
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null)
  const latestRef = useRef(data)
  const initialRef = useRef(JSON.stringify(data))
  const savingRef = useRef(false)

  useEffect(() => {
    latestRef.current = data
  }, [data])

  const flush = useCallback(async () => {
    const serialized = JSON.stringify(latestRef.current)
    if (serialized === initialRef.current || savingRef.current) {
      return
    }
    savingRef.current = true
    setStatus('saving')
    setError(null)
    try {
      await save(latestRef.current)
      initialRef.current = serialized
      setStatus('saved')
    } catch (e) {
      setStatus('error')
      setError(e instanceof Error ? e.message : 'Erreur de sauvegarde')
    } finally {
      savingRef.current = false
    }
  }, [save])

  useEffect(() => {
    const serialized = JSON.stringify(data)
    if (serialized === initialRef.current) {
      return
    }
    setStatus('pending')
    if (timerRef.current) {
      clearTimeout(timerRef.current)
    }
    timerRef.current = setTimeout(() => {
      void flush()
    }, delayMs)

    return () => {
      if (timerRef.current) {
        clearTimeout(timerRef.current)
      }
    }
  }, [data, delayMs, flush])

  return { status, error, flush }
}
