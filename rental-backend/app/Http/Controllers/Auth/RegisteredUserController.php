<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
            'phone'         => 'required|string|max:20',
            'id_type'       => 'required|in:nrc,passport',
            'nrc_passport'  => ['required', 'string', 'max:50', Rule::unique('users', 'nrc_passport')],
            'id_document'   => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'selfie'        => 'required|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $idDocumentUrl = $request->file('id_document')->store('identity_docs', 'public');
        $selfieUrl     = $request->file('selfie')->store('identity_docs', 'public');

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'phone'           => $request->phone,
            'id_type'         => $request->id_type,
            'nrc_passport'    => $request->nrc_passport,
            'id_document_url' => $idDocumentUrl,
            'selfie_url'      => $selfieUrl,
            // Self-registered users are tenants. Without this the role stays null
            // and every `role:tenant` route (apply, tour request, review) 403s.
            'role_id'         => Role::where('name', 'tenant')->value('id'),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
