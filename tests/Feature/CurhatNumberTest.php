<?php

use App\Models\Curhat;
use App\Models\CurhatCategory;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

uses(RefreshDatabase::class);

function createCurhat(array $attributes = []): Curhat
{
    $category = CurhatCategory::forceCreate(['name' => 'Pribadi']);

    return Curhat::create([
        'student_id' => User::factory()->create()->id,
        'category_id' => $category->id,
        'title' => 'Judul',
        'message' => 'Isi curhat',
        ...$attributes,
    ]);
}

test('curhat numbers are sequential per year and contain no student data', function () {
    Date::setTestNow('2026-03-01');

    $first = createCurhat();
    $second = createCurhat();

    expect($first->curhat_number)->toBe('CR-2026-000001')
        ->and($second->curhat_number)->toBe('CR-2026-000002');
});

test('curhat number sequence restarts in a new year', function () {
    Date::setTestNow('2026-12-31');
    createCurhat();
    createCurhat();

    Date::setTestNow('2027-01-01');

    expect(createCurhat()->curhat_number)->toBe('CR-2027-000001');
});

test('curhat number column rejects duplicates', function () {
    Date::setTestNow('2026-03-01');
    $existing = createCurhat();

    Curhat::withoutEvents(fn () => createCurhat(['curhat_number' => $existing->curhat_number]));
})->throws(QueryException::class);
