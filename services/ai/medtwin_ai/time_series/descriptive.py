from math import sqrt

from medtwin_ai.schemas import TrajectoryRequest, TrajectorySummary


def summarize_trajectory(request: TrajectoryRequest) -> TrajectorySummary:
    observations = sorted(request.observations, key=lambda item: item.observed_at)
    timestamps = [item.observed_at for item in observations]
    if any(left == right for left, right in zip(timestamps, timestamps[1:])):
        raise ValueError("Observation timestamps must be unique.")

    origin = timestamps[0]
    x_values = [(timestamp - origin).total_seconds() / 86400 for timestamp in timestamps]
    y_values = [item.value for item in observations]
    x_mean = sum(x_values) / len(x_values)
    y_mean = sum(y_values) / len(y_values)
    x_variance = sum((value - x_mean) ** 2 for value in x_values)
    if x_variance == 0:
        raise ValueError("Observations must span more than one timestamp.")

    slope_per_day = sum(
        (x_value - x_mean) * (y_value - y_mean)
        for x_value, y_value in zip(x_values, y_values)
    ) / x_variance
    intercept = y_mean - slope_per_day * x_mean
    residuals = [y - (intercept + slope_per_day * x) for x, y in zip(x_values, y_values)]
    residual_sum_squares = sum(residual**2 for residual in residuals)
    total_sum_squares = sum((value - y_mean) ** 2 for value in y_values)
    r_squared = (
        1.0 - residual_sum_squares / total_sum_squares
        if total_sum_squares > 0
        else None
    )

    return TrajectorySummary(
        subject_key=request.subject_key,
        signal_code=request.signal_code,
        unit=request.unit,
        observation_count=len(observations),
        span_days=round(x_values[-1], 6),
        slope_per_30_days=round(slope_per_day * 30, 8),
        fit_r_squared=round(r_squared, 8) if r_squared is not None else None,
        residual_rmse=round(sqrt(residual_sum_squares / len(residuals)), 8),
        limitations=[
            "Descriptive summary of explicitly identified synthetic fixture data only.",
            "Not a prediction, clinical risk estimate, diagnosis, or treatment recommendation.",
            "A linear fit does not model irregular sampling, missing modalities, or causal effects.",
        ],
    )