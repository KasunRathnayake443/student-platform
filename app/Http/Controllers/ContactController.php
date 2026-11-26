<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

final class ContactController
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $key = 'contact-form:'.sha1((string) $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $message = __('landing.contact.rate_limit');

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => ['contact' => [$message]],
                ], 429);
            }

            return back()
                ->withInput()
                ->withErrors(['contact' => $message]);
        }

        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactSubmission::query()->create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('landing.contact.success'),
            ], 201);
        }

        return redirect()
            ->to(route('home').'#contact')
            ->with('contact_submitted', true);
    }
}
