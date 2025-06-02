<div class="modal fade" tabindex="-1" id="dhl_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize">{{__("DHL")}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('tenant.admin.shipping.plugin.settings.update')}}" method="POST">
                @csrf

                <input type="hidden" name="shipping_gateway_name" value="dhl">
                <div class="card-body">
                    <!--otp env settings -->
                    <h5 class="mb-4">{{ __('Configure DHL Credentials') }}</h5>
                    <div class="form-group mt-3">
                        <label for=""><strong>{{__('DHL API Key')}} <span class="text-danger">*</span> </strong></label>
                        <input type="text"  class="form-control" name="dhl_api_key" value=""
                               placeholder="{{ __('DHL API Key')}}">
                    </div>

                    <div class="form-group mt-3">
                        <label for=""><strong>{{__('DHL API Secret')}} <span class="text-danger">*</span> </strong></label>
                        <input type="text"  class="form-control" name="dhl_api_secret" value=""
                               placeholder="{{ __('DHL API Secret')}}">
                    </div>

                    <button type="submit" id="update" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}} <x-btn.button-loader class="d-none"/></button>
                </div>
            </form>
        </div>
    </div>
</div>
