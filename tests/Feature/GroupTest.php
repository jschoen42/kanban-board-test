<?php

use App\Models\Group;
use App\Models\Task;
use App\Livewire\Kanban\Group as GroupComponent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\actingAs;

// beforeEach(function () {
//     $user = User::factory()
//         ->create();
//     actingAs($user);
// });

it('it shows all tasks from a group', function () {
    $user = User::factory()
        ->create();
    $group = Group::factory()->for($user)->create();
    Task::factory(3)
        ->state(new Sequence(
            ['description' => 'Task 1'],
            ['description' => 'Task 2'],
            ['description' => 'Task 3']
        ))
        ->for($group)
        ->create();

    Livewire::actingAs($user)->test(GroupComponent::class, ['group' => $group])
        ->assertSeeText([
            'Task 1',
            'Task 2',
            'Task 3',
        ]);
});

it('only shows tasks from the logged in user', function (){
    $user = User::factory()
        ->create();

    $stranger = User::factory()
        ->create();

    actingAs($stranger);

    $group = Group::factory()->for($user)->create();
    Task::factory(3)
        ->state(new Sequence(
            ['description' => 'Task 1'],
            ['description' => 'Task 2'],
            ['description' => 'Task 3']
        ))
        ->for($group)
        ->create();
    Livewire::actingAs($stranger)->test(GroupComponent::class, ['group' => $group])
        ->assertDontSeeText([
            'Task 1',
            'Task 2',
            'Task 3',
        ]);
});

it('shows tasks in order', function () {
    $group = Group::factory()->create();
    Task::factory(3)
        ->state(new Sequence(
            ['sort' => 1, 'description' => 'Task 2'],
            ['sort' => 0, 'description' => 'Task 1'],
            ['sort' => 2, 'description' => 'Task 3']
        ))
        ->for($group)
        ->create();

    Livewire::test(GroupComponent::class, ['group' => $group])
        ->assertSeeTextInOrder([
            'Task 1',
            'Task 2',
            'Task 3',
        ]);
});

it('can move task to target position', function () {
    $group = Group::factory()->create();
    Task::factory(3)
        ->state(new Sequence(
            ['sort' => 0],
            ['sort' => 1],
            ['sort' => 2]
        ))
        ->for($group)
        ->create();

    Livewire::test(GroupComponent::class, ['group' => $group])
        ->call('sort', 1, 2);

    expect($group->tasks)
       ->find(1)->sort->toBe(2);
});

it('sort tasks after dragging down', function () {
    $group = Group::factory()->create();
    Task::factory(3)
        ->state(new Sequence(
            ['sort' => 0],
            ['sort' => 1],
            ['sort' => 2]
        ))
        ->for($group)
        ->create();

    Livewire::test(GroupComponent::class, ['group' => $group])
        ->call('sort', 1, 2);

    $group->refresh();

    expect($group->tasks)
       ->find(2)->sort->toBe(0)
       ->find(3)->sort->toBe(1);
});

it('sort tasks after dragging up', function () {
    $group = Group::factory()->create();
    Task::factory(3)
        ->state(new Sequence(
            ['sort' => 0],
            ['sort' => 1],
            ['sort' => 2]
        ))
        ->for($group)
        ->create();

    Livewire::test(GroupComponent::class, ['group' => $group])
        ->call('sort', 3, 0);

    $group->refresh();

    expect($group->tasks)
       ->find(1)->sort->toBe(1)
       ->find(2)->sort->toBe(2);
});
