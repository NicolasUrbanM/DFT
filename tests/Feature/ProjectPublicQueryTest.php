<?php

namespace Tests\Feature;

use App\Http\Controllers\ProjectController;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProjectPublicQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_table_is_created_in_clean_database_with_expected_columns_and_timestamps(): void
    {
        $this->assertTrue(Schema::hasTable('projects'));

        $columns = [
            'id', 'title', 'slug', 'short_description', 'description',
            'category', 'cover_image', 'gallery', 'status', 'featured',
            'order', 'external_url', 'project_url', 'github_url',
            'meta_title', 'meta_description', 'og_image', 'published_at',
            'created_at', 'updated_at',
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('projects', $column),
                "A coluna {$column} não foi encontrada na tabela projects."
            );
        }

        $project = Project::create([
            'title' => 'Projeto Timestamps',
            'slug' => 'projeto-timestamps',
            'short_description' => 'Resumo',
            'description' => 'Descrição',
            'category' => 'Web',
            'status' => 'draft',
        ]);

        $this->assertNotNull($project->created_at);
        $this->assertNotNull($project->updated_at);
        $this->assertInstanceOf(Carbon::class, $project->created_at);
        $this->assertInstanceOf(Carbon::class, $project->updated_at);
    }

    public function test_project_controller_transform_rules_are_separated_and_testable(): void
    {
        $controller = new ProjectController();

        $project = new Project([
            'title' => 'Projeto Teste Isolado',
            'slug' => 'projeto-teste-isolado',
            'short_description' => 'Curta',
            'description' => 'Completa',
            'category' => 'Web',
            'technologies' => ['React', 'PHP'],
            'cover_image' => 'projects/capa.png',
            'project_url' => 'https://app.com',
            'github_url' => 'https://github.com/app',
            'external_url' => 'https://externo.com',
            'management_status' => 'planning',
            'priority' => 'urgent',
            'manager_id' => 1,
            'progress' => 85,
            'start_date' => '2026-01-01',
            'due_date' => '2026-03-01',
            'completed_at' => '2026-03-01 12:00:00',
            'meta_title' => 'SEO Secret',
        ]);
        $project->id = 42;

        $payload = $controller->transform($project);

        $this->assertSame(42, $payload['id']);
        $this->assertSame('Projeto Teste Isolado', $payload['title']);
        $this->assertSame('/storage/projects/capa.png', $payload['cover_image']);
        $this->assertSame(['React', 'PHP'], $payload['technologies']);

        $this->assertArrayNotHasKey('management_status', $payload);
        $this->assertArrayNotHasKey('priority', $payload);
        $this->assertArrayNotHasKey('manager_id', $payload);
        $this->assertArrayNotHasKey('progress', $payload);
        $this->assertArrayNotHasKey('start_date', $payload);
        $this->assertArrayNotHasKey('due_date', $payload);
        $this->assertArrayNotHasKey('completed_at', $payload);
        $this->assertArrayNotHasKey('meta_title', $payload);
        $this->assertArrayNotHasKey('created_at', $payload);
        $this->assertArrayNotHasKey('updated_at', $payload);
    }

    public function test_unpublished_projects_never_appear_publicly(): void
    {
        $published = Project::create([
            'title' => 'Projeto Publicado',
            'slug' => 'projeto-publicado',
            'short_description' => 'Descrição curta',
            'description' => 'Descrição completa',
            'category' => 'Web',
            'status' => 'published',
            'featured' => true,
            'published_at' => now()->subDay(),
        ]);

        Project::create([
            'title' => 'Projeto Rascunho',
            'slug' => 'projeto-rascunho',
            'short_description' => 'Descrição curta',
            'description' => 'Descrição completa',
            'category' => 'Web',
            'status' => 'draft',
            'featured' => true,
        ]);

        Project::create([
            'title' => 'Projeto Arquivado',
            'slug' => 'projeto-arquivado',
            'short_description' => 'Descrição curta',
            'description' => 'Descrição completa',
            'category' => 'Web',
            'status' => 'archived',
            'featured' => true,
        ]);

        Project::create([
            'title' => 'Projeto Futuro',
            'slug' => 'projeto-futuro',
            'short_description' => 'Descrição curta',
            'description' => 'Descrição completa',
            'category' => 'Web',
            'status' => 'published',
            'featured' => true,
            'published_at' => now()->addDays(5),
        ]);

        $response = $this->getJson('/api/projects');

        $response->assertOk();
        $data = $response->json('data');

        $this->assertCount(1, $data);
        $this->assertSame($published->id, $data[0]['id']);
        $this->assertSame('Projeto Publicado', $data[0]['title']);
    }

    public function test_ordering_is_deterministic_even_with_equal_order(): void
    {
        $p1 = Project::create([
            'title' => 'Projeto 1',
            'slug' => 'projeto-1',
            'short_description' => 'Desc',
            'description' => 'Desc',
            'category' => 'Web',
            'status' => 'published',
            'featured' => true,
            'order' => 0,
        ]);

        $p2 = Project::create([
            'title' => 'Projeto 2',
            'slug' => 'projeto-2',
            'short_description' => 'Desc',
            'description' => 'Desc',
            'category' => 'Web',
            'status' => 'published',
            'featured' => true,
            'order' => 0,
        ]);

        $p3 = Project::create([
            'title' => 'Projeto 3',
            'slug' => 'projeto-3',
            'short_description' => 'Desc',
            'description' => 'Desc',
            'category' => 'Web',
            'status' => 'published',
            'featured' => true,
            'order' => 0,
        ]);

        $response = $this->getJson('/api/projects');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title')->all();

        $this->assertSame(['Projeto 1', 'Projeto 2', 'Projeto 3'], $titles);
    }

    public function test_query_avoids_unnecessary_data(): void
    {
        Project::create([
            'title' => 'Projeto Otimizado',
            'slug' => 'projeto-otimizado',
            'short_description' => 'Desc curta',
            'description' => 'Desc detalhada',
            'category' => 'SaaS',
            'technologies' => ['React', 'Laravel'],
            'cover_image' => 'projects/capa.jpg',
            'status' => 'published',
            'featured' => true,
            'order' => 1,
            'project_url' => 'https://app.exemplo.com',
            'github_url' => 'https://github.com/exemplo/repo',
            'external_url' => 'https://exemplo.com',
            'meta_title' => 'SEO Title',
            'meta_description' => 'SEO Description',
            'og_image' => 'projects/og.jpg',
            'management_status' => 'planning',
            'priority' => 'high',
            'progress' => 50,
        ]);

        $response = $this->getJson('/api/projects');

        $response->assertOk();
        $projectData = $response->json('data.0');

        $expectedFields = [
            'id', 'title', 'slug', 'short_description', 'description',
            'category', 'technologies', 'cover_image',
            'project_url', 'github_url', 'external_url',
        ];

        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $projectData);
        }

        $forbiddenFields = [
            'management_status', 'priority', 'manager_id', 'progress',
            'start_date', 'due_date', 'completed_at',
            'meta_title', 'meta_description', 'og_image',
            'created_at', 'updated_at',
        ];

        foreach ($forbiddenFields as $field) {
            $this->assertArrayNotHasKey($field, $projectData);
        }
    }

    public function test_model_casts_and_mass_assignment_protection(): void
    {
        $project = new Project([
            'title' => 'Projeto Teste',
            'slug' => 'projeto-teste',
            'short_description' => 'Desc',
            'description' => 'Desc',
            'category' => 'Web',
            'featured' => '1',
            'order' => '10',
            'published_at' => '2026-10-01 12:00:00',
            'start_date' => '2026-10-01',
            'due_date' => '2026-10-15',
            'completed_at' => '2026-10-15 18:30:00',
            'progress' => '80',
            'technologies' => ['PHP', 'Vue'],
            'id' => 999,
        ]);

        $this->assertIsBool($project->featured);
        $this->assertTrue($project->featured);

        $this->assertIsInt($project->order);
        $this->assertSame(10, $project->order);

        $this->assertIsInt($project->progress);
        $this->assertSame(80, $project->progress);

        $this->assertInstanceOf(Carbon::class, $project->published_at);
        $this->assertInstanceOf(Carbon::class, $project->start_date);
        $this->assertInstanceOf(Carbon::class, $project->due_date);
        $this->assertInstanceOf(Carbon::class, $project->completed_at);

        $this->assertIsArray($project->technologies);

        $this->assertNull($project->id);
    }
}
