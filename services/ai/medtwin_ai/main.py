from fastapi import FastAPI, HTTPException

from medtwin_ai.agents.contracts import AgentRun
from medtwin_ai.agents.workflow import run_synthetic_research_workflow
from medtwin_ai.digital_twin.state import DigitalTwinInput, DigitalTwinState, materialize_synthetic_twin
from medtwin_ai.evaluation.synthetic_experiments import run_synthetic_experiments
from medtwin_ai.schemas import TrajectoryRequest, TrajectorySummary
from medtwin_ai.time_series.descriptive import summarize_trajectory

app = FastAPI(
    title="MedTwin-X Research Analysis Service",
    version="0.1.0",
    description="Synthetic-data research service only; not a medical diagnosis or treatment system.",
)


@app.get("/health")
def health() -> dict[str, str | bool]:
    return {
        "service": "medtwin-x-analysis",
        "status": "ready",
        "research_only": True,
        "predictive_models_available": False,
    }


@app.post("/v1/research/trajectory-summary", response_model=TrajectorySummary)
def trajectory_summary(request: TrajectoryRequest) -> TrajectorySummary:
    try:
        return summarize_trajectory(request)
    except ValueError as error:
        raise HTTPException(status_code=422, detail=str(error)) from error


@app.get("/v1/research/experiments/synthetic-baseline")
def synthetic_baseline_experiment() -> dict[str, object]:
    return run_synthetic_experiments()


@app.post("/v1/research/agents/analyze-synthetic", response_model=list[AgentRun])
def analyze_synthetic(request: TrajectoryRequest) -> list[AgentRun]:
    try:
        return run_synthetic_research_workflow(request)
    except ValueError as error:
        raise HTTPException(status_code=422, detail=str(error)) from error


@app.post("/v1/research/digital-twin/materialize-synthetic", response_model=DigitalTwinState)
def materialize_twin(request: DigitalTwinInput) -> DigitalTwinState:
    return materialize_synthetic_twin(request)