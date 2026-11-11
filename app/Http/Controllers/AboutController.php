<?php

namespace App\Http\Controllers;

use App\Models\CommitteeMember;
use App\Models\GovernanceDocument;
use Illuminate\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        $committee = CommitteeMember::query()->ordered()->get();

        return view('pages.about', [
            'featuredCommittee' => $committee->where('is_featured', true)->values(),
            'otherCommittee' => $committee->where('is_featured', false)->values(),
            'documents' => GovernanceDocument::query()->orderBy('sort_order')->get(),
        ]);
    }
}
