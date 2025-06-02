<?php

namespace Modules\Pos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosGatewayRequest extends FormRequest
{
    public function rules(): array
    {
        return [

        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
