<?php

namespace App\Http\Controllers\Auth;

use App\Enums\OrganizerStatus;
use App\Http\Controllers\Controller;
use App\Models\OrganizerProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:user,organizer'],
            'organization_name' => ['required_if:role,organizer', 'nullable', 'string', 'max:255'],
            'phone' => ['required_if:role,organizer', 'nullable', 'string', 'max:20'],
            'bank_name' => ['required_if:role,organizer', 'nullable', 'string', 'max:255'],
            'bank_account_number' => ['required_if:role,organizer', 'nullable', 'string', 'max:50'],
            'bank_account_name' => ['required_if:role,organizer', 'nullable', 'string', 'max:255'],
            'organization_address' => ['required_if:role,organizer', 'nullable', 'string'],
            'official_contact' => ['required_if:role,organizer', 'nullable', 'string', 'max:255'],
            'legality_document' => ['required_if:role,organizer', 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        if ($request->role === 'organizer') {
            $documentPath = null;
            if ($request->hasFile('legality_document')) {
                $documentPath = $request->file('legality_document')->store('legality_documents', 'public');
            }

            OrganizerProfile::create([
                'user_id' => $user->id,
                'organization_name' => $request->organization_name,
                'phone' => $request->phone,
                'bank_name' => $request->bank_name,
                'bank_account_number' => $request->bank_account_number,
                'bank_account_name' => $request->bank_account_name,
                'organization_address' => $request->organization_address,
                'official_contact' => $request->official_contact,
                'legality_document' => $documentPath,
                'status' => OrganizerStatus::Pending,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
