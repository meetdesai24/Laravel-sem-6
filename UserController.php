<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Module 2: User Management (Table 5.10) - create, read, update, delete and activate/deactivate. */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->when($request->filled('q'), fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('name', 'like', "%{$request->q}%")->orWhere('email', 'like', "%{$request->q}%");
            }))
            ->orderBy('name')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create', ['user' => new User(['role' => User::HOST, 'status' => true])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'      => ['nullable', 'digits:10'],
            'role'       => ['required', Rule::in(User::ROLES)],
            'department' => ['nullable', 'string', 'max:100'],
            'password'   => ['required', 'min:8', 'confirmed'],
        ]);

        User::create($data + ['status' => true]);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'      => ['nullable', 'digits:10'],
            'role'       => ['required', Rule::in(User::ROLES)],
            'department' => ['nullable', 'string', 'max:100'],
            'password'   => ['nullable', 'min:8', 'confirmed'],
        ]);

        if ($user->is(auth()->user()) && $data['role'] !== User::ADMIN) {
            return back()->withInput()->with('error', 'You cannot remove your own admin role.');
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Accounts that appear in visit history are kept for the audit trail: deactivate them instead.
        $inUse = $user->hostedVisits()->exists()
            || Visit::where('approved_by', $user->id)->exists()
            || VisitLog::where('checked_in_by', $user->id)->orWhere('checked_out_by', $user->id)->exists();

        if ($inUse) {
            return back()->with('error', "{$user->name} has visit history and cannot be deleted. Deactivate the account instead.");
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    public function toggle(User $user)
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update(['status' => ! $user->status]);

        return back()->with('success', $user->name.($user->status ? ' activated.' : ' deactivated.'));
    }
}
