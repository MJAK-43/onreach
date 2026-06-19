import { apiRequest } from '@/lib/api'

export interface ConversationParticipant {
  id: string
  firstName: string
  lastName: string
  email: string
}

export interface ConversationSummary {
  id: string
  candidate: ConversationParticipant
  counselor: ConversationParticipant
  unreadCount: number
  lastMessagePreview: string | null
  updatedAt: string
}

export interface ConversationMessage {
  id: string
  body: string
  author: ConversationParticipant
  readAt: string | null
  createdAt: string
}

const jsonHeaders = { Accept: 'application/json' } as const

export async function fetchMyConversations(): Promise<{ items: ConversationSummary[] }> {
  return apiRequest('/api/me/conversations', { headers: jsonHeaders })
}

export async function fetchConversationThread(
  conversationId: string,
): Promise<{ conversation: ConversationSummary; messages: ConversationMessage[] }> {
  return apiRequest(`/api/me/conversations/${conversationId}`, { headers: jsonHeaders })
}

export async function sendConversationMessage(
  conversationId: string,
  body: string,
): Promise<{ conversation: ConversationSummary; message: ConversationMessage }> {
  return apiRequest(`/api/me/conversations/${conversationId}/messages`, {
    method: 'POST',
    headers: jsonHeaders,
    body: JSON.stringify({ body }),
  })
}

export async function openCandidateConversation(
  body: string,
): Promise<{ conversation: ConversationSummary; message: ConversationMessage }> {
  return apiRequest('/api/me/conversations/open', {
    method: 'POST',
    headers: jsonHeaders,
    body: JSON.stringify({ body }),
  })
}

export async function sendStaffConversationMessage(
  candidateId: string,
  body: string,
): Promise<{ conversation: ConversationSummary; message: ConversationMessage }> {
  return apiRequest(`/api/candidates/${candidateId}/conversation/messages`, {
    method: 'POST',
    headers: jsonHeaders,
    body: JSON.stringify({ body }),
  })
}
