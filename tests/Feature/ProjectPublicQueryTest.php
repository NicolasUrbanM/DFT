<?php

namespace Tests\Feature;

use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProjectPublicQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_unpublished_projects_never_appear_publicly(): void
    {
        // Projeto publicado e destaque (deve aparecer)
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

        // Projeto em rascunho (não deve aparecer)
        Project::create([
            'title' => 'Projeto Rascunho',
            'slug' => 'projeto-rascunho',
            'short_description' => 'Descrição curta',
            'description' => 'Descrição completa',
            'category' => 'Web',
            'status' => 'draft',
            'featured' => true,
        ]);

        // Projeto arquivado (não deve aparecer)
        Project::create([
            'title' => 'Projeto Arquivado',
            'slug' => 'projeto-arquivado',
            'short_description' => 'Descrição curta',
            'description' => 'Descrição completa',
            'category' => 'Web',
            'status' => 'archived',
            'featured' => true,
        ]);

        // Projeto publicado com data futura (não deve aparecer ainda)
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
        // Três projetos publicados com o mesmo valor de order (ex: 0)
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

        // O desempate por id garante ordenação determinística
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

        // Campos públicos previstos presentes
        $expectedFields = [
            'id', 'title', 'slug', 'short_description', 'description',
            'category', 'technologies', 'cover_image',
            'project_url', 'github_url', 'external_url',
        ];

        foreach ($expectedFields as $field) {
            $this->assertArrayHasKey($field, $projectData);
        }

        // Dados internos desnecessários não devem ser vazados
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
            'id' => 999, // Não deve ser atribuído em massa
        ]);

        // Verificação dos casts
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

        // Mass assignment protegido: id não deve ter sido atribuído pelo construtor fillable
        $this->assertNull($project->id);
    }
}

