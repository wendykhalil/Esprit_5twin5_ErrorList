<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * @var array<string, string>
     */
    private const ROLE_FILTER_MAP = [
        'Client' => UserRole::Client->value,
        'Prestataire' => UserRole::Provider->value,
        'Administrateur' => UserRole::Admin->value,
    ];

    public function index(Request $request): View
    {
        $roles = ['Tous', 'Client', 'Prestataire', 'Administrateur'];
        $statuses = ['Tous', 'Actif', 'Inactif'];

        $search = trim((string) $request->input('search', ''));
        $selectedRole = $request->input('role', 'Tous');
        $selectedStatus = $request->input('status', 'Tous');

        $query = User::query()->latest();

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        if ($selectedRole !== 'Tous' && isset(self::ROLE_FILTER_MAP[$selectedRole])) {
            $query->where('role', self::ROLE_FILTER_MAP[$selectedRole]);
        }

        if ($selectedStatus === 'Actif') {
            $query->whereNotNull('email_verified_at');
        } elseif ($selectedStatus === 'Inactif') {
            $query->whereNull('email_verified_at');
        }

        $users = $query->paginate(15)->withQueryString();

        return view('backend.users.index', [
            'users' => $users,
            'roles' => $roles,
            'statuses' => $statuses,
            'search' => $search,
            'selectedRole' => $selectedRole,
            'selectedStatus' => $selectedStatus,
        ]);
    }

    public function show(User $user): View
    {
        $user->load('serviceProvider');

        return view('backend.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load('serviceProvider');
        $assignableRoles = UserRole::cases();

        return view('backend.users.edit', compact('user', 'assignableRoles'));
    }

    public function update(UpdateAdminUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();
        $newRole = $validated['role'];

        if ($user->isAdmin() && $newRole !== UserRole::Admin->value && $this->isLastAdmin($user)) {
            return back()
                ->withInput()
                ->withErrors([
                    'role' => 'Impossible de retirer le rôle administrateur au dernier compte admin.',
                ]);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($user->isAdmin() && $this->isLastAdmin($user)) {
            return back()->with('error', 'Impossible de supprimer le dernier administrateur.');
        }

        try {
            $user->delete();
        } catch (\Throwable) {
            return back()->with(
                'error',
                'Suppression impossible : cet utilisateur est lié à des réservations, équipements ou autres données.'
            );
        }

        return redirect()
            ->route('admin.users')
            ->with('success', 'Utilisateur supprimé.');
    }

    private function isLastAdmin(User $user): bool
    {
        if (! $user->isAdmin()) {
            return false;
        }

        return User::query()->where('role', UserRole::Admin->value)->count() <= 1;
    }
}
