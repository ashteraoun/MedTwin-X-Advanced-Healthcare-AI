from datetime import datetime, timezone
from typing import Literal

from pydantic import BaseModel, ConfigDict, Field


class SyntheticDemographics(BaseModel):
    model_config = ConfigDict(extra="forbid")

    age_band: str | int | None = None
    synthetic_group: str | None = None
    sex_at_birth: str | None = None


class TimedMetadata(BaseModel):
    model_config = ConfigDict(extra="forbid")

    occurred_at: datetime | None = None
    source_reference: str | None = None


class SyntheticEvent(TimedMetadata):
    event_type: str
    fields: dict[str, str | int | float | bool | None] = Field(default_factory=dict)


class SyntheticMeasurement(TimedMetadata):
    code: str
    label: str
    value: float = Field(allow_inf_nan=False)
    unit: str


class DatasetField(TimedMetadata):
    code: str
    dataset_label: str


class SyntheticDocument(TimedMetadata):
    document_type: str
    content_checksum: str | None = None


class SyntheticImage(TimedMetadata):
    modality: str
    body_region: str | None = None
    metadata: dict[str, str | int | float | bool] = Field(default_factory=dict)
    derived_features: dict[str, float] = Field(default_factory=dict)


class DigitalTwinInput(BaseModel):
    model_config = ConfigDict(extra="forbid")

    dataset_version: Literal["MedTwin-X Generated Trajectory Fixtures v1.0.0"]
    subject_key: str = Field(pattern=r"^SYNTH-[0-9]{4}$")
    demographics: SyntheticDemographics = Field(default_factory=SyntheticDemographics)
    events: list[SyntheticEvent] = Field(default_factory=list)
    documents: list[SyntheticDocument] = Field(default_factory=list)
    laboratory_results: list[SyntheticMeasurement] = Field(default_factory=list)
    vital_signs: list[SyntheticMeasurement] = Field(default_factory=list)
    medications: list[DatasetField] = Field(default_factory=list)
    diagnoses: list[DatasetField] = Field(default_factory=list)
    medical_images: list[SyntheticImage] = Field(default_factory=list)
    uncertainty_notes: list[str] = Field(default_factory=list)


class DigitalTwinState(BaseModel):
    research_only: Literal[True] = True
    representation_version: Literal["synthetic-twin-v1"] = "synthetic-twin-v1"
    dataset_version: str
    subject_key: str
    as_of: datetime | None
    demographics: SyntheticDemographics
    temporal_events: list[dict[str, object]]
    documents: list[dict[str, object]]
    laboratory_results: list[dict[str, object]]
    vital_signs: list[dict[str, object]]
    medications: list[dict[str, object]]
    diagnoses: list[dict[str, object]]
    medical_images: list[dict[str, object]]
    modality_coverage: dict[str, int]
    missing_modalities: list[str]
    uncertainty: list[str]
    source_lineage: dict[str, object]


def _utc(value: datetime) -> datetime:
    return value.replace(tzinfo=timezone.utc) if value.tzinfo is None else value.astimezone(timezone.utc)


def materialize_synthetic_twin(source: DigitalTwinInput) -> DigitalTwinState:
    timeline: list[dict[str, object]] = []
    for event in source.events:
        timeline.append({"event_type": event.event_type, **event.model_dump()})
    for result in source.laboratory_results:
        timeline.append({"event_type": "laboratory_result", **result.model_dump()})
    for vital in source.vital_signs:
        timeline.append({"event_type": "vital_sign", **vital.model_dump()})
    timeline.sort(key=lambda item: _utc(item["occurred_at"]) if isinstance(item["occurred_at"], datetime) else datetime.min.replace(tzinfo=timezone.utc))

    coverage = {
        "demographics": 1 if source.demographics.model_fields_set else 0,
        "events": len(source.events),
        "documents": len(source.documents),
        "laboratory_results": len(source.laboratory_results),
        "vital_signs": len(source.vital_signs),
        "medications": len(source.medications),
        "diagnoses": len(source.diagnoses),
        "medical_images": len(source.medical_images),
    }
    missing = [
        modality for modality in ("documents", "laboratory_results", "vital_signs", "medical_images")
        if coverage[modality] == 0
    ]
    dates = [item["occurred_at"] for item in timeline if isinstance(item["occurred_at"], datetime)]
    dates.extend(item.occurred_at for item in source.documents if item.occurred_at)
    dates.extend(item.occurred_at for item in source.medications if item.occurred_at)
    dates.extend(item.occurred_at for item in source.diagnoses if item.occurred_at)
    dates.extend(item.occurred_at for item in source.medical_images if item.occurred_at)

    lineage = {
        "dataset_version": source.dataset_version,
        "synthetic": True,
        "source_references": sorted({
            item.source_reference
            for collection in (source.events, source.documents, source.laboratory_results,
                               source.vital_signs, source.medications, source.diagnoses,
                               source.medical_images)
            for item in collection
            if item.source_reference
        }),
    }

    return DigitalTwinState(
        dataset_version=source.dataset_version,
        subject_key=source.subject_key,
        as_of=max((_utc(value) for value in dates), default=None),
        demographics=source.demographics,
        temporal_events=timeline,
        documents=[item.model_dump(mode="json") for item in source.documents],
        laboratory_results=[item.model_dump(mode="json") for item in source.laboratory_results],
        vital_signs=[item.model_dump(mode="json") for item in source.vital_signs],
        medications=[item.model_dump(mode="json") for item in source.medications],
        diagnoses=[item.model_dump(mode="json") for item in source.diagnoses],
        medical_images=[item.model_dump(mode="json") for item in source.medical_images],
        modality_coverage=coverage,
        missing_modalities=missing,
        uncertainty=list(source.uncertainty_notes),
        source_lineage=lineage,
    )