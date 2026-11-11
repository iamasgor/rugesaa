<?php

namespace App\Http\Controllers;

use App\Enums\ContactTopic;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Models\User;
use App\Notifications\NewContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact', [
            'topics' => ContactTopic::cases(),
        ]);
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        $contactMessage = ContactMessage::create($request->validated());

        $associationEmail = config('rugesaa.email');

        if (filled($associationEmail)) {
            Notification::route('mail', $associationEmail)->notify(new NewContactMessage($contactMessage));
        } else {
            Notification::send(User::query()->where('is_admin', true)->get(), new NewContactMessage($contactMessage));
        }

        return to_route('contact')
            ->with('contact_sent', $contactMessage->topic->getLabel());
    }
}
