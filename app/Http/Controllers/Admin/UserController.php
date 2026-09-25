<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Data\AdminMockData;

class UserController extends Controller
{
    public function index()
    {
        $users = AdminMockData::getUsers();
        $roles = ['Tous', 'Client', 'Propriétaire', 'Administrateur'];
        $statuses = ['Tous', 'Actif', 'Inactif'];
        
        // Apply filters
        $search = request('search', '');
        $selectedRole = request('role', 'Tous');
        $selectedStatus = request('status', 'Tous');

        $filtered = array_filter($users, function ($u) use ($search, $selectedRole, $selectedStatus) {
            $matchSearch = empty($search) || stripos($u['name'], $search) !== false || stripos($u['email'], $search) !== false;
            $matchRole = $selectedRole === 'Tous' || $u['role'] === $selectedRole;
            $matchStatus = $selectedStatus === 'Tous' || $u['status'] === $selectedStatus;
            return $matchSearch && $matchRole && $matchStatus;
        });

        return view('backend.users.index', [
            'users' => array_values($filtered),
            'roles' => $roles,
            'statuses' => $statuses,
            'search' => $search,
            'selectedRole' => $selectedRole,
            'selectedStatus' => $selectedStatus,
        ]);
    }
}
