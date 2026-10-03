<?php

namespace App\Http\Requests\Admin;

class DeleteRestaurantRequest extends AdminFormRequest
{
    protected $errorBag = 'restaurantDeletion';

    public function rules(): array
    {
        $restaurant = $this->route('restaurant');

        return [
            'confirm_name' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($restaurant) {
                    if ($value !== $restaurant->name) {
                        $fail(__('admin.delete_restaurant.name_mismatch'));
                    }
                },
            ],
            'delete_owner' => ['sometimes', 'boolean'],
        ];
    }

    public function wantsOwnerDeleted(): bool
    {
        // Default: remove the owner when this was their last restaurant.
        return $this->boolean('delete_owner', true);
    }
}