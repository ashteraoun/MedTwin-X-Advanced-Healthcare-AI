# Ethics and Safety

> **Research prototype — not a medical diagnosis or treatment system.**

MedTwin-X is not validated for healthcare delivery and must not be used for diagnosis, treatment, triage, or personalized medical advice. The application exposes generated fixtures and can compute arbitrary-unit descriptive forecasts; it produces no clinical risk estimates or recommendations.

## Required Safeguards

- Use generated synthetic data by default. External datasets require documented license/access authority, research approval where required, and privacy review.
- Never enter identifiable patient information. Minimize fields and precision; do not log request bodies containing health data.
- Keep evidence provenance and model versions with every future generated research statement. A citation is not proof of support; verification must be measured and recorded.
- Require uncertainty, calibrated abstention behavior, and a safety critique in future model/agent outputs. A safety layer does not make an unvalidated model safe for care.
- Store structured agent inputs/tools/retrieved evidence/results/confidence/errors/timestamps only. Do not store or expose hidden chain-of-thought.
- Project APIs require Sanctum authentication and owner policies. Public twin/agent/experiment writes are throttled and restricted to generated synthetic fixtures; add stronger authorization before any non-demo data or workflows are enabled. Restrict access to raw data and credentials.
- Review generated outputs for advice, unsupported claims, sensitive leakage, and unsafe instructions before any human-facing research release.

Research teams remain responsible for IRB/ethics review, data-use agreements, local regulations, security review, and publication standards applicable to their work.