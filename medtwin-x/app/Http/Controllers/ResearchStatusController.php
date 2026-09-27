<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ResearchStatusController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'project' => 'MedTwin-X',
            'phase' => 'synthetic-research-mvp',
            'research_only' => true,
            'notice' => 'Research prototype — not a medical diagnosis or treatment system.',
            'data_policy' => 'Use synthetic or approved de-identified data only.',
            'clinical_use' => false,
            'predictive_models_available' => false,
            'descriptive_analysis_available' => true,
            'structured_agent_workflow_available' => true,
            'medical_evidence_corpus_available' => false,
            'medical_knowledge_graph_populated' => false,
        ]);
    }
}
