<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        if (app()->environment('local') && config('mail.default') === 'log') {
            $user = User::where('email', $request->input('email'))->first();

            if (!$user) {
                return back()->withErrors([
                    'email' => 'We could not find an account with that email address.',
                ]);
            }

            $token = Password::createToken($user);
            $resetUrl = route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ]);

            return back()
                ->with('success', 'Password reset link created for local development.')
                ->with('local_reset_url', $resetUrl);
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with(
                'success',
                'Password reset link sent. Please check your email.'
            );
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->withErrors([
                'email' => 'Please wait before requesting another reset link.',
            ]);
        }

        return back()->withErrors([
            'email' => 'We could not find an account with that email address.',
        ]);
    }

    public function showResetForm(
        Request $request,
        string $token
    ): View {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ]);

                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()
                ->route('login')
                ->with(
                    'success',
                    'Your password has been reset. You can now log in.'
                );
        }

        return back()
            ->withErrors([
                'email' => 'This password reset link is invalid or has expired.',
            ])
            ->withInput($request->only('email'));
    }
}
