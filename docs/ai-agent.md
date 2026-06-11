# Agent IA — Architecture

## Service séparé

Le service IA (`ai-service/`) est un microservice FastAPI indépendant du backend Symfony.

## Structure

```
ai-service/
├── agents/          # Agent Orchestrator
├── tools/           # Tool Registry
├── prompts/         # Prompts système
├── rag/             # RAG Engine (pgvector)
└── adapters/        # OpenAI Adapter
```

## Composants (Sprint 0 — squelettes)

### Agent Orchestrator
Coordonne les agents spécialisés (Campus France, Visa, Parcoursup).

### Tool Registry
Enregistre et expose les outils disponibles pour les agents.

### RAG Engine
Recherche augmentée sur la base documentaire (pgvector).

### OpenAI Adapter
Abstraction du fournisseur LLM (Adapter Pattern).

## Endpoint

```
GET /health → {"status": "ok"}
```

## Sprint 1

- Implémentation RAG avec pgvector
- Connexion OpenAI
- Intégration WhatsApp / Gmail via n8n
- Orchestration multi-agents
