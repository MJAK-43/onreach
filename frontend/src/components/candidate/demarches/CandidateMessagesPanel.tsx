import { useEffect, useState } from 'react'
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  fetchConversationThread,
  fetchMyConversations,
  openCandidateConversation,
  sendConversationMessage,
  type ConversationMessage,
} from '@/lib/conversation-api'

function MessageBubble({ message, currentUserId }: { message: ConversationMessage; currentUserId?: string }) {
  const isMine = message.author.id === currentUserId

  return (
    <div className={isMine ? 'flex justify-end' : 'flex justify-start'}>
      <div
        className={
          isMine
            ? 'max-w-[85%] rounded-2xl bg-primary px-4 py-2 text-sm text-primary-foreground'
            : 'max-w-[85%] rounded-2xl bg-muted px-4 py-2 text-sm'
        }
      >
        <p>{message.body}</p>
        <p className="mt-1 text-[10px] opacity-70">
          {new Date(message.createdAt).toLocaleString('fr-FR')}
        </p>
      </div>
    </div>
  )
}

export function CandidateMessagesPanel({ currentUserId }: { currentUserId?: string }) {
  const queryClient = useQueryClient()
  const [draft, setDraft] = useState('')
  const conversationsQuery = useQuery({
    queryKey: ['me-conversations'],
    queryFn: fetchMyConversations,
  })

  const conversationId = conversationsQuery.data?.items[0]?.id ?? null

  const threadQuery = useQuery({
    queryKey: ['me-conversation-thread', conversationId],
    queryFn: () => fetchConversationThread(conversationId!),
    enabled: Boolean(conversationId),
  })

  const sendMutation = useMutation({
    mutationFn: async (body: string) => {
      if (conversationId) {
        return sendConversationMessage(conversationId, body)
      }
      return openCandidateConversation(body)
    },
    onSuccess: () => {
      setDraft('')
      void queryClient.invalidateQueries({ queryKey: ['me-conversations'] })
      void queryClient.invalidateQueries({ queryKey: ['me-conversation-thread'] })
    },
  })

  useEffect(() => {
    if (conversationId) {
      void queryClient.invalidateQueries({ queryKey: ['me-conversation-thread', conversationId] })
    }
  }, [conversationId, queryClient])

  const messages = threadQuery.data?.messages ?? []

  return (
    <div className="space-y-4">
      {conversationsQuery.isLoading && <p className="text-sm text-muted-foreground">Chargement…</p>}
      {conversationsQuery.isError && (
        <p className="text-sm text-red-600">Impossible de charger la messagerie.</p>
      )}

      <div className="max-h-96 space-y-3 overflow-y-auto rounded-xl border border-border p-4">
        {messages.length === 0 ? (
          <p className="text-sm text-muted-foreground">
            Aucun message pour le moment. Écrivez à votre conseiller ci-dessous.
          </p>
        ) : (
          messages.map((message) => (
            <MessageBubble key={message.id} message={message} currentUserId={currentUserId} />
          ))
        )}
      </div>

      <form
        className="flex gap-2"
        onSubmit={(event) => {
          event.preventDefault()
          const body = draft.trim()
          if (!body) return
          sendMutation.mutate(body)
        }}
      >
        <Input
          value={draft}
          onChange={(event) => setDraft(event.target.value)}
          placeholder="Votre message…"
          disabled={sendMutation.isPending}
        />
        <Button type="submit" disabled={sendMutation.isPending || draft.trim() === ''}>
          Envoyer
        </Button>
      </form>
    </div>
  )
}
