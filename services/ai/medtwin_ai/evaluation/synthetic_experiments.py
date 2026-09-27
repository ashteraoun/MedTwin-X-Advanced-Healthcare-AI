from datetime import datetime, timezone
from math import sqrt


DATASET_VERSION = "MedTwin-X Generated Trajectory Fixtures v1.0.0"
SITE_COUNT = 3


def generated_series() -> list[list[float]]:
    profiles = [31, 46, 27, 58, 39, 66, 34, 51]
    return [
        [
            float(base + ((subject_index % 3) - 1) * visit + ((visit % 2) * 2))
            for visit in range(6)
        ]
        for subject_index, base in enumerate(profiles)
    ]


def _regression_sufficient_statistics(
    series: list[list[float]],
) -> tuple[int, float, float, float, float]:
    pairs = [
        (float(visit), values[visit + 1])
        for values in series
        for visit in range(4)
    ]
    return (
        len(pairs),
        sum(x for x, _ in pairs),
        sum(y for _, y in pairs),
        sum(x * x for x, _ in pairs),
        sum(x * y for x, y in pairs),
    )


def _fit_from_sufficient_statistics(
    stats: tuple[int, float, float, float, float],
) -> tuple[float, float]:
    count, sum_x, sum_y, sum_xx, sum_xy = stats
    denominator = count * sum_xx - sum_x**2
    slope = (count * sum_xy - sum_x * sum_y) / denominator
    intercept = (sum_y - slope * sum_x) / count
    return intercept, slope


def _rmse(actual: list[float], predicted: list[float]) -> float:
    return sqrt(sum((truth - estimate) ** 2 for truth, estimate in zip(actual, predicted)) / len(actual))


def run_synthetic_experiments() -> dict[str, object]:
    series = generated_series()
    actual = [values[5] for values in series]
    static_predictions = [values[4] for values in series]

    trend_predictions = []
    for values in series:
        x_values = [float(index) for index in range(5)]
        y_values = values[:5]
        x_mean = sum(x_values) / len(x_values)
        y_mean = sum(y_values) / len(y_values)
        slope = sum((x - x_mean) * (y - y_mean) for x, y in zip(x_values, y_values)) / sum(
            (x - x_mean) ** 2 for x in x_values
        )
        trend_predictions.append(y_mean + slope * (5 - x_mean))

    site_series = [
        [values for index, values in enumerate(series) if index % SITE_COUNT == site]
        for site in range(SITE_COUNT)
    ]
    client_statistics = [_regression_sufficient_statistics(client) for client in site_series]
    aggregated_statistics = tuple(sum(stats[column] for stats in client_statistics) for column in range(5))
    centralized_statistics = _regression_sufficient_statistics(series)
    centralized_coefficients = _fit_from_sufficient_statistics(centralized_statistics)
    federated_coefficients = _fit_from_sufficient_statistics(aggregated_statistics)
    test_x = 4.0
    centralized_predictions = [centralized_coefficients[0] + centralized_coefficients[1] * test_x] * len(series)
    federated_predictions = [federated_coefficients[0] + federated_coefficients[1] * test_x] * len(series)

    return {
        "experiment_id": "synthetic-temporal-and-federated-baseline-v1",
        "completed_at": datetime.now(timezone.utc).isoformat(),
        "dataset": {
            "version": DATASET_VERSION,
            "subjects": len(series),
            "visits_per_subject": 6,
            "signal": "SYNTH-MARKER-A",
            "unit": "arbitrary units",
            "source": "deterministic generated fixture function in medtwin_ai.evaluation.synthetic_experiments",
        },
        "temporal_forecast": {
            "target": "synthetic visit 6 marker value",
            "split": "first five visits per subject for forecast; sixth visit held out",
            "static_baseline": {
                "method": "last observation carried forward",
                "test_rmse": round(_rmse(actual, static_predictions), 8),
                "sample_count": len(actual),
            },
            "longitudinal_baseline": {
                "method": "per-subject ordinary least-squares linear trend over first five visits",
                "test_rmse": round(_rmse(actual, trend_predictions), 8),
                "sample_count": len(actual),
            },
        },
        "federated_aggregation": {
            "target": "next synthetic marker value from visit index",
            "split": "transitions 1-4 for fitting, visit 6 as held-out evaluation",
            "silos": SITE_COUNT,
            "subjects_per_silo": [len(client) for client in site_series],
            "method": "local ordinary-least-squares sufficient statistics summed before fitting",
            "transmitted_fields": ["count", "sum_x", "sum_y", "sum_x_squared", "sum_xy"],
            "centralized_rmse": round(_rmse(actual, centralized_predictions), 8),
            "simulated_federated_rmse": round(_rmse(actual, federated_predictions), 8),
            "coefficients_match": all(
                abs(central - federated) < 1e-10
                for central, federated in zip(centralized_coefficients, federated_coefficients)
            ),
            "formal_privacy_guarantee": False,
            "sample_count": len(actual),
        },
        "limitations": [
            "Generated arbitrary-unit software fixtures only; not clinical measurements or outcomes.",
            "Eight subjects are insufficient to support generalization claims.",
            "Federated sufficient statistics are not differentially private and may leak information in other settings.",
            "No medical risk, diagnosis, or treatment target is modeled.",
        ],
    }