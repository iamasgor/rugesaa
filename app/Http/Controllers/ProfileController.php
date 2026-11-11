<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMemberProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->profile === null) {
            return to_route('portal')->with('error', 'Your account has no alumni profile.');
        }

        return view('portal.profile', [
            'user' => $user,
            'profile' => $user->profile,
        ]);
    }

    public function update(UpdateMemberProfileRequest $request): RedirectResponse
    {
        $profile = $request->user()->profile;
        $validated = $request->validated();

        $oldPhoto = $profile->photo_path;
        $photoPath = $oldPhoto;

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('profile-photos', 'public');
        } elseif ($request->boolean('remove_photo')) {
            $photoPath = null;
        }

        $profile->update([
            ...collect($validated)->except(['photo', 'remove_photo'])->all(),
            'photo_path' => $photoPath,
            'show_in_directory' => $request->boolean('show_in_directory'),
            'mentor_opt_in' => $request->boolean('mentor_opt_in'),
            'newsletter_opt_in' => $request->boolean('newsletter_opt_in'),
        ]);

        if ($oldPhoto !== null && $oldPhoto !== $photoPath) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return to_route('profile.edit')->with('profile_saved', true);
    }
}
