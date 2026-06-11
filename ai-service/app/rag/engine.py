"""RAG Engine — squelette Sprint 0."""


class RAGEngine:
    """Moteur RAG Campus France / Visa / Parcoursup (Sprint 1+)."""

    def ingest(self, _documents: list[str]) -> None:
        raise NotImplementedError("RAG ingestion planned for Sprint 1")

    def query(self, _question: str) -> str:
        raise NotImplementedError("RAG query planned for Sprint 1")
