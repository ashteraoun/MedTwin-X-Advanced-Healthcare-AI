# Privacy

The demo uses generated subject keys (`SYNTH-####`) and arbitrary-unit values; it is not sourced from people. The Laravel schema supports lineage and access-status metadata but does not itself anonymize, de-identify, or authorize a dataset.

## Operational Rules

- Never commit `.env`, secrets, credentials, source datasets, or identifiable health information.
- Do not use production or real patient data in tests, logs, screenshots, issue reports, or prompts.
- For approved de-identified research data, document the source and terms, minimize retained fields, control access, and keep identifiers separate from research records.
- A pseudonym is not anonymization. Assess linkage and re-identification risk for each release and combination of fields.
- Do not expose raw documents or image objects through the research API. There is no general ingestion or media-serving route.
- Use encryption, access controls, retention limits, backups, incident response, and audit review appropriate to the approved dataset before handling anything beyond synthetic data.

## Federated Learning

The experimental runner partitions generated subjects across three simulated silos and aggregates only linear-regression sufficient statistics (`count`, sums, and cross-products), not patient-level rows. It verifies coefficient equality against centralized fitting. This is not a formal privacy guarantee: aggregate statistics can leak information, and no differential privacy mechanism is implemented. Never transmit patient-level records between clients. Any future differential privacy claim must specify clipping, noise, accountant, budget, and tested implementation.