from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from app.agents.orchestrator import AgentOrchestrator
from app.config import settings

app = FastAPI(
    title="On'Reach AI Service",
    description="Service IA — fondation Sprint 0",
    version="0.1.0",
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.cors_origins,
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

orchestrator = AgentOrchestrator()


@app.get("/health")
async def health() -> dict[str, str]:
    return {"status": "ok"}


@app.get("/")
async def root() -> dict[str, str]:
    return {"service": "onreach-ai", "status": orchestrator.status()}
