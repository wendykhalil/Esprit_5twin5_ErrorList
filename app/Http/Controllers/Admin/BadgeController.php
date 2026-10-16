<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Badge;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BadgeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Badge::withCount('users')->latest();
        if ($request->filled('niveau')) {
            $query->where('niveau', $request->input('niveau'));
        }

        return view('backend.badges.index', [
            'badges' => $query->paginate(15)->withQueryString(),
            'niveau' => $request->input('niveau'),
        ]);
    }

    public function create(): View
    {
        return view('backend.badges.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $badge = Badge::create($this->validated($request));
        $this->storeImage($request, $badge);

        return redirect()->route('admin.badges.index')->with('success', 'Badge créé avec succès.');
    }

    public function show(Badge $badge): View
    {
        $badge->load(['users' => fn ($query) => $query->orderByPivot('date_obtention', 'desc')]);

        return view('backend.badges.show', compact('badge'));
    }

    public function edit(Badge $badge): View
    {
        return view('backend.badges.edit', compact('badge'));
    }

    public function update(Request $request, Badge $badge): RedirectResponse
    {
        $badge->update($this->validated($request, $badge));
        $this->storeImage($request, $badge);

        return redirect()->route('admin.badges.index')->with('success', 'Badge modifié avec succès.');
    }

    public function destroy(Badge $badge): RedirectResponse
    {
        $this->deleteImage($badge);
        $badge->delete();

        return redirect()->route('admin.badges.index')->with('success', 'Badge supprimé avec succès.');
    }

    public function assign(Request $request, Badge $badge): RedirectResponse
    {
        $data = $request->validate(['user_id' => ['required', 'exists:users,id']]);
        $badge->users()->syncWithoutDetaching([
            $data['user_id'] => ['date_obtention' => now()],
        ]);

        return back()->with('success', 'Badge attribué manuellement.');
    }

    public function revoke(Badge $badge, User $user): RedirectResponse
    {
        $badge->users()->detach($user->getKey());

        return back()->with('success', 'Badge retiré manuellement.');
    }

    private function validated(Request $request, ?Badge $badge = null): array
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:badges,slug,'.($badge?->id ?? 'NULL')],
            'description' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'critere' => ['required', 'in:kwh_total,co2_total,reservations_terminees,retours_sans_dommage,releves_declares'],
            'seuil' => ['required', 'integer', 'min:0'],
            'niveau' => ['required', 'in:bronze,argent,or'],
            'actif' => ['sometimes', 'boolean'],
        ]);
        unset($data['image']);

        return $data;
    }

    private function storeImage(Request $request, Badge $badge): void
    {
        if (!$request->hasFile('image')) {
            return;
        }

        $this->deleteImage($badge);
        $badge->update(['image' => $request->file('image')->store('badges', 'public')]);
    }

    private function deleteImage(Badge $badge): void
    {
        if ($badge->image) {
            Storage::disk('public')->delete($badge->image);
        }
    }
}
