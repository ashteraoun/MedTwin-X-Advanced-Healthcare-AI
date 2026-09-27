# Python Research Service

FastAPI provides synthetic-only descriptive analysis, digital-twin materialization, structured agent-stage outputs, and one deterministic fixture experiment. It has no connection to the Laravel database and does not persist request payloads.

## Local Run

Use a Python 3.10+ environment and install `requirements.txt`. From this directory, run:

```powershell
python -m uvicorn medtwin_ai.main:app --host 127.0.0.1 --port 8001
```

Run tests with `python -m unittest discover -s tests -v` from this directory. In this workspace the test suite was run with Python 3.13.

The schemas accept only the generated fixture dataset version, subject keys shaped like `SYNTH-0001`, `SYNTH-*` signal identifiers, and arbitrary units. Such validation cannot prove caller-supplied data provenance. Do not send real or identifiable patient data. Bind to localhost for development.

The `medical_nlp`, `multimodal`, `knowledge_graph`, `medical_rag`, `federated_learning`, and `explainability` directories are architecture boundaries only. No clinical text/image model, medical corpus, graph population, or differential-privacy mechanism is included. Agent stages are deterministic; unavailable evidence and risk services abstain.