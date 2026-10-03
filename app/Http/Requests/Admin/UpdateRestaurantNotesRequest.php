<?php

namespace App\Http\Requests\Admin;

class UpdateRestaurantNotesRequest extends AdminFormRequest
{
    protected $errorBag = 'restaurantNotes';

    public function rules(): array
    {
        return [
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ];
    }
}