<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\ActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProjectActivityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authorized_user_can_view_project_activity()
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'created_by' => $user->id,
        ]);

        ActivityLog::factory()->create([
            'project_id' => $project->id,
            'user_id' => $user->id,
            'action' => 'task_created',
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/projects/{$project->id}/activity");

        $response->assertOk();
    }

    /** @test */
    public function unauthorized_user_cannot_view_project_activity()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson("/projects/{$project->id}/activity");

        $response->assertForbidden();
    }

    /** @test */
    public function activity_can_be_filtered_by_action()
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'created_by' => $user->id,
        ]);

        ActivityLog::factory()->create([
            'project_id' => $project->id,
            'action' => 'task_created',
        ]);

        ActivityLog::factory()->create([
            'project_id' => $project->id,
            'action' => 'task_completed',
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/projects/{$project->id}/activity?action=task_completed");

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function activity_is_paginated()
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'created_by' => $user->id,
        ]);

        ActivityLog::factory()->count(25)->create([
            'project_id' => $project->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->getJson("/projects/{$project->id}/activity");

        $response
            ->assertOk()
            ->assertJsonPath('meta.per_page', 20);
    }
}
