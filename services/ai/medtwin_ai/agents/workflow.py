from datetime import datetime, timezone

from medtwin_ai.agents.contracts import AgentRun
from medtwin_ai.schemas import TrajectoryRequest
from medtwin_ai.time_series.descriptive import summarize_trajectory


def _run(
    agent_type: str,
    status: str,
    request: TrajectoryRequest,
    tools_used: list[str],
    result: dict[str, object],
) -> AgentRun:
    completed_at = datetime.now(timezone.utc)
    input_summary = {
        "dataset_version": request.dataset_version,
        "subject_key": request.subject_key,
        "signal_code": request.signal_code,
        "unit": request.unit,
        "observations": [item.model_dump(mode="json") for item in request.observations],
    }
    return AgentRun(
        agent_type=agent_type,
        status=status,
        input=input_summary,
        tools_used=tools_used,
        retrieved_evidence=[],
        structured_result=result,
        confidence=None,
        errors=[],
        started_at=completed_at,
        completed_at=completed_at,
    )


def run_synthetic_research_workflow(request: TrajectoryRequest) -> list[AgentRun]:
    temporal = summarize_trajectory(request)
    data_result = {
        "observation_count": temporal.observation_count,
        "span_days": temporal.span_days,
        "signal_code": temporal.signal_code,
        "unit": temporal.unit,
        "synthetic_only": True,
    }
    temporal_result = temporal.model_dump(mode="json")

    return [
        _run("patient_data_analysis", "completed", request, ["input_schema_validation"], data_result),
        _run(
            "medical_evidence_retrieval",
            "abstained",
            request,
            ["permitted_evidence_corpus_check"],
            {"available": False, "reason": "No permitted medical evidence corpus is configured."},
        ),
        _run("temporal_trend", "completed", request, ["descriptive_linear_trend"], temporal_result),
        _run(
            "risk_analysis",
            "abstained",
            request,
            ["model_registry_check"],
            {"available": False, "reason": "No trained or validated risk model is registered."},
        ),
        _run(
            "evidence_verification",
            "abstained",
            request,
            ["citation_presence_check"],
            {"verified_statements": 0, "reason": "No retrieved evidence is available to verify."},
        ),
        _run(
            "safety_critique",
            "completed",
            request,
            ["research_scope_guard"],
            {
                "research_only": True,
                "clinical_recommendation_generated": False,
                "identifiable_data_accepted": False,
            },
        ),
        _run(
            "explanation",
            "completed",
            request,
            ["structured_result_formatter"],
            {
                "summary": "A descriptive trend was computed from supplied synthetic fixture observations.",
                "source_dataset_version": request.dataset_version,
                "medical_claims": [],
            },
        ),
    ]