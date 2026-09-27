<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyntheticResearchDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $projectId = DB::table('research_projects')->where('slug', 'synthetic-longitudinal-demo')->value('id');
            if (! $projectId) {
                $projectId = DB::table('research_projects')->insertGetId([
                    'name' => 'Synthetic Longitudinal Demo',
                    'slug' => 'synthetic-longitudinal-demo',
                    'description' => 'Generated measurements for software demonstration only; not clinical data.',
                    'status' => 'demo',
                    'protocol_metadata' => json_encode(['synthetic_only' => true, 'clinical_use' => false]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $datasetId = DB::table('datasets')->where('name', 'MedTwin-X Generated Trajectory Fixtures')
                ->where('source_kind', 'synthetic')->value('id');
            if (! $datasetId) {
                $datasetId = DB::table('datasets')->insertGetId([
                    'research_project_id' => $projectId,
                    'name' => 'MedTwin-X Generated Trajectory Fixtures',
                    'version' => '1.0.0',
                    'source_kind' => 'synthetic',
                    'license_or_terms' => 'Generated in-repository; synthetic demonstration data',
                    'access_status' => 'permitted',
                    'deidentification_status' => 'synthetic',
                    'permitted_modalities' => json_encode(['longitudinal_measurements']),
                    'lineage_metadata' => json_encode(['generator' => 'SyntheticResearchDemoSeeder', 'seed' => 2718]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $profiles = [
                [22, 'group-a', 31], [28, 'group-b', 46], [35, 'group-a', 27],
                [41, 'group-c', 58], [53, 'group-b', 39], [62, 'group-c', 66],
                [47, 'group-a', 34], [31, 'group-b', 51],
            ];

            foreach ($profiles as $index => [$ageBand, $cohort, $base]) {
                $subjectKey = sprintf('SYNTH-%04d', $index + 1);
                $patientId = DB::table('patients_synthetic')->where('dataset_id', $datasetId)
                    ->where('subject_key', $subjectKey)->value('id');

                if (! $patientId) {
                    $patientId = DB::table('patients_synthetic')->insertGetId([
                        'dataset_id' => $datasetId,
                        'subject_key' => $subjectKey,
                        'demographics' => json_encode(['age_band' => $ageBand, 'synthetic_group' => $cohort]),
                        'privacy_metadata' => json_encode(['synthetic' => true, 'identifiable' => false]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                for ($visit = 0; $visit < 6; $visit++) {
                    $occurredAt = now()->subMonths(15 - ($visit * 3))->startOfDay();
                    $eventKey = "demo-{$subjectKey}-visit-".($visit + 1);
                    $eventId = DB::table('patient_events')->where('patient_id', $patientId)
                        ->where('source_key', $eventKey)->value('id');
                    if (! $eventId) {
                        $eventId = DB::table('patient_events')->insertGetId([
                            'patient_id' => $patientId,
                            'event_type' => 'synthetic_measurement_visit',
                            'occurred_at' => $occurredAt,
                            'source_key' => $eventKey,
                            'normalized_payload' => json_encode(['visit_number' => $visit + 1, 'source' => 'deterministic_demo_fixture']),
                            'uncertainty' => json_encode(['measurement_noise' => 'not_simulated']),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $labExists = DB::table('lab_results')->where('patient_event_id', $eventId)
                        ->where('analyte_code', 'SYNTH-MARKER-A')->exists();
                    if (! $labExists) {
                        DB::table('lab_results')->insert([
                            'patient_id' => $patientId,
                            'patient_event_id' => $eventId,
                            'analyte_code' => 'SYNTH-MARKER-A',
                            'analyte_name' => 'Synthetic research marker A',
                            'code_system' => 'MEDTWINX-SYNTHETIC',
                            'numeric_value' => $base + (($index % 3) - 1) * $visit + (($visit % 2) * 2),
                            'text_value' => null,
                            'unit' => 'arbitrary units',
                            'measured_at' => $occurredAt,
                            'provenance' => json_encode(['synthetic' => true, 'generator_version' => '1.0.0']),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $vitalExists = DB::table('vital_signs')->where('patient_event_id', $eventId)
                        ->where('vital_code', 'SYNTH-ACTIVITY-INDEX')->exists();
                    if (! $vitalExists) {
                        DB::table('vital_signs')->insert([
                            'patient_id' => $patientId,
                            'patient_event_id' => $eventId,
                            'vital_code' => 'SYNTH-ACTIVITY-INDEX',
                            'vital_name' => 'Synthetic activity index',
                            'value' => 20 + (($index * 7 + $visit * 5) % 41),
                            'unit' => 'arbitrary units',
                            'measured_at' => $occurredAt,
                            'provenance' => json_encode(['synthetic' => true, 'generator_version' => '1.0.0']),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });
    }
}
