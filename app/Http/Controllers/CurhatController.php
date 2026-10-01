<?php

namespace App\Http\Controllers;

use App\Models\CounselingSchedule;
use App\Models\Curhat;
use App\Models\CurhatAccessLog;
use App\Models\CurhatCategory;
use App\Models\CurhatHistory;
use App\Models\CurhatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CurhatController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = CurhatCategory::where('is_active', true)->orderBy('name')->get();

        return view('curhats.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort_unless(Auth::user()->role === 'student', 403);
        $validated = $request->validate([
            'category_id' => 'required|exists:curhat_categories,id',
            'title' => 'required|string|max:150',
            'message' => 'required|string|max:10000',
            'privacy_scope' => 'required|in:counselor,counselor_psychologist',
            'counseling_request' => 'required|in:no,yes,unsure',
        ]);
        $curhat = DB::transaction(function () use ($validated) {
            $curhat = Curhat::create([
                'student_id' => Auth::id(),
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'message' => $validated['message'],
                'privacy_scope' => $validated['privacy_scope'],
                'counseling_request' => $validated['counseling_request'],
                'status' => 'submitted',
            ]);
            CurhatHistory::create([
                'curhat_id' => $curhat->id,
                'user_id' => Auth::id(),
                'action' => 'created',
                'status' => 'submitted',
                'note' => 'Curhat dibuat oleh siswa.',
            ]);

            return $curhat;
        }, attempts: 3);

        return redirect()->route('curhats.show', $curhat)
            ->with('success', 'Curhat berhasil dikirim.');
    }

    public function index()
    {
        $user = Auth::user();
        $query = Curhat::with(['category', 'student', 'counselor']);
        if ($user->role === 'student') {
            $query->where('student_id', $user->id);
        } elseif (in_array($user->role, ['counselor', 'psychologist'])) {
            $query->where('assigned_to', $user->id);
        } else {
            abort(403);
        }
        $curhats = $query->latest()->paginate(15);

        return view('curhats.index', compact('curhats'));
    }

    public function show(Curhat $curhat)
    {
        $this->authorize('view', $curhat);
        CurhatAccessLog::create([
            'curhat_id' => $curhat->id,
            'user_id' => Auth::id(),
            'action' => 'view',
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
        $curhat->load(['category', 'student', 'counselor', 'messages.user', 'histories.user', 'schedules.counselor']);

        return view('curhats.show', compact('curhat'));
    }

    public function storeMessage(Request $request, Curhat $curhat)
    {
        $this->authorize('view', $curhat);
        $validated = $request->validate([
            'message' => 'required|string|max:10000',
        ]);
        CurhatMessage::create([
            'curhat_id' => $curhat->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
        ]);
        if (Auth::user()->isCounselor() && $curhat->canTransitionTo('responded')) {
            $curhat->transitionTo('responded');

            CurhatHistory::create([
                'curhat_id' => $curhat->id,
                'user_id' => Auth::id(),
                'action' => 'status_changed',
                'status' => 'responded',
                'note' => 'Tanggapan diberikan.',
            ]);
        }

        return back()->with('success', 'Pesan berhasil dikirim.');
    }

    public function assign(Request $request, Curhat $curhat)
    {
        abort_unless(Auth::user()->role === 'counseling_supervisor', 403);
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);
        $assignee = User::whereKey($validated['assigned_to'])
            ->whereIn('role', ['counselor', 'psychologist'])
            ->firstOrFail();
        if ($assignee->role === 'psychologist' && $curhat->privacy_scope !== 'counselor_psychologist') {
            abort(403);
        }
        $curhat->update(['assigned_to' => $assignee->id]);
        CurhatHistory::create([
            'curhat_id' => $curhat->id,
            'user_id' => Auth::id(),
            'action' => 'assigned',
            'note' => 'Pendamping ditugaskan.',
        ]);

        return back()->with('success', 'Pendamping berhasil ditugaskan.');
    }

    public function updateStatus(Request $request, Curhat $curhat)
    {
        $this->authorize('view', $curhat);
        abort_unless(in_array(Auth::user()->role, ['counselor', 'psychologist']), 403);

        $validated = $request->validate([
            'status' => ['required', Rule::in($curhat->allowedNextStatuses())],
            'note' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($curhat, $validated) {
            $curhat->transitionTo($validated['status']);

            CurhatHistory::create([
                'curhat_id' => $curhat->id,
                'user_id' => Auth::id(),
                'action' => 'status_changed',
                'status' => $validated['status'],
                'note' => $validated['note'] ?? null,
            ]);
        });

        return back()->with('success', 'Status curhat berhasil diperbarui.');
    }

    public function storeSchedule(Request $request, Curhat $curhat)
    {
        $this->authorize('view', $curhat);
        abort_unless(in_array(Auth::user()->role, ['counselor', 'psychologist']), 403);

        $isAlreadyScheduled = $curhat->status === 'scheduled';

        if (! $isAlreadyScheduled && ! $curhat->canTransitionTo('scheduled')) {
            return back()->withErrors([
                'date' => 'Curhat harus di-review terlebih dahulu sebelum dijadwalkan.',
            ]);
        }

        $validated = $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'location' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($curhat, $validated, $isAlreadyScheduled) {
            CounselingSchedule::create([
                'curhat_id' => $curhat->id,
                'counselor_id' => Auth::id(),
                'date' => $validated['date'],
                'start_time' => $validated['start_time'],
                'location' => $validated['location'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'scheduled',
            ]);

            if (! $isAlreadyScheduled) {
                $curhat->transitionTo('scheduled');
            }

            CurhatHistory::create([
                'curhat_id' => $curhat->id,
                'user_id' => Auth::id(),
                'action' => 'scheduled',
                'status' => 'scheduled',
                'note' => 'Jadwal konseling dibuat.',
            ]);
        });

        return back()->with('success', 'Jadwal konseling berhasil dibuat.');
    }
}
