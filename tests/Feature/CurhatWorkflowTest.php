<?php

use App\Models\CounselingSchedule;
use App\Models\Curhat;
use App\Models\CurhatCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student can submit a curhat and it is recorded in history', function () {
    $student = User::factory()->student()->create();
    $category = CurhatCategory::factory()->create();

    $response = $this->actingAs($student)->post(route('curhats.store'), [
        'category_id' => $category->id,
        'title' => 'Sulit fokus',
        'message' => 'Saya sulit fokus belajar.',
        'privacy_scope' => 'counselor',
        'counseling_request' => 'no',
    ]);

    $curhat = Curhat::sole();
    $response->assertRedirect(route('curhats.show', $curhat));

    expect($curhat->student_id)->toBe($student->id)
        ->and($curhat->status)->toBe('submitted')
        ->and($curhat->histories()->where('action', 'created')->exists())->toBeTrue();
});

test('submitting a curhat with invalid input shows field errors', function () {
    $student = User::factory()->student()->create();

    $this->actingAs($student)
        ->post(route('curhats.store'), ['title' => ''])
        ->assertSessionHasErrors(['category_id', 'title', 'message', 'privacy_scope', 'counseling_request']);

    expect(Curhat::count())->toBe(0);
});

test('counselor cannot open the create curhat form', function () {
    $this->actingAs(User::factory()->counselor()->create())
        ->get(route('curhats.create'))
        ->assertForbidden();
});

test('guests are redirected to login', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('curhats.index'))->assertRedirect(route('login'));
});

test('student only sees their own curhats in the list', function () {
    $student = User::factory()->student()->create();
    $own = Curhat::factory()->for($student, 'student')->create();
    $other = Curhat::factory()->create();

    $this->actingAs($student)
        ->get(route('curhats.index'))
        ->assertOk()
        ->assertSee($own->curhat_number)
        ->assertDontSee($other->curhat_number);
});

test('counselor listing does not expose the curhat title or message', function () {
    $counselor = User::factory()->counselor()->create();
    $curhat = Curhat::factory()->assignedTo($counselor)->create([
        'title' => 'Judul rahasia',
        'message' => 'Isi rahasia',
    ]);

    $this->actingAs($counselor)
        ->get(route('curhats.index'))
        ->assertOk()
        ->assertSee($curhat->curhat_number)
        ->assertDontSee('Judul rahasia')
        ->assertDontSee('Isi rahasia');
});

test('counselor cannot open a curhat that is not assigned to them', function () {
    $counselor = User::factory()->counselor()->create();
    $curhat = Curhat::factory()->assignedTo(User::factory()->counselor()->create())->create();

    $this->actingAs($counselor)
        ->get(route('curhats.show', $curhat))
        ->assertForbidden();
});

test('opening a curhat detail writes an access log', function () {
    $counselor = User::factory()->counselor()->create();
    $curhat = Curhat::factory()->assignedTo($counselor)->create();

    $this->actingAs($counselor)->get(route('curhats.show', $curhat))->assertOk();

    $this->assertDatabaseHas('curhat_access_logs', [
        'curhat_id' => $curhat->id,
        'user_id' => $counselor->id,
        'action' => 'view',
    ]);
});

test('counselor can move a curhat to an allowed status', function () {
    $counselor = User::factory()->counselor()->create();
    $curhat = Curhat::factory()->assignedTo($counselor)->create();

    $this->actingAs($counselor)
        ->post(route('curhats.status', $curhat), ['status' => 'reviewed', 'note' => 'Sudah dibaca'])
        ->assertSessionHasNoErrors();

    $curhat->refresh();
    expect($curhat->status)->toBe('reviewed')
        ->and($curhat->reviewed_at)->not->toBeNull();

    $this->assertDatabaseHas('curhat_histories', [
        'curhat_id' => $curhat->id,
        'action' => 'status_changed',
        'status' => 'reviewed',
        'note' => 'Sudah dibaca',
    ]);
});

test('counselor cannot skip the status workflow', function (string $from, string $to) {
    $counselor = User::factory()->counselor()->create();
    $curhat = Curhat::factory()->assignedTo($counselor)->status($from)->create();

    $this->actingAs($counselor)
        ->post(route('curhats.status', $curhat), ['status' => $to])
        ->assertSessionHasErrors('status');

    expect($curhat->refresh()->status)->toBe($from);
})->with([
    'submitted to completed' => ['submitted', 'completed'],
    'submitted to responded' => ['submitted', 'responded'],
    'completed to reviewed' => ['completed', 'reviewed'],
]);

