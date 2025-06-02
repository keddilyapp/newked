<?php

namespace Modules\WooCommerce\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConfirmCredentialMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (tenant_plan_sidebar_permission('woocommerce'))
        {
            if (get_static_option('woocommerce_site_url') && get_static_option('woocommerce_consumer_key') && get_static_option('woocommerce_consumer_secret')) {
                return $next($request);
            }

            return to_route('tenant.admin.woocommerce.settings');
        }

        abort(404);
    }
}
