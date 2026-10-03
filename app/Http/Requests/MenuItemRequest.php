<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared validation for creating and updating a menu item with nested
 * option groups and options, preserving Arabic with optional English text.
 *
 * Expected payload shape (form array or JSON):
 *
 *  name, description, price, category_id (create only), image
 *
 *  option_groups[]
 *    [0][group_type]   SINGLE|MULTIPLE
 *    [0][group_name_ar]
 *    [0][min_choices]
 *    [0][max_choices]
 *    [0][is_required]  0|1
 *    [0][position]
 *    [0][options][]
 *        [0][option_name_ar]
 *        [0][price_delta]
 *        [0][option_note_ar]
 *        [0][position]
 *        [0][is_active]    0|1
 */
class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $item = $this->route('item');

        return ! $item || (int) $item->menuCategory?->restaurant?->user_id === (int) auth()->id();
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'description_en' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_active' => ['sometimes', 'boolean'],

            'option_groups' => ['nullable', 'array', 'max:50'],
            'option_groups.*' => ['required', 'array'],
            'option_groups.*.id' => ['nullable', 'integer', 'distinct'],
            'option_groups.*.group_type' => ['required_with:option_groups', Rule::in(['SINGLE', 'MULTIPLE'])],
            'option_groups.*.group_name_ar' => ['required_with:option_groups', 'string', 'max:255'],
            'option_groups.*.group_name_en' => ['nullable', 'string', 'max:255'],
            'option_groups.*.min_choices' => ['nullable', 'integer', 'min:0', 'max:50'],
            'option_groups.*.max_choices' => ['nullable', 'integer', 'min:0', 'max:50'],
            'option_groups.*.is_required' => ['nullable', 'boolean'],
            'option_groups.*.position' => ['nullable', 'integer', 'min:0'],

            'option_groups.*.options' => ['required_with:option_groups', 'array', 'min:1', 'max:50'],
            'option_groups.*.options.*' => ['required', 'array'],
            'option_groups.*.options.*.id' => ['nullable', 'integer', 'distinct'],
            'option_groups.*.options.*.option_name_ar' => ['required', 'string', 'max:255'],
            'option_groups.*.options.*.option_name_en' => ['nullable', 'string', 'max:255'],
            'option_groups.*.options.*.price_delta' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'option_groups.*.options.*.option_note_ar' => ['nullable', 'string', 'max:160'],
            'option_groups.*.options.*.option_note_en' => ['nullable', 'string', 'max:160'],
            'option_groups.*.options.*.position' => ['nullable', 'integer', 'min:0'],
            'option_groups.*.options.*.is_active' => ['nullable', 'boolean'],
        ];

        if ($isCreate) {
            $restaurants = auth()->user()->restaurants;
            $restaurant = $restaurants->find(session('selected_restaurant_id')) ?? $restaurants->first();
            $rules['category_id'] = ['required', Rule::exists('menu_categories', 'id')->where('restaurant_id', $restaurant?->id ?? 0)];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'option_groups.*.group_name_ar.required_with' => __('messages.errors.group_name_required'),
            'option_groups.*.options.required_with' => __('messages.errors.group_needs_options'),
            'option_groups.*.options.*.option_name_ar.required' => __('messages.errors.option_name_required'),
        ];
    }

    /**
     * Business-rule checks the built-in rules can't express.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            foreach ((array) $this->input('option_groups', []) as $gIdx => $group) {
                if (! is_array($group)) {
                    continue;
                }
                $item = $this->route('item');
                $existing = ! empty($group['id']) && is_scalar($group['id']) && $item
                    ? $item->optionGroups()->find($group['id']) : null;
                if (! empty($group['id']) && ! $existing) {
                    $v->errors()->add("option_groups.$gIdx.id", __('studio.invalid_option_owner'));
                }
                foreach (is_array($group['options'] ?? null) ? $group['options'] : [] as $oIdx => $option) {
                    if (is_array($option) && ! empty($option['id']) &&
                        (! $existing || ! is_scalar($option['id']) || ! $existing->options()->whereKey($option['id'])->exists())) {
                        $v->errors()->add("option_groups.$gIdx.options.$oIdx.id", __('studio.invalid_option_owner'));
                    }
                }
                $type = $group['group_type'] ?? 'SINGLE';
                $min = is_scalar($group['min_choices'] ?? 0) ? (int) ($group['min_choices'] ?? 0) : 0;
                $max = is_scalar($group['max_choices'] ?? 1) ? (int) ($group['max_choices'] ?? 1) : 1;
                $options = $group['options'] ?? [];
                $optCount = is_array($options) ? count($options) : 0;

                if ($type === 'SINGLE') {
                    if ($max !== 1) {
                        $v->errors()->add(
                            "option_groups.$gIdx.max_choices",
                            __('messages.errors.single_max_must_be_one')
                        );
                    }
                    if ($min > 1) {
                        $v->errors()->add(
                            "option_groups.$gIdx.min_choices",
                            __('messages.errors.single_min_must_be_zero_or_one')
                        );
                    }
                }

                if ($type === 'MULTIPLE') {
                    if ($max > 0 && $min > $max) {
                        $v->errors()->add(
                            "option_groups.$gIdx.min_choices",
                            __('messages.errors.min_greater_than_max')
                        );
                    }
                    if ($max > $optCount) {
                        $v->errors()->add(
                            "option_groups.$gIdx.max_choices",
                            __('messages.errors.max_exceeds_options', ['count' => $optCount])
                        );
                    }
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $groups = $this->input('option_groups', []);

        if (! is_array($groups) || empty($groups)) {
            return;
        }

        $groups = array_values($groups);

        foreach ($groups as &$group) {
            if (! is_array($group)) {
                continue;
            }
            $group['is_required'] = $group['is_required'] ?? false;
            $group['min_choices'] = $group['min_choices'] ?? 0;
            $group['max_choices'] = $group['max_choices'] ?? 1;
            $group['position'] = $group['position'] ?? 0;

            if (($group['group_type'] ?? 'SINGLE') === 'SINGLE' && in_array($group['is_required'], [true, false, 0, 1, '0', '1'], true)) {
                $group['max_choices'] = 1;
                $group['min_choices'] = $group['is_required'] ? 1 : 0;
            }

            if (isset($group['options']) && is_array($group['options'])) {
                $group['options'] = array_values($group['options']);
                foreach ($group['options'] as &$opt) {
                    if (! is_array($opt)) {
                        continue;
                    }
                    $opt['price_delta'] = isset($opt['price_delta']) && $opt['price_delta'] !== ''
                        ? $opt['price_delta']
                        : 0;
                    $opt['position'] = $opt['position'] ?? 0;
                    $opt['is_active'] = $opt['is_active'] ?? true;
                }
                unset($opt);
            }
        }
        unset($group);

        $this->merge(['option_groups' => $groups]);
    }
}
