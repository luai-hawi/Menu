<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('email')) {
            $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]);
        }
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', $this->emailTakenRule($user->id, 'admin.validation.email_in_use')],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'expires_at' => ['nullable', 'date'],
        ];
    }
}