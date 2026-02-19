<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $users = User::query()
            ->orderBy('name')
            ->paginate(15);

        return view('users.index', ['users' => $users]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', [
            'roles' => $this->rolesForCurrentUser(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $validated = $request->validated();
        $validated['password_hash'] = Hash::make($validated['password']);
        unset($validated['password'], $validated['password_confirmation']);

        User::create($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): RedirectResponse
    {
        return redirect()->route('users.edit', $user);
    }

    public function edit(User $user): View|RedirectResponse
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'roles' => $this->rolesForCurrentUser(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validated();

        if ($user->role === 'admin' && ($validated['role'] ?? '') !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Cannot demote the last admin. Ensure at least one admin exists.');
            }
        }

        if (! empty($validated['password'])) {
            $validated['password_hash'] = Hash::make($validated['password']);
        }
        unset($validated['password'], $validated['password_confirmation']);

        $user->update($validated);

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }

    private function rolesForCurrentUser(): array
    {
        $all = [
            'admin' => 'Admin',
            'manager' => 'Manager',
            'viewer' => 'Viewer',
        ];

        if (auth()->user()?->role === 'manager') {
            return array_filter($all, fn ($key) => $key !== 'admin', ARRAY_FILTER_USE_KEY);
        }

        return $all;
    }
}
