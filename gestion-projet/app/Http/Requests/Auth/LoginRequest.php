<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /** Max failed attempts before lockout */
    private const MAX_ATTEMPTS = 5;

    /** Base lockout duration in seconds (5 minutes) */
    private const BASE_LOCKOUT_SECONDS = 300;

    /** Max lockout cap in seconds (2 hours) */
    private const MAX_LOCKOUT_SECONDS = 7200;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'utilisateur_email' => ['required', 'string', 'email'],
            'utilisateur_mot_de_passe' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'utilisateur_email.required' => "L'adresse e-mail est obligatoire.",
            'utilisateur_email.email' => "L'adresse e-mail n'est pas valide.",
            'utilisateur_mot_de_passe.required' => 'Le mot de passe est obligatoire.',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotLocked();

        if (! Auth::attempt([
            'utilisateur_email' => $this->input('utilisateur_email'),
            'password' => $this->input('utilisateur_mot_de_passe'),
        ], $this->boolean('remember'))) {
            $this->recordFailedAttempt();

            $remaining = self::MAX_ATTEMPTS - $this->currentAttempts();

            if ($remaining > 0) {
                throw ValidationException::withMessages([
                    'utilisateur_email' => "Identifiants incorrects. Il vous reste {$remaining} tentative(s) avant blocage temporaire.",
                ]);
            }

            throw ValidationException::withMessages([
                'utilisateur_email' => 'Identifiants incorrects.',
            ]);
        }

        $this->clearLoginState();
    }

    private function ensureIsNotLocked(): void
    {
        $lockKey = 'login_lock:'.$this->throttleKey();

        if (! Cache::has($lockKey)) {
            return;
        }

        $unlockAt = Cache::get($lockKey);
        $seconds = max(0, $unlockAt - now()->timestamp);
        $minutes = ceil($seconds / 60);

        throw ValidationException::withMessages([
            'utilisateur_email' => "Trop de tentatives de connexion. Réessayez dans {$minutes} minute(s).",
        ]);
    }

    private function recordFailedAttempt(): void
    {
        $key = $this->throttleKey();
        $attemptsKey = 'login_attempts:'.$key;
        $lockoutsKey = 'login_lockouts:'.$key;
        $lockKey = 'login_lock:'.$key;

        if (! Cache::has($attemptsKey)) {
            Cache::put($attemptsKey, 1, now()->addMinutes(15));
            $attempts = 1;
        } else {
            $attempts = Cache::increment($attemptsKey);
        }

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockouts = Cache::get($lockoutsKey, 0) + 1;
            Cache::put($lockoutsKey, $lockouts, now()->addDay());

            $duration = min(
                self::BASE_LOCKOUT_SECONDS * (2 ** ($lockouts - 1)),
                self::MAX_LOCKOUT_SECONDS
            );

            Cache::put($lockKey, now()->timestamp + $duration, $duration);
            Cache::forget($attemptsKey);
        }
    }

    private function currentAttempts(): int
    {
        return (int) Cache::get('login_attempts:'.$this->throttleKey(), 0);
    }

    private function clearLoginState(): void
    {
        $key = $this->throttleKey();
        Cache::forget('login_attempts:'.$key);
        Cache::forget('login_lock:'.$key);
    }

    private function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('utilisateur_email')).'|'.$this->ip());
    }
}
