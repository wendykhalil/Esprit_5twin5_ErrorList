<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index(Request $request)
    {
        $query = Badge::with('user')->latest('date_obtention');
        if ($request->filled('niveau')) {
            $query->where('niveau', $request->input('niveau'));
        }
        return view('backend.badges.index', [
            'badges' => $query->paginate(15)->withQueryString(),
            'niveau' => $request->input('niveau'),
        ]);
    }

    public function create()
    {
        return view('backend.badges.create', ['users' => User::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        Badge::create($this->validated($request));
        return redirect()->route('admin.badges.index')->with('success', 'Badge créé avec succès.');
    }

    public function edit(Badge $badge)
    {
        return view('backend.badges.edit', ['badge' => $badge, 'users' => User::orderBy('name')->get()]);
    }

    public function update(Request $request, Badge $badge)
    {
        $badge->update($this->validated($request, $badge));
        return redirect()->route('admin.badges.index')->with('success', 'Badge modifié avec succès.');
    }

    public function destroy(Badge $badge)
    {
        $badge->delete();
        return redirect()->route('admin.badges.index')->with('success', 'Badge supprimé avec succès.');
    }

    private function validated(Request $request, ?Badge $badge = null): array
    {
        $unique = 'unique:badges,nom,NULL,id,user_id,'.$request->input('user_id');
        if ($badge) {
            $unique = 'unique:badges,nom,'.$badge->id.',id,user_id,'.$request->input('user_id');
        }
        return $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'nom' => ['required', 'string', 'max:255', $unique],
            'niveau' => ['required', 'in:bronze,argent,or'],
            'seuil_kwh' => ['required', 'integer', 'min:0'],
            'date_obtention' => ['required', 'date', 'before_or_equal:today'],
        ]);
    }
}