test('student cannot change the status of their own curhat', function () {
    $student = User::factory()->student()->create();
    $curhat = Curhat::factory()->for($student, 'student')->create();

    $this->actingAs($student)
        ->post(route('curhats.status', $curhat), ['status' => 'reviewed'])
        ->assertForbidden();
});

test('counselor reply marks a reviewed curhat as responded', function () {
    $counselor = User::factory()->counselor()->create();
    $curhat = Curhat::factory()->assignedTo($counselor)->status('reviewed')->create();

    $this->actingAs($counselor)
        ->post(route('curhats.messages.store', $curhat), ['message' => 'Terima kasih sudah bercerita.']);

    expect($curhat->refresh()->status)->toBe('responded');
    $this->assertDatabaseHas('curhat_histories', ['curhat_id' => $curhat->id, 'status' => 'responded']);
});

test('counselor reply does not skip review on a submitted curhat', function () {
    $counselor = User::factory()->counselor()->create();
    $curhat = Curhat::factory()->assignedTo($counselor)->create();

    $this->actingAs($counselor)
        ->post(route('curhats.messages.store', $curhat), ['message' => 'Halo.']);

    expect($curhat->refresh()->status)->toBe('submitted')
        ->and($curhat->messages()->count())->toBe(1);
});

test('counselor can schedule a reviewed curhat', function () {
    $counselor = User::factory()->counselor()->create();
    $curhat = Curhat::factory()->assignedTo($counselor)->status('reviewed')->create();

    $this->actingAs($counselor)
        ->post(route('curhats.schedules.store', $curhat), [
            'date' => today()->addDay()->toDateString(),
            'start_time' => '10:00',
            'location' => 'Ruang BK',
        ])
        ->assertSessionHasNoErrors();

    expect($curhat->refresh()->status)->toBe('scheduled')
        ->and($curhat->schedules()->count())->toBe(1);
    $this->assertDatabaseHas('curhat_histories', ['curhat_id' => $curhat->id, 'action' => 'scheduled']);
});

test('counselor can add another schedule to an already scheduled curhat', function () {
    $counselor = User::factory()->counselor()->create();
    $curhat = Curhat::factory()->assignedTo($counselor)->status('scheduled')->create();

    $this->actingAs($counselor)
        ->post(route('curhats.schedules.store', $curhat), [
            'date' => today()->addDays(7)->toDateString(),
            'start_time' => '09:30',
        ])
        ->assertSessionHasNoErrors();

    expect($curhat->refresh()->status)->toBe('scheduled')
        ->and($curhat->schedules()->count())->toBe(1);
});

test('counselor cannot schedule a curhat that has not been reviewed', function () {
    $counselor = User::factory()->counselor()->create();
    $curhat = Curhat::factory()->assignedTo($counselor)->create();

    $this->actingAs($counselor)
        ->post(route('curhats.schedules.store', $curhat), [
            'date' => today()->addDay()->toDateString(),
            'start_time' => '10:00',
        ])
        ->assertSessionHasErrors('date');

    expect($curhat->refresh()->status)->toBe('submitted')
        ->and(CounselingSchedule::count())->toBe(0);
});

test('student sees schedules for their own curhat only', function () {
    $student = User::factory()->student()->create();
    $counselor = User::factory()->counselor()->create();
    $own = Curhat::factory()->for($student, 'student')->assignedTo($counselor)->status('scheduled')->create();
    $other = Curhat::factory()->assignedTo($counselor)->status('scheduled')->create();

    foreach ([$own, $other] as $curhat) {
        CounselingSchedule::create([
            'curhat_id' => $curhat->id,
            'counselor_id' => $counselor->id,
            'date' => today()->addDay(),
            'start_time' => '10:00',
        ]);
    }

    $this->actingAs($student)
        ->get(route('schedule'))
        ->assertOk()
        ->assertSee($own->curhat_number)
        ->assertDontSee($other->curhat_number);
});

test('internal status notes are hidden from the student', function () {
    $student = User::factory()->student()->create();
    $counselor = User::factory()->counselor()->create();
    $curhat = Curhat::factory()->for($student, 'student')->assignedTo($counselor)->create();
    $curhat->histories()->create([
        'user_id' => $counselor->id,
        'action' => 'status_changed',
        'status' => 'reviewed',
        'note' => 'Catatan internal pendamping',
    ]);

    $this->actingAs($student)
        ->get(route('curhats.show', $curhat))
        ->assertOk()
        ->assertDontSee('Catatan internal pendamping');

    $this->actingAs($counselor)
        ->get(route('curhats.show', $curhat))
        ->assertSee('Catatan internal pendamping');
});
