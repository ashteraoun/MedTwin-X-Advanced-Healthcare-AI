import asyncio
from datetime import datetime, timedelta, timezone
import unittest

import httpx

from medtwin_ai.main import app
from medtwin_ai.schemas import Observation, TrajectoryRequest
from medtwin_ai.time_series.descriptive import summarize_trajectory


class DescriptiveTrajectoryTests(unittest.TestCase):
    def setUp(self) -> None:
        self.origin = datetime(2025, 1, 1, tzinfo=timezone.utc)

    def post(self, path: str, payload: dict[str, object]) -> httpx.Response:
        async def send_request() -> httpx.Response:
            transport = httpx.ASGITransport(app=app)
            async with httpx.AsyncClient(transport=transport, base_url="http://test") as client:
                return await client.post(path, json=payload)

        return asyncio.run(send_request())

    def make_request(self, values: list[float]) -> TrajectoryRequest:
        return TrajectoryRequest(
            dataset_version="MedTwin-X Generated Trajectory Fixtures v1.0.0",
            subject_key="SYNTH-0001",
            signal_code="SYNTH-MARKER-A",
            unit="arbitrary units",
            observations=[
                Observation(observed_at=self.origin + timedelta(days=index * 30), value=value)
                for index, value in enumerate(values)
            ],
        )

    def test_linear_fixture_returns_measured_descriptive_slope(self) -> None:
        result = summarize_trajectory(self.make_request([1.0, 3.0, 5.0]))

        self.assertEqual(result.slope_per_30_days, 2.0)
        self.assertEqual(result.fit_r_squared, 1.0)
        self.assertEqual(result.residual_rmse, 0.0)
        self.assertEqual(result.observation_count, 3)
        self.assertTrue(result.research_only)

    def test_constant_fixture_has_undefined_r_squared(self) -> None:
        result = summarize_trajectory(self.make_request([4.0, 4.0, 4.0]))

        self.assertEqual(result.slope_per_30_days, 0.0)
        self.assertIsNone(result.fit_r_squared)

    def test_duplicate_timestamps_are_rejected(self) -> None:
        request = self.make_request([1.0, 2.0])
        request.observations[1].observed_at = request.observations[0].observed_at

        with self.assertRaisesRegex(ValueError, "timestamps must be unique"):
            summarize_trajectory(request)

    def test_api_rejects_unmarked_subjects(self) -> None:
        response = self.post(
            "/v1/research/trajectory-summary",
            {
                "dataset_version": "MedTwin-X Generated Trajectory Fixtures v1.0.0",
                "subject_key": "patient-123",
                "signal_code": "SYNTH-MARKER-A",
                "unit": "arbitrary units",
                "observations": [
                    {"observed_at": self.origin.isoformat(), "value": 1},
                    {"observed_at": (self.origin + timedelta(days=30)).isoformat(), "value": 2},
                ],
            },
        )

        self.assertEqual(response.status_code, 422)

    def test_api_returns_structured_research_only_result(self) -> None:
        response = self.post(
            "/v1/research/trajectory-summary",
            {
                "dataset_version": "MedTwin-X Generated Trajectory Fixtures v1.0.0",
                "subject_key": "SYNTH-0001",
                "signal_code": "SYNTH-MARKER-A",
                "unit": "arbitrary units",
                "observations": [
                    {"observed_at": self.origin.isoformat(), "value": 1},
                    {"observed_at": (self.origin + timedelta(days=30)).isoformat(), "value": 2},
                ],
            },
        )

        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json()["slope_per_30_days"], 1.0)
        self.assertTrue(response.json()["research_only"])
        self.assertIn("not a prediction", response.json()["limitations"][1].lower())


if __name__ == "__main__":
    unittest.main()