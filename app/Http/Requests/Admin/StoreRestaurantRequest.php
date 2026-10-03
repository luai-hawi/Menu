<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\RestaurantSlugPolicy;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreRestaurantRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => RestaurantSlugPolicy::normalize($this->input('slug')),
            'owner_email' => $this->filled('owner_email') ? mb_strtolower(trim((string) $this->input('owner_email'))) : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'owner_method' => ['required', Rule::in(['existing', 'new'])],
            'user_id' => [
                'exclude_unless:owner_method,existing', 'required', 'integer',
                Rule::exists('users', 'id')->whereNot('role', 'admin'),
            ],
            'owner_name' => ['exclude_unless:owner_method,new', 'nullable', 'string', 'max:255'],
            'owner_email' => ['exclude_unless:owner_method,new', 'required', 'email', 'max:255', $this->emailTakenRule()],
            'phone' => ['exclude_unless:owner_method,new', 'nullable', 'string', 'max:20'],
            'password' => ['exclude_unless:owner_method,new', 'required', 'confirmed', Password::min(8)],
            'name' => ['required', 'string', 'max:255'],
            'slug' => $this->slugRules(),
            'description' => ['nullable', 'string', 'max:5000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'subscription_amount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }
}