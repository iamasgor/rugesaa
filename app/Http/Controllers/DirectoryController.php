<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DirectoryController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if (Gate::denies('view-directory')) {
            return to_route('portal')->with('error', 'The alumni directory opens once your membership is active.');
        }

        return view('portal.directory');
    }
}
