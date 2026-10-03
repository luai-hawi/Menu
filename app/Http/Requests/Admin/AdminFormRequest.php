<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

abstract class AdminFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * Localized generic messages (lang/{en,ar}/admin.php) so admin forms do not
     * depend on a full Arabic validation.php being installed.
     */
    public function messages(): array
    {
        return collect(trans('admin.validation'))
            ->filter(fn ($message) => is_string($message))
            ->all();
    }

    public function attributes(): array
    {
        return collect(trans('admin.fields'))
            ->filter(fn ($label) => is_string($label))
            ->all();
    }

    protected function emailTakenRule(?int $ignoreUserId = null, string $messageKey = 'admin.validation.email_taken'): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($ignoreUserId, $messageKey) {
            $exists = \App\Models\User::query()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower((string) $value)])
                ->when($ignoreUserId, fn ($q) => $q->whereKeyNot($ignoreUserId))
                ->exists();

            if ($exists) {
                $fail(__($messageKey));
            }
        };
    }

    protected function slugRules(?int $ignoreRestaurantId = null, ?string $currentSlug = null): array
    {
        $policy = app(\App\Services\Admin\RestaurantSlugPolicy::class);
        $changed = $currentSlug === null || $this->input('slug') !== $currentSlug;

        return [
            'required',
            'string',
            'max:'.\App\Services\Admin\RestaurantSlugPolicy::MAX_LENGTH,
            // Legacy slugs keep working; format/reserved rules apply to new values only.
            ...($changed ? ['regex:'.\App\Services\Admin\RestaurantSlugPolicy::PATTERN] : []),
            function (string $attribute, mixed $value, \Closure $fail) use ($policy, $changed) {
                if ($changed && is_string($value) && $policy->isReserved($value)) {
                    $fail(__('admin.validation.slug_reserved'));
                }
            },
            \Illuminate\Validation\Rule::unique('restaurants', 'slug')->ignore($ignoreRestaurantId),
        ];
    }
}