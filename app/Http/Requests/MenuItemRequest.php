<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Shared validation for creating and updating a menu item with nested
 * option groups and options. Arabic-only (EN fields were removed).
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
        return auth()->check();
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],

            'option_groups' => ['nullable', 'array'],
            'option_groups.*.id' => ['nullable', 'integer'],
            'option_groups.*.group_type' => ['required_with:option_groups', Rule::in(['SINGLE', 'MULTIPLE'])],
            'option_groups.*.group_name_ar' => ['required_with:option_groups', 'string', 'max:255'],
            'option_groups.*.min_choices' => ['nullable', 'integer', 'min:0', 'max:50'],
            'option_groups.*.max_choices' => ['nullable', 'integer', 'min:0', 'max:50'],
            'option_groups.*.is_required' => ['nullable', 'boolean'],
            'option_groups.*.position' => ['nullable', 'integer', 'min:0'],

            'option_groups.*.options' => ['required_with:option_groups', 'array', 'min:1'],
            'option_groups.*.options.*.id' => ['nullable', 'integer'],
            'option_groups.*.options.*.option_name_ar' => ['required', 'string', 'max:255'],
            'option_groups.*.options.*.price_delta' => ['nullable', 'numeric', 'between:-9999.99,9999.99'],
            'option_groups.*.options.*.option_note_ar' => ['nullable', 'string', 'max:160'],
            'option_groups.*.options.*.position' => ['nullable', 'integer', 'min:0'],
            'option_groups.*.options.*.is_active' => ['nullable', 'boolean'],
        ];

        if ($isCreate) {
            $rules['category_id'] = ['required', 'exists:menu_categories,id'];
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
                $type = $group['group_type'] ?? 'SINGLE';
                $min = (int) ($group['min_choices'] ?? 0);
                $max = (int) ($group['max_choices'] ?? 1);
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
            $group['is_required'] = filter_var($group['is_required'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $group['min_choices'] = isset($group['min_choices']) ? (int) $group['min_choices'] : 0;
            $group['max_choices'] = isset($group['max_choices']) ? (int) $group['max_choices'] : 1;
            $group['position'] = isset($group['position']) ? (int) $group['position'] : 0;

            if (($group['group_type'] ?? 'SINGLE') === 'SINGLE') {
                $group['max_choices'] = 1;
                $group['min_choices'] = $group['is_required'] ? 1 : 0;
            }

            if (isset($group['options']) && is_array($group['options'])) {
                $group['options'] = array_values($group['options']);
                foreach ($group['options'] as &$opt) {
                    $opt['price_delta'] = isset($opt['price_delta']) && $opt['price_delta'] !== ''
                        ? (float) $opt['price_delta']
                        : 0;
                    $opt['position'] = isset($opt['position']) ? (int) $opt['position'] : 0;
                    $opt['is_active'] = filter_var($opt['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
                }
                unset($opt);
            }
        }
        unset($group);

        $this->merge(['option_groups' => $groups]);
    }
}
