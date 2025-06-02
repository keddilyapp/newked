<div class="modal fade" tabindex="-1" id="shiprocket_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize">{{__("ShipRocket")}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('tenant.admin.shipping.plugin.settings.update')}}" method="POST">
                @csrf

                <input type="hidden" name="shipping_gateway_name" value="shiprocket">
                <div class="card-body">
                    <!--otp env settings -->
                    <h5 class="mb-4">{{ __('Configure ShipRocket Credentials') }}</h5>
                    <div class="form-group mt-3">
                        <label for=""><strong>{{__('ShipRocket API User Email')}} <span class="text-danger">*</span> </strong></label>
                        <input type="text"  class="form-control" name="shiprocket_api_user_email" value=""
                               placeholder="{{ __('ShipRocket API User Email')}}">
                    </div>

                    <div class="form-group mt-3">
                        <label for=""><strong>{{__('ShipRocket API User Password')}} <span class="text-danger">*</span> </strong></label>
                        <input type="text"  class="form-control" name="shiprocket_api_user_password" value=""
                               placeholder="{{ __('ShipRocket API User Password')}}">
                    </div>

                    <div class="form-group mt-3">
                        <label for=""><strong>{{__('ShipRocket Authorization Token')}}</strong></label>
                        <input type="text"  class="form-control" name="shiprocket_api_authorization_token" value=""
                               placeholder="{{ __('ShipRocket Authorization Token')}}">
                        <p class="text-primary"><small>{{__('The token will auto generate if the above credentials are correct')}}</small></p>
                    </div>

                    <button type="submit" id="update" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}} <x-btn.button-loader class="d-none"/></button>
                </div>
            </form>
        </div>
    </div>
</div>
