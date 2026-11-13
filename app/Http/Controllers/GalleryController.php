<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        return view('gallery.index', [
            'albums' => GalleryAlbum::query()
                ->published()
                ->whereHas('photos')
                ->with('coverPhoto')
                ->withCount('photos')
                ->paginate(12),
        ]);
    }

    public function show(Request $request, GalleryAlbum $album): View
    {
        abort_unless($album->isPublished() || $request->user()?->is_admin, 404);

        return view('gallery.show', [
            'album' => $album,
            'photos' => $album->photos()->get(),
        ]);
    }
}
