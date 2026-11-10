<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController
{

    public function index(Request $request): JsonResponse
    {
        $query = Project::query()
            ->published()
            ->ordered();

        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        } elseif (! $request->boolean('all')) {
            $query->featured();
        }

        $projects = $query
            ->get([
                'id', 'title', 'slug', 'short_description', 'description',
                'category', 'technologies', 'cover_image',
                'project_url', 'github_url', 'external_url',
            ])
            ->map(fn (Project $project) => $this->transform($project));

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }

    public function transform(Project $project): array
    {
        return [
            'id' => $project->id,
            'title' => $project->title,
            'slug' => $project->slug,
            'short_description' => $project->short_description,
            'description' => $project->description,
            'category' => $project->category,
            'technologies' => $project->technologies ?? [],
            'cover_image' => $project->cover_image
                ? '/storage/' . ltrim($project->cover_image, '/')
                : null,
            'project_url' => $project->project_url,
            'github_url' => $project->github_url,
            'external_url' => $project->external_url,
        ];
    }
}

