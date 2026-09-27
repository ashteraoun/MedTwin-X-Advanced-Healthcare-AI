<?php

namespace Tests\Feature;

use Database\Seeders\SyntheticResearchDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ResearchFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_research_status_endpoint_discloses_scope_and_safety_boundary(): void
    {
        $this->getJson('/api/research/status')
            ->assertOk()
            ->assertJsonPath('project', 'MedTwin-X')
            ->assertJsonPath('research_only', true)
            ->assertJsonPath('clinical_use', false)
            ->assertJsonPath('predictive_models_available', false)
            ->assertJsonPath('notice', 'Research prototype — not a medical diagnosis or treatment system.');
    }

    public function test_research_landing_page_displays_the_required_notice(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Research prototype — not a medical diagnosis or treatment system.')
            ->assertSee('Never enter identifiable patient information.');
    }

    public function test_research_domain_schema_is_migrated(): void
    {
        foreach ([
            'research_projects', 'datasets', 'patients_synthetic', 'patient_events',
            'clinical_documents', 'lab_results', 'vital_signs', 'medical_images',
            'medical_entities', 'medical_relations', 'knowledge_graph_nodes',
            'knowledge_graph_edges', 'patient_embeddings', 'patient_timeline',
            'digital_twin_states', 'agent_runs', 'evidence', 'citations',
            'risk_predictions', 'experiment_runs', 'evaluation_metrics',
            'model_versions', 'audit_logs',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table: {$table}");
        }
    }

    public function test_synthetic_demo_endpoints_return_a_longitudinal_cohort(): void
    {
        $this->seed(SyntheticResearchDemoSeeder::class);

        $this->getJson('/api/research/overview')
            ->assertOk()
            ->assertJsonPath('subjects', 8)
            ->assertJsonPath('events', 48)
            ->assertJsonPath('laboratory_observations', 48)
            ->assertJsonPath('vital_observations', 48)
            ->assertJsonPath('trained_models_available', false)
            ->assertJsonPath('experiment_runs', 0);

        $patients = $this->getJson('/api/research/patients')
            ->assertOk()
            ->assertJsonCount(8, 'data');

        $patientId = $patients->json('data.0.id');
        $this->getJson("/api/research/patients/{$patientId}")
            ->assertOk()
            ->assertJsonPath('research_only', true)
            ->assertJsonCount(6, 'patient.events')
            ->assertJsonPath('patient.events.0.laboratory_results.0.unit', 'arbitrary units');
    }

    public function test_patient_endpoint_does_not_serve_non_synthetic_records(): void
    {
        $datasetId = DB::table('datasets')->insertGetId([
            'name' => 'unapproved-test-dataset',
            'source_kind' => 'restricted',
            'access_status' => 'under_review',
            'deidentification_status' => 'not_assessed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $patientId = DB::table('patients_synthetic')->insertGetId([
            'dataset_id' => $datasetId,
            'subject_key' => 'TEST-NOT-SYNTHETIC',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson("/api/research/patients/{$patientId}")->assertNotFound();
    }

    public function test_synthetic_experiment_persists_only_returned_metrics_and_audit_record(): void
    {
        Http::fake(['*' => Http::response([
            'experiment_id' => 'synthetic-temporal-and-federated-baseline-v1',
            'dataset' => ['version' => 'MedTwin-X Generated Trajectory Fixtures v1.0.0'],
            'temporal_forecast' => [
                'static_baseline' => ['test_rmse' => 2.03, 'sample_count' => 8],
                'longitudinal_baseline' => ['test_rmse' => 1.2, 'sample_count' => 8],
            ],
            'federated_aggregation' => [
                'centralized_rmse' => 14.18,
                'simulated_federated_rmse' => 14.18,
                'sample_count' => 8,
            ],
        ])]);

        $this->postJson('/api/research/experiments/synthetic-baseline')
            ->assertCreated()
            ->assertJsonPath('research_only', true)
            ->assertJsonPath('result.temporal_forecast.longitudinal_baseline.test_rmse', 1.2);

        $this->assertDatabaseCount('experiment_runs', 1);
        $this->assertDatabaseCount('evaluation_metrics', 4);
        $this->assertDatabaseHas('evaluation_metrics', [
            'metric_name' => 'synthetic_longitudinal_test_rmse',
            'metric_value' => 1.2,
            'sample_count' => 8,
        ]);
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'synthetic_experiment.completed']);
    }

    public function test_experiment_service_failure_does_not_record_results(): void
    {
        Http::fake(['*' => Http::response(['detail' => 'unavailable'], 500)]);

        $this->postJson('/api/research/experiments/synthetic-baseline')->assertServiceUnavailable();

        $this->assertDatabaseCount('experiment_runs', 0);
        $this->assertDatabaseCount('evaluation_metrics', 0);
    }

    public function test_synthetic_agent_workflow_persists_seven_structured_runs(): void
    {
        $this->seed(SyntheticResearchDemoSeeder::class);
        $patientId = DB::table('patients_synthetic')->value('id');
        $agentTypes = [
            'patient_data_analysis', 'medical_evidence_retrieval', 'temporal_trend',
            'risk_analysis', 'evidence_verification', 'safety_critique', 'explanation',
        ];
        $outputs = array_map(fn (string $agentType): array => [
            'agent_type' => $agentType,
            'status' => in_array($agentType, ['medical_evidence_retrieval', 'risk_analysis', 'evidence_verification'], true)
                ? 'abstained'
                : 'completed',
            'input' => ['subject_key' => 'SYNTH-0001', 'dataset_version' => 'fixture-v1'],
            'tools_used' => ['validated_test_tool'],
            'retrieved_evidence' => [],
            'structured_result' => ['research_only' => true],
            'confidence' => null,
            'errors' => [],
            'started_at' => now()->toIso8601String(),
            'completed_at' => now()->toIso8601String(),
        ], $agentTypes);
        Http::fake(['*' => Http::response($outputs)]);

        $this->postJson('/api/research/agents/analyze-synthetic', ['patient_id' => $patientId])
            ->assertCreated()
            ->assertJsonCount(7, 'data')
            ->assertJsonPath('data.1.status', 'abstained')
            ->assertJsonPath('data.3.status', 'abstained');

        $this->assertDatabaseCount('agent_runs', 7);
        $this->assertDatabaseCount('audit_logs', 7);
        $storedOutput = json_decode(DB::table('agent_runs')->value('structured_result'), true);
        $this->assertArrayNotHasKey('chain_of_thought', $storedOutput);
    }

    public function test_agent_workflow_rejects_non_synthetic_subject_before_service_call(): void
    {
        $datasetId = DB::table('datasets')->insertGetId([
            'name' => 'restricted-test-dataset',
            'source_kind' => 'restricted',
            'access_status' => 'under_review',
            'deidentification_status' => 'not_assessed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $patientId = DB::table('patients_synthetic')->insertGetId([
            'dataset_id' => $datasetId,
            'subject_key' => 'TEST-RESTRICTED',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->postJson('/api/research/agents/analyze-synthetic', ['patient_id' => $patientId])->assertNotFound();
        Http::assertNothingSent();
        $this->assertDatabaseCount('agent_runs', 0);
    }

    public function test_twin_materialization_stores_synthetic_state_without_forwarding_document_text(): void
    {
        $this->seed(SyntheticResearchDemoSeeder::class);
        $patientId = DB::table('patients_synthetic')->value('id');
        DB::table('clinical_documents')->insert([
            'patient_id' => $patientId,
            'document_type' => 'synthetic_report_metadata',
            'deidentification_status' => 'synthetic',
            'deidentified_text' => 'synthetic-document-body-must-not-be-forwarded',
            'source_reference' => 'synthetic-document-reference',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Http::fake(['*' => Http::response([
            'research_only' => true,
            'representation_version' => 'synthetic-twin-v1',
            'dataset_version' => 'MedTwin-X Generated Trajectory Fixtures v1.0.0',
            'subject_key' => 'SYNTH-0001',
            'as_of' => now()->toIso8601String(),
            'demographics' => ['age_band' => '22'],
            'temporal_events' => [],
            'documents' => [['document_type' => 'synthetic_report_metadata']],
            'laboratory_results' => [],
            'vital_signs' => [],
            'medications' => [],
            'diagnoses' => [],
            'medical_images' => [],
            'modality_coverage' => ['documents' => 1],
            'missing_modalities' => ['medical_images'],
            'uncertainty' => ['synthetic fixture'],
            'source_lineage' => ['synthetic' => true],
        ])]);

        $this->postJson("/api/research/digital-twin/{$patientId}/materialize")
            ->assertCreated()
            ->assertJsonPath('research_only', true)
            ->assertJsonPath('state.representation_version', 'synthetic-twin-v1');

        $this->assertDatabaseCount('digital_twin_states', 1);
        $this->assertDatabaseHas('audit_logs', ['event_type' => 'synthetic_digital_twin.materialized']);
        Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/v1/research/digital-twin/materialize-synthetic')
            && ! str_contains($request->body(), 'synthetic-document-body-must-not-be-forwarded')
            && $request['subject_key'] === 'SYNTH-0001'
        );
    }
}
