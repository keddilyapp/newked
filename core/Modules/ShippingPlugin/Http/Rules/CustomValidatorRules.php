<?php

namespace Modules\ShippingPlugin\Http\Rules;

class CustomValidatorRules
{
    public function customRules()
    {
        $active_gateway = get_static_option('active_shipping_gateway');

        if (!empty($active_gateway) && method_exists($this, $active_gateway))
        {
            return $this->$active_gateway();
        }
    }

    private function shiprocket(): array
    {
        return [
            'rules' => [
                'postal_code' => 'required|numeric|min_digits: 6|max_digits: 6'
            ],
            'messages' => [
                'postal_code.required' => __('PIN code is required.'),
                'postal_code.numeric' => __('PIN code must be a number.'),
                'postal_code.min_digits' => __('PIN code must be six digits.'),
                'postal_code.max_digits' => __('PIN code must be six digits.')
            ]
        ];
    }
}
