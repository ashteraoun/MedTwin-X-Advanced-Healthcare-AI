# Model Card

## MedTwin-X Foundation

- **Status:** No trained predictive or generative medical model is shipped. The service includes deterministic descriptive linear regression and a tiny synthetic forecasting baseline for software research.
- **Intended use:** Software research and synthetic-cohort workflow development only.
- **Out-of-scope use:** Diagnosis, triage, treatment selection, patient-facing advice, or clinical decision-making.
- **Training data:** None. Demo records are deterministic generated fixtures, not model training data.
- **Evaluation:** One held-out experiment ran on 8 deterministic generated subjects: static carry-forward RMSE `2.0310096`; per-subject temporal linear trend RMSE `1.2`, in arbitrary units. These are actual fixture results, not evidence of generalization or clinical performance.
- **Known limitations:** No medical NLP, learned embedding, image, language, or risk model is connected. Agent stages are deterministic; risk and evidence stages abstain. No evidence corpus or clinical validation exists.
- **Privacy:** Do not send identifiable data. Synthetic demo API exposes only its synthetic dataset.
- **Versioning:** Model versions are represented in the schema for future provenance; no learned model artifact is registered. The experiment runner has a code-level experiment identifier but no external MLflow tracker.

Any later model card must name the exact artifact/checksum, code revision, data manifest, intended research task, evaluation cohort/splits, metrics with intervals, subgroup/missingness analysis, failure modes, and prohibited uses.