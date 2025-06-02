<?php

namespace Modules\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddNewCustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required'],
            'email' => ['required', 'email', 'max:254','unique:users'],
            'username' => ['required','unique:users'],
            'email_verified' => ['nullable'],
            'phone' => ['nullable'],
            'email_verify_token' => ['nullable'],
            'address' => ['nullable'],
            'state' => ['nullable', 'exists:states,id'],
            'city' => ['nullable'],
            'zipcode' => ['nullable'],
            'country' => ['required', 'exists:countries,id'],
            'password' => ['required'],
            'remember_token' => ['nullable'],
            'facebook_id' => ['nullable'],
            'google_id' => ['nullable'],
            'image' => ['nullable'],
            'account_password' => ['nullable']
        ];
    }

    protected function prepareForValidation()
    {
        $pwd = str()->random(6);

        return $this->merge([
            'password' => \Hash::make($pwd),
            'account_password' => $pwd
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }
}
