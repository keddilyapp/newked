<?php

namespace Modules\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [

        ];
    }

    protected function prepareForValidation()
    {
        return $this->merge([
            "payment_gateway" => $this->selected_gateway,
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }
}
