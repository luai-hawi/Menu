<?php

namespace App\Http\Requests\Admin;

use App\Services\Admin\RestaurantSlugPolicy;

class UpdateRestaurantRequest extends AdminFormRequest
{
    protected function prepareForValidation(): void
    {
        $current = $this->route('restaurant')?->slug;
        $submitted = is_string($this->input('slug')) ? trim($this->input('slug')) : $this->input('slug');

        // An unchanged legacy slug (e.g. with uppercase letters) is kept verbatim so old menu URLs keep working.
        $this->merge(['slug' => $submitted === $current ? $current : RestaurantSlugPolicy::normalize($submitted)]);
    }

    public function rules(): array
    {
        $restaurant = $this->route('restaurant');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => $this->slugRules($restaurant->id, $restaurant->slug),
            'description' => ['nullable', 'string', 'max:5000'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}