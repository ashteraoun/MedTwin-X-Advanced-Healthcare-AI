# Dataset Strategy

## Generated Demo Data

`SyntheticResearchDemoSeeder` creates eight generated subject records with six dated events each. Each event has a made-up research marker and activity index in arbitrary units. The Python experiment runner has a matching deterministic fixture generator for its time-split evaluation. These values are software fixtures, not physiological measurements, do not represent patients, and have no medical interpretation or clinical outcome label.

## Admission Requirements

Before introducing any external dataset, record the exact source/version, license or data-use agreement, permitted purpose/modalities, access restrictions, de-identification review, known limitations, preprocessing lineage, and content checksum in dataset metadata. Restricted datasets require their own approvals and controlled environment. Never put credentials or source data in this repository. Do not upload identifiable or re-identifiable health information.

Only data marked synthetic is currently exposed by Laravel cohort endpoints. Dataset admission metadata does not constitute authorization to use underlying data. The twin schema accepts medication and diagnosis entries only as dataset fields; it does not recommend either. No importer, de-identification tool, medical image store, document corpus, or dataset download is active.

## Future Modality Handling

- Text/documents: retain source reference and permitted de-identified text only; validate PHI controls before NLP.
- Labs/vitals/events: preserve units, codes, observation time, missingness, and source lineage; normalize without erasing original values.
- Images: keep permitted object references and derived features; validate license and remove identifying metadata before processing.
- Demographics: minimize precision and store synthetic/dataset-scoped pseudonyms, never direct identifiers.

The synthetic demo is not a substitute for realistic external validation and cannot support clinical claims.