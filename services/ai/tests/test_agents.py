from datetime import datetime, timedelta, timezone
import unittest

from medtwin_ai.agents.workflow import run_synthetic_research_workflow
from medtwin_ai.schemas import Observation, TrajectoryRequest


class StructuredAgentWorkflowTests(unittest.TestCase):
    def test_all_seven_stages_return_structured_outputs_and_abstain_without_resources(self) -> None:
        origin = datetime(2025, 1, 1, tzinfo=timezone.utc)
        request = TrajectoryRequest(
            dataset_version="MedTwin-X Generated Trajectory Fixtures v1.0.0",
            subject_key="SYNTH-0001",
            signal_code="SYNTH-MARKER-A",
            unit="arbitrary units",
            observations=[
                Observation(observed_at=origin, value=1.0),
                Observation(observed_at=origin + timedelta(days=30), value=2.0),
            ],
        )

        outputs = run_synthetic_research_workflow(request)

        self.assertEqual(
            [output.agent_type for output in outputs],
            [
                "patient_data_analysis", "medical_evidence_retrieval", "temporal_trend",
                "risk_analysis", "evidence_verification", "safety_critique", "explanation",
            ],
        )
        self.assertEqual(outputs[1].status, "abstained")
        self.assertEqual(outputs[3].status, "abstained")
        self.assertTrue(outputs[5].structured_result["research_only"])
        self.assertEqual(outputs[5].structured_result["clinical_recommendation_generated"], False)

        for output in outputs:
            serialized = output.model_dump(mode="json")
            self.assertIn("input", serialized)
            self.assertIn("tools_used", serialized)
            self.assertIn("retrieved_evidence", serialized)
            self.assertIn("structured_result", serialized)
            self.assertIn("confidence", serialized)
            self.assertIn("errors", serialized)
            self.assertIn("started_at", serialized)
            self.assertIn("completed_at", serialized)
            self.assertNotIn("chain_of_thought", serialized)


if __name__ == "__main__":
    unittest.main()