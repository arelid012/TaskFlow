<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
            'task_id' => null,
            'action' => 'test',
            'description' => $this->faker->sentence,
        ];
    }
}
