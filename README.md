# MedTwin-X

**MedTwin-X: Multimodal Medical Digital Twin and Agentic Healthcare Intelligence Platform**

> **Research prototype — not a medical diagnosis or treatment system.**

MedTwin-X is a research prototype for studying multimodal, longitudinal patient representations, medical evidence retrieval, knowledge graphs, agent workflows, and simulated privacy-preserving learning. It is not validated for clinical use and must not be used to make care decisions. The current runnable slice includes synthetic-cohort exploration, versioned digital-twin materialization, structured agent scaffolding with abstention, and a measured synthetic time-series/federated aggregation experiment. It does not include a trained medical model or evidence corpus.

Use synthetic data in development. Only use external data after confirming its de-identification, permitted research purpose, access conditions, and license. Never submit identifiable patient information.

## Research Blueprint

1. **Problem:** Medical research data is fragmented across narrative text, structured measurements, time-stamped events, and (when permitted) image-derived features. We will investigate whether explicitly longitudinal, multimodal representations improve research prediction and evidence traceability compared with simpler baselines.
2. **Hypothesis:** Under patient- and time-separated evaluation, adding aligned modalities, temporal context, and graph-linked evidence may improve prespecified predictive and calibration metrics over unimodal/static baselines. This is a falsifiable hypothesis, not a claim of novelty or demonstrated benefit.
3. **System architecture:** Data is validated and normalized, linked to provenance, represented as events and modality-specific features, assembled into versioned twin states, optionally augmented by a permitted medical knowledge graph, and evaluated by versioned models/agents. Evidence and audit records accompany outputs. A dashboard is a research interface only.
4. **AI architecture:** Separate Python services for NLP, image feature extraction, tabular/time-series modeling, embeddings, retrieval, and evaluation. Laravel owns identity, authorization, projects, dataset metadata, jobs, audit, and APIs. A model registry/version manifest pins code, data, configuration, and dependencies. Models must return typed outputs with uncertainty and provenance; no hidden reasoning traces are stored or exposed.
5. **Digital twin:** A synthetic/de-identified longitudinal state keyed by a pseudonymous dataset-local identifier, containing demographics, observations, labs, vitals, events, medication/diagnosis fields, optional image metadata/features, source links, time bounds, missingness, and uncertainty. It is a research representation, not a continuously updated clinical twin.
6. **Agent architecture:** Data analysis → evidence retrieval → temporal trend → risk analysis → evidence verification → safety critique → explanation. Each stage has a schema-validated input/output, explicit tools/evidence, confidence, errors, timestamps, and audit metadata. A safety stage can abstain. Structured results only; no chain-of-thought.
7. **MySQL schema:** Laravel migrations define projects, datasets, synthetic patients, events, documents, labs, vitals, image metadata, concepts/relations, graph nodes/edges, embeddings, timeline/twin snapshots, agent runs, evidence/citations, predictions, experiments/metrics, model versions, and audit logs. External vector/graph stores are adapters; identifiers, provenance, and references remain relational. See [ARCHITECTURE.md](ARCHITECTURE.md).
8. **Python service architecture:** FastAPI validates synthetic trajectory inputs, computes descriptive linear trends, materializes synthetic twin snapshots, and returns seven structured agent-stage outputs. Retrieval, risk, and evidence-verification stages abstain without a corpus/model. All requested packages exist, but text, multimodal-model, graph, RAG, and learned explainability integrations await permitted sources and models.
9. **Laravel architecture:** Laravel 12/PHP 8.2+ provides the relational research schema, Sanctum user route, synthetic-only read APIs, an experiment proxy/history API, and audit persistence. MySQL 8+ is intended; SQLite is used for tests and local verification. Research demo reads are public because they contain generated fixture records only.
10. **Experimental methodology:** The first actual run compares static carry-forward with a per-subject temporal linear trend on a held-out synthetic visit and verifies centralized/federated sufficient-statistic equivalence. Other requested modality, RAG, agent, and graph comparisons remain planned until relevant corpora, models, and labels are available. Record only metrics computed by the runner.
11. **Dataset strategy:** Start with generated synthetic records and test fixtures. Candidate external sources require a documented license/access review and de-identification assessment before ingestion; credentialed or restricted datasets require their approvals and data-use terms. Track source, version, transformations, permitted modalities, and lineage.
12. **Safety and ethics:** Research-only positioning, no diagnosis/treatment advice, no real identifiable data, least-privilege access, data minimization, provenance, auditable model versions, uncertainty and abstention, evidence-linked statements, and a documented human research review. Do not claim clinical validity or deploy for care.

## Run Locally

Requirements: PHP 8.2+, Composer, and the Laravel 12 dependencies in `medtwin-x/`; Python 3.10+ for the FastAPI service. MySQL 8+ is the intended research database; SQLite remains usable for automated tests.

```powershell
cd medtwin-x
composer install
php artisan migrate
php artisan serve
```

The AI service is in `services/ai/`. From that directory, create/activate a Python environment, install `requirements.txt`, then run `uvicorn medtwin_ai.main:app --host 127.0.0.1 --port 8001`. Laravel calls this local service using `MEDTWIN_AI_URL` (default `http://127.0.0.1:8001`). Do not expose either service to the public internet with this development configuration.

Set database connection values in the local `.env`; do not commit secrets. Sanctum registration/login issue expiring bearer tokens for authenticated project APIs. Verify `GET /api/research/status` and `/`. The status response is not a clinical service. See [API.md](API.md) for the current route and roadmap.

For the local-only Docker stack, run `docker compose --env-file .\\medtwin-x\\.env up --build` from the repository root. It requires an `APP_KEY` supplied from `medtwin-x/.env`; configured ports bind to loopback. The default Compose database credentials are development-only and must never be used with real data or in production. The Compose files have not been executed in this environment because Docker is unavailable.

## Documentation

- [ARCHITECTURE.md](ARCHITECTURE.md)
- [RESEARCH_METHODOLOGY.md](RESEARCH_METHODOLOGY.md)
- [DATASET.md](DATASET.md)
- [MODEL_CARD.md](MODEL_CARD.md)
- [EXPERIMENTS.md](EXPERIMENTS.md)
- [ETHICS_AND_SAFETY.md](ETHICS_AND_SAFETY.md)
- [PRIVACY.md](PRIVACY.md)
- [LIMITATIONS.md](LIMITATIONS.md)
- [API.md](API.md)

## Current Status

Implemented: synthetic cohort seeding and read-only exploration; normalized research tables; a synthetic twin materializer; FastAPI trend analysis and a seven-stage structured workflow; one deterministic time-split experiment; simulated federated aggregation with no formal privacy guarantee; Laravel persistence of real experiment outputs; research dashboard and requested documents.

The experiment observed static carry-forward RMSE `2.0310096` and longitudinal-trend RMSE `1.2` on 8 generated subjects. Centralized and federated sufficient-statistic regression both measured RMSE `14.18571024` and matching coefficients. These results describe only this tiny arbitrary-unit fixture, are not clinical measurements, and do not demonstrate generalization or privacy. There are no trained medical/multimodal models, licensed evidence corpus, populated medical graph, text/image NLP, formal differential privacy, or clinical-validation results.
