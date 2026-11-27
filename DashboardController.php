<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Visit;
use App\Models\Visitor;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        $today = Visit::whereDate('visit_date', today());
        $stats = [];

        if ($user->hasRole(User::ADMIN)) {
            $stats = [
                'Today’s visits'         => (clone $today)->count(),
                'Pending approvals'      => Visit::awaitingHost()->count(),
                'Visitors inside'        => Visit::where('status', Visit::CHECKED_IN)->count(),
                'Blacklisted visitors'   => Visitor::where('is_blacklisted', true)->count(),
                'Staff accounts'         => User::count(),
            ];
        } elseif ($user->hasRole(User::SECURITY)) {
            $stats = [
                'Expected today'  => (clone $today)->where('status', Visit::APPROVED)->count(),
                'Visitors inside' => Visit::where('status', Visit::CHECKED_IN)->count(),
                'Checked out today' => (clone $today)->where('status', Visit::CHECKED_OUT)->count(),
            ];
        } else {
            $mine = Visit::where('host_id', $user->id);
            $stats = [
                'Awaiting my decision' => (clone $mine)->awaitingHost()->count(),
                'My visits today'      => (clone $mine)->whereDate('visit_date', today())->count(),
                'Currently inside'     => (clone $mine)->where('status', Visit::CHECKED_IN)->count(),
            ];
        }

        $inside = Visit::with(['visitor', 'host', 'log'])
            ->where('status', Visit::CHECKED_IN)
            ->when($user->hasRole(User::HOST), fn ($q) => $q->where('host_id', $user->id))
            ->latest('updated_at')->limit(8)->get();

        return view('dashboard', compact('stats', 'inside'));
    }
}
