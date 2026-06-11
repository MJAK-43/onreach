"""OpenAI Adapter — squelette Sprint 0."""


class OpenAIAdapter:
    def __init__(self, api_key: str) -> None:
        self._api_key = api_key

    @property
    def is_configured(self) -> bool:
        return bool(self._api_key)

    def complete(self, _prompt: str) -> str:
        raise NotImplementedError("OpenAI completion planned for Sprint 1")
