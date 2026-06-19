import { useEffect, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { Bell, CheckCheck } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { useDeferredNotifications, useMarkAllNotificationsRead, useMarkNotificationRead } from '@/hooks/useNotifications'
import { cn } from '@/lib/utils'

export function NotificationBell() {
  const [open, setOpen] = useState(false)
  const panelRef = useRef<HTMLDivElement>(null)
  const { data, isLoading } = useDeferredNotifications()
  const markRead = useMarkNotificationRead()
  const markAllRead = useMarkAllNotificationsRead()
  const unreadCount = data?.unreadCount ?? 0

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (panelRef.current && !panelRef.current.contains(event.target as Node)) {
        setOpen(false)
      }
    }
    document.addEventListener('mousedown', handleClickOutside)
    return () => document.removeEventListener('mousedown', handleClickOutside)
  }, [])

  return (
    <div className="relative" ref={panelRef}>
      <button
        type="button"
        onClick={() => setOpen((value) => !value)}
        className="relative rounded-lg p-2 text-slate-500 hover:bg-slate-100"
        aria-label="Notifications"
      >
        <Bell className="h-5 w-5" />
        {unreadCount > 0 && (
          <span className="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
            {unreadCount > 9 ? '9+' : unreadCount}
          </span>
        )}
      </button>

      {open && (
        <div className="absolute right-0 z-50 mt-2 w-80 rounded-xl border border-slate-200 bg-white shadow-lg sm:w-96">
          <div className="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <p className="text-sm font-semibold text-slate-900">Notifications</p>
            {unreadCount > 0 && (
              <Button
                type="button"
                variant="ghost"
                size="sm"
                className="h-8 gap-1 text-xs"
                disabled={markAllRead.isPending}
                onClick={() => markAllRead.mutate()}
              >
                <CheckCheck className="h-3.5 w-3.5" />
                Tout lire
              </Button>
            )}
          </div>

          <div className="max-h-96 overflow-y-auto">
            {isLoading && <p className="px-4 py-6 text-sm text-muted-foreground">Chargement...</p>}
            {!isLoading && (data?.items.length ?? 0) === 0 && (
              <p className="px-4 py-6 text-sm text-muted-foreground">Aucune notification.</p>
            )}
            {data?.items.map((item) => (
              <div
                key={item.id}
                className={cn(
                  'border-b border-slate-50 px-4 py-3 last:border-b-0',
                  !item.read && 'bg-blue-50/40',
                )}
              >
                <div className="flex items-start justify-between gap-2">
                  <div className="min-w-0">
                    <p className="text-sm font-medium text-slate-900">{item.title}</p>
                    <p className="mt-0.5 text-xs text-slate-600">{item.message}</p>
                    <p className="mt-1 text-[10px] text-slate-400">
                      {new Date(item.createdAt).toLocaleString('fr-FR')}
                    </p>
                  </div>
                  {!item.read && (
                    <button
                      type="button"
                      className="shrink-0 text-[10px] font-medium text-blue-600 hover:text-blue-700"
                      onClick={() => markRead.mutate(item.id)}
                    >
                      Lu
                    </button>
                  )}
                </div>
                {item.linkUrl && (
                  <Link
                    to={item.linkUrl.replace(/^https?:\/\/[^/]+/, '') || '/'}
                    className="mt-2 inline-block text-xs font-medium text-blue-600 hover:text-blue-700"
                    onClick={() => {
                      if (!item.read) markRead.mutate(item.id)
                      setOpen(false)
                    }}
                  >
                    Voir →
                  </Link>
                )}
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  )
}
