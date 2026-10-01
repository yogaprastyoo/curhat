<?php

use App\Models\CounselingSchedule;
use App\Models\Curhat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student dashboard only counts the student own curhats', function () {
    $student = User::factory()->student()->create();
    Curhat::factory()->for($student, 'student')->count(2)->create();
    Curhat::factory()->for($student, 'student')->status('completed')->create();
    Curhat::factory()->count(5)->create();

    $this->actingAs($student)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('totalCurhat', 3)
        ->assertViewHas('waiting', 2)
        ->assertViewHas('completed', 1);
});

test('counselor dashboard only counts assigned curhats', function () {
    $counselor = User::factory()->counselor()->create();
    Curhat::factory()->assignedTo($counselor)->count(2)->create();
    $reviewed = Curhat::factory()->assignedTo($counselor)->status('reviewed')->create();
    Curhat::factory()->assignedTo(User::factory()->counselor()->create())->count(3)->create();

    CounselingSchedule::create([
        'curhat_id' => $reviewed->id,
        'counselor_id' => $counselor->id,
        'date' => today(),
        'start_time' => '10:00',
    ]);

    $this->actingAs($counselor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertViewIs('dashboard-counselor')
        ->assertViewHas('new', 2)
        ->assertViewHas('needResponse', 1)
        ->assertViewHas('todaySchedule', 1);
});

test('sidebar menu follows the user role', function () {
    $this->actingAs(User::factory()->student()->create())
        ->get(route('dashboard'))
        ->assertSee('Curhat Baru')
        ->assertDontSee('Curhat Ditugaskan');

    $this->actingAs(User::factory()->counselor()->create())
        ->get(route('dashboard'))
        ->assertSee('Curhat Ditugaskan')
        ->assertDontSee('Jadwal Saya');
});

test('user can log out from the navbar', function () {
    $this->actingAs(User::factory()->student()->create())
        ->post(route('logout'))
        ->assertRedirect('/login');

    $this->assertGuest();
});
