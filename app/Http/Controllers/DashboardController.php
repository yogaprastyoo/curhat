<?php

namespace App\Http\Controllers;

use App\Models\CounselingSchedule;
use App\Models\Curhat;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->isStudent()) {
            $ownCurhats = Curhat::where('student_id', $user->id);

            return view('dashboard', [
                'totalCurhat' => (clone $ownCurhats)->count(),
                'waiting' => (clone $ownCurhats)->whereIn('status', ['submitted', 'reviewed'])->count(),
                'completed' => (clone $ownCurhats)->where('status', 'completed')->count(),
                'latest' => (clone $ownCurhats)->with('category')->latest()->limit(5)->get(),
            ]);
        }

        if ($user->isCounselor()) {
            $assigned = Curhat::where('assigned_to', $user->id);

            return view('dashboard-counselor', [
                'new' => (clone $assigned)->where('status', 'submitted')->count(),
                'needResponse' => (clone $assigned)->whereIn('status', ['reviewed', 'in_counseling'])->count(),
                'todaySchedule' => CounselingSchedule::where('counselor_id', $user->id)
                    ->whereDate('date', today())
                    ->count(),
                'latest' => (clone $assigned)->with('category')->latest()->limit(5)->get(),
            ]);
        }

        return view('dashboard-general');
    }
}
