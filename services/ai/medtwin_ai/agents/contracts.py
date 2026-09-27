from datetime import datetime
from typing import Literal

from pydantic import BaseModel, ConfigDict, Field


AgentType = Literal[
    "patient_data_analysis",
    "medical_evidence_retrieval",
    "temporal_trend",
    "risk_analysis",
    "evidence_verification",
    "safety_critique",
    "explanation",
]


class AgentRun(BaseModel):
    model_config = ConfigDict(extra="forbid")

    agent_type: AgentType
    status: Literal["completed", "abstained", "unavailable", "failed"]
    input: dict[str, object]
    tools_used: list[str] = Field(default_factory=list)
    retrieved_evidence: list[dict[str, object]] = Field(default_factory=list)
    structured_result: dict[str, object]
    confidence: float | None = Field(default=None, ge=0, le=1)
    errors: list[str] = Field(default_factory=list)
    started_at: datetime
    completed_at: datetime