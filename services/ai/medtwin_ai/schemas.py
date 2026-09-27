from datetime import datetime
from typing import Literal

from pydantic import BaseModel, ConfigDict, Field


class Observation(BaseModel):
    model_config = ConfigDict(extra="forbid")

    observed_at: datetime
    value: float = Field(allow_inf_nan=False)


class TrajectoryRequest(BaseModel):
    model_config = ConfigDict(extra="forbid")

    dataset_version: Literal["MedTwin-X Generated Trajectory Fixtures v1.0.0"]
    subject_key: str = Field(pattern=r"^SYNTH-[0-9]{4}$")
    signal_code: str = Field(pattern=r"^SYNTH-[A-Z0-9-]{1,80}$")
    unit: Literal["arbitrary units"]
    observations: list[Observation] = Field(min_length=2, max_length=2000)


class TrajectorySummary(BaseModel):
    research_only: Literal[True] = True
    analysis_type: Literal["descriptive_linear_trend"] = "descriptive_linear_trend"
    subject_key: str
    signal_code: str
    unit: str
    observation_count: int
    span_days: float
    slope_per_30_days: float
    fit_r_squared: float | None
    residual_rmse: float
    limitations: list[str]