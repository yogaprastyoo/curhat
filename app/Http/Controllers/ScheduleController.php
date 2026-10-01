<?php

namespace App\Http\Controllers;

use App\Models\CounselingSchedule;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    /**
     * Siswa melihat jadwal untuk curhat miliknya; pendamping melihat jadwal yang dia buat.
     */
    public function index(): View
    {
        $user = Auth::user();
        $query = CounselingSchedule::with(['curhat', 'counselor']);

        if ($user->isStudent()) {
            $query->whereHas('curhat', fn ($curhat) => $curhat->where('student_id', $user->id));
        } elseif ($user->isCounselor()) {
            $query->where('counselor_id', $user->id);
        } else {
            abort(403);
        }

        $schedules = $query->orderByDesc('date')->orderByDesc('start_time')->paginate(15);

        return view('schedule', compact('schedules'));
    }
}
