# Research Methodology

**Research question:** Under patient- and time-separated evaluation, do aligned multimodal longitudinal representations provide measurable benefit over modality-specific and static baselines on explicitly defined synthetic or approved de-identified research tasks?

**Hypothesis:** Temporal and cross-modal context may improve performance or calibration where informative signals are present, but can also fail under missingness, distribution shift, or noisy inputs. Benefits must be demonstrated empirically and compared with modality-availability and simple baselines. This is not a clinical hypothesis or claim of benefit.

## Protocol

1. Define a non-clinical research target, cohort, index time, forecast horizon, eligible modalities, and inclusion criteria before training.
2. Freeze a versioned dataset manifest and provenance, transformation code, model/configuration, random seeds, and dependency versions.
3. Split by subject before feature generation; use chronological validation/test windows where temporal prediction is studied. Prevent post-index information and duplicate subjects from crossing splits.
4. Compare clinical-text-only, tabular/time-series-only, and multimodal systems; static snapshots against event-sequence representations; generic versus medically sourced retrieval; single versus multi-agent orchestration; graph augmentation against no graph; centralized against simulated federated learning.
5. Report AUROC/AUPRC only for appropriate classification tasks and alongside calibration (Brier score, calibration error), uncertainty, coverage/abstention, subgroup and missing-modality performance, confidence intervals, sample counts, and failure analysis. State task prevalence and class imbalance.
6. Repeat with multiple seeds where appropriate. Keep model selection separate from the test set. Use paired subject-level bootstrap intervals for model differences where the sample size supports it.
7. Publish negative results and protocol deviations. Metrics are written only by completed evaluation runs; do not manually populate results.

## Current Status

No clinical task labels, trained medical models, multimodal evaluation, retrieval benchmark, or graph comparison exists. One software experiment measured static and longitudinal baselines on eight deterministic arbitrary-unit synthetic trajectories; a second arm compared pooled and simulated-silo sufficient-statistic regression. Results are recorded in [EXPERIMENTS.md](EXPERIMENTS.md) and must not be interpreted as clinical observations or evidence of predictive performance on real populations.