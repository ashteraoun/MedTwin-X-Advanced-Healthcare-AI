<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ResearchProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ResearchProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', ResearchProject::class);

        $projects = ResearchProject::query()
            ->where('owner_id', $request->user()->id)
            ->orWhere(fn ($query) => $query->whereNull('owner_id')->where('status', 'demo'))
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $projects]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', ResearchProject::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'alpha_dash', 'max:255', Rule::unique('research_projects', 'slug')],
            'description' => ['nullable', 'string', 'max:5000'],
            'protocol_metadata' => ['nullable', 'array'],
        ]);

        $project = ResearchProject::query()->create([
            ...$validated,
            'owner_id' => $request->user()->id,
            'status' => 'draft',
        ]);

        DB::table('audit_logs')->insert([
            'user_id' => $request->user()->id,
            'event_type' => 'research_project.created',
            'subject_type' => ResearchProject::class,
            'subject_id' => $project->id,
            'metadata' => json_encode(['slug' => $project->slug]),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['data' => $project], 201);
    }

    public function show(ResearchProject $researchProject): JsonResponse
    {
        $this->authorize('view', $researchProject);

        return response()->json(['data' => $researchProject]);
    }
}
