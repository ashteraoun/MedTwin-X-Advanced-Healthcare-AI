<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('status', 32)->default('draft');
            $table->json('protocol_metadata')->nullable();
            $table->timestamps();
        });
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('version', 80)->nullable();
            $table->string('source_kind', 32);
            $table->string('license_or_terms', 255)->nullable();
            $table->string('access_status', 32)->default('under_review');
            $table->string('deidentification_status', 32)->default('not_assessed');
            $table->json('permitted_modalities')->nullable();
            $table->json('lineage_metadata')->nullable();
            $table->text('source_url')->nullable();
            $table->string('content_checksum', 128)->nullable();
            $table->timestamps();
            $table->index(['source_kind', 'access_status']);
        });
        Schema::create('patients_synthetic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->constrained()->cascadeOnDelete();
            $table->string('subject_key', 120);
            $table->unsignedSmallInteger('birth_year')->nullable();
            $table->string('sex_at_birth', 32)->nullable();
            $table->json('demographics')->nullable();
            $table->json('privacy_metadata')->nullable();
            $table->timestamps();
            $table->unique(['dataset_id', 'subject_key']);
        });
        Schema::create('patient_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients_synthetic')->cascadeOnDelete();
            $table->string('event_type', 80);
            $table->dateTime('occurred_at')->nullable();
            $table->string('source_key', 160)->nullable();
            $table->json('normalized_payload')->nullable();
            $table->json('uncertainty')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'occurred_at']);
        });
        Schema::create('clinical_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients_synthetic')->nullOnDelete();
            $table->string('document_type', 80);
            $table->dateTime('documented_at')->nullable();
            $table->string('deidentification_status', 32)->default('not_assessed');
            $table->string('content_checksum', 128)->nullable();
            $table->longText('deidentified_text')->nullable();
            $table->text('source_reference')->nullable();
            $table->json('lineage_metadata')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'documented_at']);
        });
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients_synthetic')->cascadeOnDelete();
            $table->foreignId('patient_event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('analyte_code', 120)->nullable();
            $table->string('analyte_name', 180);
            $table->string('code_system', 80)->nullable();
            $table->decimal('numeric_value', 18, 6)->nullable();
            $table->string('text_value', 255)->nullable();
            $table->string('unit', 80)->nullable();
            $table->decimal('reference_low', 18, 6)->nullable();
            $table->decimal('reference_high', 18, 6)->nullable();
            $table->dateTime('measured_at')->nullable();
            $table->json('provenance')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'analyte_code', 'measured_at'], 'lab_patient_analyte_time_idx');
        });
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients_synthetic')->cascadeOnDelete();
            $table->foreignId('patient_event_id')->nullable()->constrained()->nullOnDelete();
            $table->string('vital_code', 100);
            $table->string('vital_name', 160);
            $table->decimal('value', 18, 6);
            $table->string('unit', 80)->nullable();
            $table->dateTime('measured_at')->nullable();
            $table->json('provenance')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'vital_code', 'measured_at'], 'vital_patient_code_time_idx');
        });
        Schema::create('medical_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients_synthetic')->cascadeOnDelete();
            $table->foreignId('dataset_id')->nullable()->constrained()->nullOnDelete();
            $table->string('modality', 40);
            $table->string('body_region', 100)->nullable();
            $table->dateTime('acquired_at')->nullable();
            $table->text('permitted_object_reference')->nullable();
            $table->json('derived_features')->nullable();
            $table->string('feature_extractor_version', 120)->nullable();
            $table->string('content_checksum', 128)->nullable();
            $table->json('provenance')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'acquired_at']);
        });
        Schema::create('medical_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->nullable()->constrained()->nullOnDelete();
            $table->string('entity_type', 80);
            $table->string('label', 255);
            $table->string('ontology', 100)->nullable();
            $table->string('ontology_code', 160)->nullable();
            $table->string('normalized_label', 255)->nullable();
            $table->json('attributes')->nullable();
            $table->timestamps();
            $table->index(['ontology', 'ontology_code']);
        });
        Schema::create('medical_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_entity_id')->constrained('medical_entities')->cascadeOnDelete();
            $table->foreignId('target_entity_id')->constrained('medical_entities')->cascadeOnDelete();
            $table->string('relation_type', 100);
            $table->string('source_reference', 255)->nullable();
            $table->decimal('confidence', 6, 5)->nullable();
            $table->json('provenance')->nullable();
            $table->timestamps();
            $table->index(['source_entity_id', 'relation_type']);
        });
        Schema::create('knowledge_graph_nodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_entity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('node_type', 80);
            $table->string('ontology', 100)->nullable();
            $table->string('ontology_code', 160)->nullable();
            $table->string('label', 255);
            $table->json('attributes')->nullable();
            $table->timestamps();
            $table->index(['ontology', 'ontology_code']);
        });
        Schema::create('evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dataset_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source_type', 60);
            $table->string('title', 500)->nullable();
            $table->text('source_url')->nullable();
            $table->string('source_version', 120)->nullable();
            $table->date('published_on')->nullable();
            $table->dateTime('retrieved_at')->nullable();
            $table->string('content_checksum', 128)->nullable();
            $table->text('permitted_excerpt')->nullable();
            $table->json('provenance')->nullable();
            $table->timestamps();
            $table->index(['source_type', 'published_on']);
        });
        Schema::create('knowledge_graph_edges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_node_id')->constrained('knowledge_graph_nodes')->cascadeOnDelete();
            $table->foreignId('target_node_id')->constrained('knowledge_graph_nodes')->cascadeOnDelete();
            $table->foreignId('evidence_id')->nullable()->constrained()->nullOnDelete();
            $table->string('relation_type', 100);
            $table->decimal('confidence', 6, 5)->nullable();
            $table->json('provenance')->nullable();
            $table->timestamps();
            $table->index(['source_node_id', 'relation_type']);
        });
        Schema::create('model_versions', function (Blueprint $table) {
            $table->id();
            $table->string('model_name', 160);
            $table->string('version', 100);
            $table->string('task', 100);
            $table->string('artifact_checksum', 128)->nullable();
            $table->string('code_revision', 80)->nullable();
            $table->json('configuration')->nullable();
            $table->json('training_data_lineage')->nullable();
            $table->text('model_card_url')->nullable();
            $table->timestamps();
            $table->unique(['model_name', 'version']);
        });
        Schema::create('patient_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients_synthetic')->cascadeOnDelete();
            $table->foreignId('model_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('embedding_type', 80);
            $table->string('vector_store', 100)->nullable();
            $table->string('vector_reference', 255)->nullable();
            $table->json('embedding_json')->nullable();
            $table->unsignedInteger('dimensions')->nullable();
            $table->json('source_lineage')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'embedding_type']);
        });
        Schema::create('patient_timeline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients_synthetic')->cascadeOnDelete();
            $table->string('source_type', 80);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->dateTime('occurred_at')->nullable();
            $table->json('timeline_payload')->nullable();
            $table->json('uncertainty')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'occurred_at']);
            $table->index(['source_type', 'source_id']);
        });
        Schema::create('digital_twin_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients_synthetic')->cascadeOnDelete();
            $table->dateTime('as_of')->nullable();
            $table->string('representation_version', 100);
            $table->json('state')->nullable();
            $table->json('modality_coverage')->nullable();
            $table->json('uncertainty')->nullable();
            $table->json('source_lineage')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'as_of']);
        });
        Schema::create('agent_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('model_version_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_run_id')->nullable()->constrained('agent_runs')->nullOnDelete();
            $table->string('agent_type', 100);
            $table->string('status', 32)->default('queued');
            $table->json('input_summary')->nullable();
            $table->json('tools_used')->nullable();
            $table->json('retrieved_evidence_ids')->nullable();
            $table->json('structured_result')->nullable();
            $table->decimal('confidence', 6, 5)->nullable();
            $table->json('errors')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->index(['research_project_id', 'agent_type', 'status']);
        });
        Schema::create('citations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_run_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('evidence_id')->constrained()->cascadeOnDelete();
            $table->string('statement_key', 160)->nullable();
            $table->decimal('relevance_score', 6, 5)->nullable();
            $table->string('verification_status', 32)->default('unverified');
            $table->json('verification_metadata')->nullable();
            $table->timestamps();
            $table->index(['agent_run_id', 'verification_status']);
        });
        Schema::create('risk_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients_synthetic')->cascadeOnDelete();
            $table->foreignId('model_version_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('agent_run_id')->nullable()->constrained()->nullOnDelete();
            $table->string('research_target', 160);
            $table->unsignedInteger('horizon_days')->nullable();
            $table->decimal('score', 10, 8)->nullable();
            $table->json('uncertainty')->nullable();
            $table->json('evidence_citation_ids')->nullable();
            $table->dateTime('predicted_at')->nullable();
            $table->timestamps();
            $table->index(['patient_id', 'research_target', 'predicted_at']);
        });
        Schema::create('experiment_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('dataset_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('model_version_id')->nullable()->constrained()->nullOnDelete();
            $table->string('experiment_key', 160);
            $table->string('condition_name', 160)->nullable();
            $table->string('status', 32)->default('planned');
            $table->json('protocol_snapshot')->nullable();
            $table->json('configuration')->nullable();
            $table->string('code_revision', 80)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->index(['research_project_id', 'experiment_key', 'status']);
        });
        Schema::create('evaluation_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experiment_run_id')->constrained()->cascadeOnDelete();
            $table->string('metric_name', 120);
            $table->decimal('metric_value', 20, 10);
            $table->string('split_name', 100)->nullable();
            $table->string('subgroup', 160)->nullable();
            $table->json('uncertainty_interval')->nullable();
            $table->unsignedInteger('sample_count')->nullable();
            $table->json('metric_metadata')->nullable();
            $table->timestamps();
            $table->index(['experiment_run_id', 'metric_name', 'split_name']);
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_type', 120);
            $table->string('subject_type', 120)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('correlation_id', 120)->nullable();
            $table->json('metadata')->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
            $table->index(['event_type', 'occurred_at']);
        });
    }

    public function down(): void
    {
        foreach ([
            'audit_logs', 'evaluation_metrics', 'experiment_runs', 'risk_predictions',
            'citations', 'agent_runs', 'digital_twin_states', 'patient_timeline',
            'patient_embeddings', 'model_versions', 'knowledge_graph_edges', 'evidence',
            'knowledge_graph_nodes', 'medical_relations', 'medical_entities', 'medical_images',
            'vital_signs', 'lab_results', 'clinical_documents', 'patient_events',
            'patients_synthetic', 'datasets', 'research_projects',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
