from datetime import datetime, timedelta, timezone
import unittest

from medtwin_ai.digital_twin.state import DigitalTwinInput, materialize_synthetic_twin


class DigitalTwinMaterializationTests(unittest.TestCase):
    def test_twin_keeps_longitudinal_fields_coverage_uncertainty_and_lineage(self) -> None:
        first = datetime(2025, 1, 1, tzinfo=timezone.utc)
        second = first + timedelta(days=30)
        source = DigitalTwinInput.model_validate({
            "dataset_version": "MedTwin-X Generated Trajectory Fixtures v1.0.0",
            "subject_key": "SYNTH-0001",
            "demographics": {"age_band": 22, "synthetic_group": "group-a"},
            "events": [{
                "event_type": "synthetic_visit",
                "occurred_at": first.isoformat(),
                "source_reference": "fixture:event-1",
            }],
            "laboratory_results": [{
                "code": "SYNTH-MARKER-A",
                "label": "Synthetic marker A",
                "value": 4.0,
                "unit": "arbitrary units",
                "occurred_at": second.isoformat(),
                "source_reference": "fixture:lab-1",
            }],
            "medications": [{
                "code": "SYNTH-MED-1",
                "dataset_label": "Synthetic medication field",
                "occurred_at": first.isoformat(),
            }],
            "diagnoses": [{
                "code": "SYNTH-DX-1",
                "dataset_label": "Synthetic diagnosis field",
                "occurred_at": first.isoformat(),
            }],
            "uncertainty_notes": ["Generated values have no clinical interpretation."],
        })

        state = materialize_synthetic_twin(source)

        self.assertEqual(state.subject_key, "SYNTH-0001")
        self.assertEqual(state.demographics.age_band, 22)
        self.assertEqual(state.as_of, second)
        self.assertEqual(state.temporal_events[0]["event_type"], "synthetic_visit")
        self.assertEqual(state.temporal_events[1]["event_type"], "laboratory_result")
        self.assertEqual(state.modality_coverage["laboratory_results"], 1)
        self.assertEqual(state.modality_coverage["medications"], 1)
        self.assertEqual(state.modality_coverage["diagnoses"], 1)
        self.assertIn("medical_images", state.missing_modalities)
        self.assertIn("fixture:lab-1", state.source_lineage["source_references"])
        self.assertIn("Generated values have no clinical interpretation.", state.uncertainty)

    def test_rejects_non_synthetic_subject_identifier(self) -> None:
        with self.assertRaises(ValueError):
            DigitalTwinInput.model_validate({
                "dataset_version": "MedTwin-X Generated Trajectory Fixtures v1.0.0",
                "subject_key": "patient-123",
            })


if __name__ == "__main__":
    unittest.main()