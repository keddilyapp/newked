<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // For landlord user subscription
        '/paytm-ipn',
        '/cashfree-ipn',
        '/payfast-ipn',
        '/cinetpay-ipn',
        '/zitopay-ipn',
        '/paytabs-ipn',
        '/iyzipay-ipn',
        '/sslcommerz-ipn',
        '/awdpay-ipn',
        '/kineticpay-ipn',

        // For domain reseller
        '/ipn/paytm',
        '/ipn/cashfree',
        '/ipn/payfast',
        '/ipn/cinetpay',
        '/ipn/zitopay',
        '/ipn/paytabs',
        '/ipn/iyzipay',
        '/ipn/sslcommerz',
        '/ipn/awdpay',
        '/ipn/kineticpay',

        // For tenant customer checkout
        '/s/paytm-ipn',
        '/s/cashfree-ipn',
        '/s/payfast-ipn',
        '/s/cinetpay-ipn',
        '/s/zitopay-ipn',
        '/s/iyzipay-ipn',
        '/s/sslcommerz-ipn',
        '/s/awdpay-ipn',
        '/s/kineticpay-ipn',

        // For landlord user dashboard subscription
        '/wallet/paytm-ipn',
        '/wallet/cashfree-ipn',
        '/wallet/payfast-ipn',
        '/wallet/cinetpay-ipn',
        '/wallet/zitopay-ipn',
        '/wallet/toyyibpay-ipn',
        '/wallet/iyzipay-ipn',
        '/wallet/sslcommerz-ipn',
        '/wallet/awdpay-ipn',
        '/wallet/kineticpay-ipn',
    ];
}
