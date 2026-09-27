import unittest

from medtwin_ai.evaluation.synthetic_experiments import run_synthetic_experiments


class SyntheticExperimentTests(unittest.TestCase):
    def test_temporal_comparison_reports_computed_fixture_metrics(self) -> None:
        result = run_synthetic_experiments()
        temporal = result["temporal_forecast"]

        self.assertEqual(result["dataset"]["subjects"], 8)
        self.assertEqual(temporal["static_baseline"]["sample_count"], 8)
        self.assertAlmostEqual(temporal["static_baseline"]["test_rmse"], 2.0310096)
        self.assertAlmostEqual(temporal["longitudinal_baseline"]["test_rmse"], 1.2)

    def test_aggregated_federated_statistics_match_centralized_fit_without_privacy_claim(self) -> None:
        result = run_synthetic_experiments()["federated_aggregation"]

        self.assertEqual(result["silos"], 3)
        self.assertTrue(result["coefficients_match"])
        self.assertEqual(result["centralized_rmse"], result["simulated_federated_rmse"])
        self.assertFalse(result["formal_privacy_guarantee"])
        self.assertNotIn("subject_key", result["transmitted_fields"])


if __name__ == "__main__":
    unittest.main()