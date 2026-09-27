<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ResearchDataController extends Controller
{
    public function overview(): JsonResponse
    {
        $syntheticDatasetIds = DB::table('datasets')->where('source_kind', 'synthetic')->pluck('id');

        return response()->json([
            'research_only' => true,
            'notice' => 'Research prototype — not a medical diagnosis or treatment system.',
            'data_scope' => 'Synthetic demonstration data only.',
            'datasets' => $syntheticDatasetIds->count(),
            'subjects' => DB::table('patients_synthetic')->whereIn('dataset_id', $syntheticDatasetIds)->count(),
            'events' => DB::table('patient_events')->join('patients_synthetic', 'patients_synthetic.id', '=', 'patient_events.patient_id')
                ->whereIn('patients_synthetic.dataset_id', $syntheticDatasetIds)->count(),
            'laboratory_observations' => DB::table('lab_results')->join('patients_synthetic', 'patients_synthetic.id', '=', 'lab_results.patient_id')
                ->whereIn('patients_synthetic.dataset_id', $syntheticDatasetIds)->count(),
            'vital_observations' => DB::table('vital_signs')->join('patients_synthetic', 'patients_synthetic.id', '=', 'vital_signs.patient_id')
                ->whereIn('patients_synthetic.dataset_id', $syntheticDatasetIds)->count(),
            'trained_models_available' => false,
            'experiment_runs' => DB::table('experiment_runs')
                ->where('experiment_key', 'synthetic-temporal-and-federated-baseline-v1')->count(),
        ]);
    }

    public function patients(): JsonResponse
    {
        $patients = DB::table('patients_synthetic')
            ->join('datasets', 'datasets.id', '=', 'patients_synthetic.dataset_id')
            ->where('datasets.source_kind', 'synthetic')
            ->select('patients_synthetic.id', 'patients_synthetic.subject_key', 'patients_synthetic.demographics', 'datasets.name as dataset_name')
            ->orderBy('patients_synthetic.subject_key')
            ->get()
            ->map(fn (object $patient): array => [
                'id' => $patient->id,
                'subject_key' => $patient->subject_key,
                'demographics' => json_decode($patient->demographics ?? '{}', true),
                'dataset' => $patient->dataset_name,
                'event_count' => DB::table('patient_events')->where('patient_id', $patient->id)->count(),
            ]);

        return response()->json([
            'research_only' => true,
            'data_scope' => 'Synthetic demonstration data only.',
            'data' => $patients,
        ]);
    }

    public function patient(int $patientId): JsonResponse
    {
        $patient = DB::table('patients_synthetic')
            ->join('datasets', 'datasets.id', '=', 'patients_synthetic.dataset_id')
            ->where('datasets.source_kind', 'synthetic')
            ->where('patients_synthetic.id', $patientId)
            ->select('patients_synthetic.*', 'datasets.name as dataset_name')
            ->first();

        abort_if($patient === null, 404);

        $events = DB::table('patient_events')->where('patient_id', $patientId)
            ->orderBy('occurred_at')->get()->map(function (object $event): array {
                return [
                    'id' => $event->id,
                    'type' => $event->event_type,
                    'occurred_at' => $event->occurred_at,
                    'payload' => json_decode($event->normalized_payload ?? '{}', true),
                    'uncertainty' => json_decode($event->uncertainty ?? '{}', true),
                    'laboratory_results' => DB::table('lab_results')->where('patient_event_id', $event->id)
                        ->select('analyte_code', 'analyte_name', 'numeric_value', 'unit', 'measured_at')->get(),
                    'vital_signs' => DB::table('vital_signs')->where('patient_event_id', $event->id)
                        ->select('vital_code', 'vital_name', 'value', 'unit', 'measured_at')->get(),
                ];
            });

        return response()->json([
            'research_only' => true,
            'notice' => 'Research prototype — not a medical diagnosis or treatment system.',
            'data_scope' => 'Synthetic demonstration data only.',
            'patient' => [
                'subject_key' => $patient->subject_key,
                'demographics' => json_decode($patient->demographics ?? '{}', true),
                'dataset' => $patient->dataset_name,
                'events' => $events,
            ],
        ]);
    }

    public function runSyntheticBaseline(): JsonResponse
    {
        $startedAt = now();

        try {
            $response = Http::connectTimeout(2)->timeout(20)
                ->get(rtrim(config('services.medtwin_ai.url'), '/').'/v1/research/experiments/synthetic-baseline');
        } catch (ConnectionException) {
            return response()->json([
                'message' => 'Synthetic analysis service is unavailable. No experiment result was created.',
            ], 503);
        }

        if (! $response->successful()) {
            return response()->json([
                'message' => 'Synthetic analysis service returned an error. No experiment result was created.',
            ], 503);
        }

        $result = $response->json();
        $temporal = data_get($result, 'temporal_forecast');
        $federated = data_get($result, 'federated_aggregation');

        if (! is_array($temporal) || ! is_array($federated)) {
            return response()->json([
                'message' => 'Synthetic analysis service returned an invalid result. No experiment result was created.',
            ], 502);
        }

        $runId = DB::transaction(function () use ($result, $temporal, $federated, $startedAt): int {
            $completedAt = now();
            $runId = DB::table('experiment_runs')->insertGetId([
                'research_project_id' => DB::table('research_projects')->where('slug', 'synthetic-longitudinal-demo')->value('id'),
                'dataset_id' => DB::table('datasets')->where('name', 'MedTwin-X Generated Trajectory Fixtures')->value('id'),
                'experiment_key' => 'synthetic-temporal-and-federated-baseline-v1',
                'condition_name' => 'static-longitudinal-centralized-federated',
                'status' => 'completed',
                'protocol_snapshot' => json_encode([
                    'dataset_version' => data_get($result, 'dataset.version'),
                    'time_split' => 'first five visits for forecast; sixth visit held out',
                    'privacy_guarantee' => false,
                ]),
                'configuration' => json_encode($result),
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'created_at' => $completedAt,
                'updated_at' => $completedAt,
            ]);

            $metrics = [
                ['synthetic_static_test_rmse', data_get($temporal, 'static_baseline.test_rmse'), data_get($temporal, 'static_baseline.sample_count')],
                ['synthetic_longitudinal_test_rmse', data_get($temporal, 'longitudinal_baseline.test_rmse'), data_get($temporal, 'longitudinal_baseline.sample_count')],
                ['synthetic_centralized_test_rmse', data_get($federated, 'centralized_rmse'), data_get($federated, 'sample_count')],
                ['synthetic_federated_test_rmse', data_get($federated, 'simulated_federated_rmse'), data_get($federated, 'sample_count')],
            ];

            foreach ($metrics as [$name, $value, $sampleCount]) {
                if (! is_numeric($value) || ! is_numeric($sampleCount)) {
                    throw new \UnexpectedValueException('Experiment metrics must be numeric and have sample counts.');
                }

                DB::table('evaluation_metrics')->insert([
                    'experiment_run_id' => $runId,
                    'metric_name' => $name,
                    'metric_value' => $value,
                    'split_name' => 'synthetic-held-out',
                    'sample_count' => $sampleCount,
                    'metric_metadata' => json_encode(['unit' => 'arbitrary units RMSE', 'synthetic_only' => true]),
                    'created_at' => $completedAt,
                    'updated_at' => $completedAt,
                ]);
            }

            DB::table('audit_logs')->insert([
                'event_type' => 'synthetic_experiment.completed',
                'subject_type' => 'experiment_run',
                'subject_id' => $runId,
                'metadata' => json_encode(['experiment_key' => 'synthetic-temporal-and-federated-baseline-v1']),
                'occurred_at' => $completedAt,
                'created_at' => $completedAt,
                'updated_at' => $completedAt,
            ]);

            return $runId;
        });

        return response()->json([
            'experiment_run_id' => $runId,
            'research_only' => true,
            'notice' => 'Research prototype — not a medical diagnosis or treatment system.',
            'result' => $result,
        ], 201);
    }

    public function runSyntheticAgents(Request $request): JsonResponse
    {
        $validated = $request->validate(['patient_id' => ['required', 'integer', 'min:1']]);
        $patient = DB::table('patients_synthetic')
            ->join('datasets', 'datasets.id', '=', 'patients_synthetic.dataset_id')
            ->where('datasets.source_kind', 'synthetic')
            ->where('patients_synthetic.id', $validated['patient_id'])
            ->select('patients_synthetic.id', 'patients_synthetic.subject_key')
            ->first();

        abort_if($patient === null, 404);

        $observations = DB::table('lab_results')
            ->where('patient_id', $patient->id)
            ->where('analyte_code', 'SYNTH-MARKER-A')
            ->whereNotNull('numeric_value')
            ->orderBy('measured_at')
            ->get(['measured_at', 'numeric_value']);

        abort_if($observations->count() < 2, 422, 'At least two synthetic observations are required.');

        $payload = [
            'dataset_version' => 'MedTwin-X Generated Trajectory Fixtures v1.0.0',
            'subject_key' => $patient->subject_key,
            'signal_code' => 'SYNTH-MARKER-A',
            'unit' => 'arbitrary units',
            'observations' => $observations->map(fn (object $item): array => [
                'observed_at' => $item->measured_at,
                'value' => (float) $item->numeric_value,
            ])->all(),
        ];

        try {
            $response = Http::connectTimeout(2)->timeout(20)
                ->post(rtrim(config('services.medtwin_ai.url'), '/').'/v1/research/agents/analyze-synthetic', $payload);
        } catch (ConnectionException) {
            return response()->json(['message' => 'Synthetic analysis service is unavailable; no agent runs were stored.'], 503);
        }

        if (! $response->successful()) {
            return response()->json(['message' => 'Synthetic agent workflow failed; no agent runs were stored.'], $response->status() === 422 ? 422 : 503);
        }

        $outputs = $response->json();
        $expectedTypes = [
            'patient_data_analysis', 'medical_evidence_retrieval', 'temporal_trend',
            'risk_analysis', 'evidence_verification', 'safety_critique', 'explanation',
        ];

        if (! is_array($outputs) || count($outputs) !== count($expectedTypes)) {
            return response()->json(['message' => 'Synthetic agent workflow returned an invalid result; nothing was stored.'], 502);
        }

        foreach ($outputs as $index => $output) {
            if (! is_array($output)
                || ($output['agent_type'] ?? null) !== $expectedTypes[$index]
                || ! is_array($output['input'] ?? null)
                || ! is_array($output['tools_used'] ?? null)
                || ! is_array($output['retrieved_evidence'] ?? null)
                || ! is_array($output['structured_result'] ?? null)
                || ! in_array($output['status'] ?? null, ['completed', 'abstained', 'unavailable', 'failed'], true)) {
                return response()->json(['message' => 'Synthetic agent workflow returned an invalid result; nothing was stored.'], 502);
            }
        }

        $runIds = DB::transaction(function () use ($outputs): array {
            $now = now();
            $projectId = DB::table('research_projects')->where('slug', 'synthetic-longitudinal-demo')->value('id');
            $runIds = [];

            foreach ($outputs as $output) {
                $startedAt = isset($output['started_at']) ? date('Y-m-d H:i:s', strtotime($output['started_at'])) : $now;
                $completedAt = isset($output['completed_at']) ? date('Y-m-d H:i:s', strtotime($output['completed_at'])) : $now;
                $runId = DB::table('agent_runs')->insertGetId([
                    'research_project_id' => $projectId,
                    'agent_type' => $output['agent_type'],
                    'status' => $output['status'],
                    'input_summary' => json_encode($output['input']),
                    'tools_used' => json_encode($output['tools_used']),
                    'retrieved_evidence_ids' => json_encode(array_values(array_filter(array_map(
                        fn (array $item): mixed => $item['id'] ?? null,
                        $output['retrieved_evidence'],
                    )))),
                    'structured_result' => json_encode($output['structured_result']),
                    'confidence' => is_numeric($output['confidence'] ?? null) ? $output['confidence'] : null,
                    'errors' => json_encode($output['errors'] ?? []),
                    'started_at' => $startedAt,
                    'completed_at' => $completedAt,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $runIds[] = $runId;

                DB::table('audit_logs')->insert([
                    'event_type' => 'synthetic_agent_run.completed',
                    'subject_type' => 'agent_run',
                    'subject_id' => $runId,
                    'metadata' => json_encode(['agent_type' => $output['agent_type'], 'status' => $output['status']]),
                    'occurred_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            return $runIds;
        });

        return response()->json([
            'agent_run_ids' => $runIds,
            'research_only' => true,
            'notice' => 'Research prototype — not a medical diagnosis or treatment system.',
            'data' => $outputs,
        ], 201);
    }

    public function materializeSyntheticTwin(int $patientId): JsonResponse
    {
        $patient = DB::table('patients_synthetic')
            ->join('datasets', 'datasets.id', '=', 'patients_synthetic.dataset_id')
            ->where('datasets.source_kind', 'synthetic')
            ->where('patients_synthetic.id', $patientId)
            ->select('patients_synthetic.*')
            ->first();

        abort_if($patient === null, 404);

        $events = DB::table('patient_events')->where('patient_id', $patientId)->orderBy('occurred_at')->get();
        $laboratoryResults = DB::table('lab_results')->where('patient_id', $patientId)
            ->whereNotNull('numeric_value')->orderBy('measured_at')->get();
        $vitalSigns = DB::table('vital_signs')->where('patient_id', $patientId)->orderBy('measured_at')->get();

        $medications = [];
        $diagnoses = [];
        foreach ($events as $event) {
            $eventPayload = json_decode($event->normalized_payload ?? '{}', true) ?: [];
            $datasetField = [
                'code' => (string) ($eventPayload['code'] ?? $event->source_key ?? 'SYNTHETIC-UNSPECIFIED'),
                'dataset_label' => (string) ($eventPayload['dataset_label'] ?? $event->event_type),
                'occurred_at' => $event->occurred_at,
                'source_reference' => $event->source_key,
            ];

            if ($event->event_type === 'synthetic_medication_field') {
                $medications[] = $datasetField;
            } elseif ($event->event_type === 'synthetic_diagnosis_field') {
                $diagnoses[] = $datasetField;
            }
        }

        $payload = [
            'dataset_version' => 'MedTwin-X Generated Trajectory Fixtures v1.0.0',
            'subject_key' => $patient->subject_key,
            'demographics' => json_decode($patient->demographics ?? '{}', true) ?: [],
            'events' => $events->map(fn (object $event): array => [
                'event_type' => $event->event_type,
                'occurred_at' => $event->occurred_at,
                'source_reference' => $event->source_key,
                'fields' => json_decode($event->normalized_payload ?? '{}', true) ?: [],
            ])->all(),
            'documents' => DB::table('clinical_documents')->where('patient_id', $patientId)->get()
                ->map(fn (object $document): array => [
                    'document_type' => $document->document_type,
                    'content_checksum' => $document->content_checksum,
                    'occurred_at' => $document->documented_at,
                    'source_reference' => $document->source_reference,
                ])->all(),
            'laboratory_results' => $laboratoryResults->map(fn (object $result): array => [
                'code' => $result->analyte_code ?? 'SYNTHETIC-UNSPECIFIED',
                'label' => $result->analyte_name,
                'value' => (float) ($result->numeric_value ?? 0),
                'unit' => $result->unit ?? 'arbitrary units',
                'occurred_at' => $result->measured_at,
                'source_reference' => $result->patient_event_id ? 'synthetic-event-'.$result->patient_event_id : null,
            ])->all(),
            'vital_signs' => $vitalSigns->map(fn (object $vital): array => [
                'code' => $vital->vital_code,
                'label' => $vital->vital_name,
                'value' => (float) $vital->value,
                'unit' => $vital->unit ?? 'arbitrary units',
                'occurred_at' => $vital->measured_at,
                'source_reference' => $vital->patient_event_id ? 'synthetic-event-'.$vital->patient_event_id : null,
            ])->all(),
            'medications' => $medications,
            'diagnoses' => $diagnoses,
            'medical_images' => DB::table('medical_images')->where('patient_id', $patientId)->get()
                ->map(fn (object $image): array => [
                    'modality' => $image->modality,
                    'body_region' => $image->body_region,
                    'metadata' => ['synthetic' => true],
                    'derived_features' => json_decode($image->derived_features ?? '{}', true) ?: [],
                    'occurred_at' => $image->acquired_at,
                    'source_reference' => 'synthetic-image-'.$image->id,
                ])->all(),
            'uncertainty_notes' => $events->map(fn (object $event): ?string => $event->uncertainty
                ? json_encode(json_decode($event->uncertainty, true))
                : null)->filter()->values()->all(),
        ];

        try {
            $response = Http::connectTimeout(2)->timeout(20)
                ->post(rtrim(config('services.medtwin_ai.url'), '/').'/v1/research/digital-twin/materialize-synthetic', $payload);
        } catch (ConnectionException) {
            return response()->json(['message' => 'Synthetic analysis service is unavailable; no twin state was stored.'], 503);
        }

        if (! $response->successful() || ! is_array($response->json())) {
            return response()->json(['message' => 'Digital-twin materialization failed; no state was stored.'], 503);
        }

        $state = $response->json();
        if (($state['research_only'] ?? false) !== true
            || ($state['subject_key'] ?? null) !== $patient->subject_key
            || ! is_array($state['modality_coverage'] ?? null)
            || ! is_array($state['source_lineage'] ?? null)) {
            return response()->json(['message' => 'Digital-twin service returned an invalid state; nothing was stored.'], 502);
        }

        $now = now();
        $stateId = DB::table('digital_twin_states')->insertGetId([
            'patient_id' => $patientId,
            'as_of' => $state['as_of'] ?? null,
            'representation_version' => $state['representation_version'] ?? 'synthetic-twin-v1',
            'state' => json_encode($state),
            'modality_coverage' => json_encode($state['modality_coverage']),
            'uncertainty' => json_encode($state['uncertainty'] ?? []),
            'source_lineage' => json_encode($state['source_lineage']),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('audit_logs')->insert([
            'event_type' => 'synthetic_digital_twin.materialized',
            'subject_type' => 'digital_twin_state',
            'subject_id' => $stateId,
            'metadata' => json_encode(['patient_key' => $patient->subject_key, 'representation_version' => $state['representation_version'] ?? 'synthetic-twin-v1']),
            'occurred_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return response()->json([
            'digital_twin_state_id' => $stateId,
            'research_only' => true,
            'notice' => 'Research prototype — not a medical diagnosis or treatment system.',
            'state' => $state,
        ], 201);
    }

    public function experimentHistory(): JsonResponse
    {
        $runs = DB::table('experiment_runs')
            ->where('experiment_key', 'synthetic-temporal-and-federated-baseline-v1')
            ->orderByDesc('completed_at')
            ->limit(20)
            ->get()
            ->map(fn (object $run): array => [
                'id' => $run->id,
                'status' => $run->status,
                'completed_at' => $run->completed_at,
                'metrics' => DB::table('evaluation_metrics')
                    ->where('experiment_run_id', $run->id)
                    ->select('metric_name', 'metric_value', 'sample_count', 'split_name')
                    ->get(),
            ]);

        return response()->json(['research_only' => true, 'data' => $runs]);
    }
}
