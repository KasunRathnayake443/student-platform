<?php

namespace App\Http\Controllers;

use App\Models\StudentRegistration;
use App\Services\PlatformSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class StudentRegistrationController
{
    public function show(): View
    {
        return view('student-registration', [
            'feeLabel' => PlatformSettings::registrationFeeLabel(),
            'paymentDetails' => PlatformSettings::paymentDetails(),
            'languages' => PlatformSettings::registrationLanguages(),
            'grades' => PlatformSettings::registrationGrades(),
            'contactAddress' => PlatformSettings::contactAddress(),
            'contactEmail' => PlatformSettings::contactEmail(),
            'contactPhone' => PlatformSettings::contactPhone(),
            'socialLinks' => PlatformSettings::socialLinks(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $key = 'student-registration:'.sha1((string) $request->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()
                ->withInput()
                ->withErrors(['registration' => __('registration.messages.rate_limit')]);
        }

        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'institution' => ['required', 'string', 'max:255'],
            'grade' => ['required', 'string', 'max:100', Rule::in(PlatformSettings::registrationGrades())],
            'language' => ['required', 'string', 'max:100', Rule::in(PlatformSettings::registrationLanguages())],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'referred_by' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in([
                StudentRegistration::PAYMENT_BANK_TRANSFER,
                StudentRegistration::PAYMENT_EZCASH,
                StudentRegistration::PAYMENT_MCASH,
            ])],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'transaction_reference' => ['required', 'string', 'max:255'],
            'receipt' => [
                $request->input('payment_method') === StudentRegistration::PAYMENT_BANK_TRANSFER ? 'required' : 'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ]);

        $receiptPath = $request->hasFile('receipt')
            ? $request->file('receipt')->store('student-registration-receipts', 'public')
            : null;

        StudentRegistration::query()->create([
            'full_name' => $validated['full_name'],
            'institution' => $validated['institution'],
            'grade' => $validated['grade'],
            'language' => $validated['language'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'referred_by' => $validated['referred_by'] ?? null,
            'payment_method' => $validated['payment_method'],
            'payment_date' => $validated['payment_date'],
            'transaction_reference' => $validated['transaction_reference'],
            'receipt_path' => $receiptPath,
            'status' => StudentRegistration::STATUS_PENDING,
        ]);

        return redirect()
            ->route('student.registration')
            ->with('registration_submitted', true);
    }
}
