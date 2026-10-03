<?php

namespace App\Http\Requests\Admin;

class DeleteUnusedAccountRequest extends AdminFormRequest
{
    protected $errorBag = 'accountDeletion';

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'confirm_email' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    if (mb_strtolower(trim((string) $value)) !== mb_strtolower($user->email)) {
                        $fail(__('admin.delete_account.email_mismatch'));
                    }
                },
            ],
        ];
    }
}