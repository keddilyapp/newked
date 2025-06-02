<div class="modal fade" tabindex="-1" id="shiprocket_configuration_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-capitalize">{{__("ShipRocket")}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{route('tenant.admin.shipping.plugin.configuration.update')}}" method="POST">
                @csrf

                <input type="hidden" name="shipping_gateway_name" value="shiprocket">
                <div class="card-body">
                    <!--otp env settings -->
                    <h5 class="mb-4">{{ __('Configure ShipRocket Options') }}</h5>


                    <div class="form-group">
                        <label for="shiprocket_pickup_location">{{__('Select Pickup Location')}}</label>
                        <select class="form-control" name="shiprocket_pickup_location" id="shiprocket_pickup_location">
                            @foreach($data['list'] ?? [] as $item)
                                <option value="{{$item->pickup_location}}">{{$item->pickup_location}}</option>
                            @endforeach
                        </select>
                        <p class="text-primary mt-1">
                            <small>{{__('Select pickup location which were created on the ShipRocket account')}}</small>
                        </p>
                    </div>

                    <div class="mb-2">
                        <x-fields.switcher label="Auto create order" name="shiprocket_auto_create_order_option"
                                           info="{{__('Checking this button will auto create orders when any user place an order and complete his/her payment process. Disabling it will not take any action on automatic order creation on ShipRocket')}}"
                                           info-class="alert alert-info text-dark"
                                           set-value="on"
                                           value="{{get_static_option('shiprocket_auto_create_order_option')}}"/>

                        <x-fields.switcher label="Disable order tracking" name="shiprocket_order_tracking_option"
                                           info="{{__('Checking this button will enable or disable order tracking system from frontend for general user')}}"
                                           info-class="alert alert-info text-dark"
                                           set-value="on"
                                           value="{{get_static_option('shiprocket_order_tracking_option')}}"/>
                    </div>

                    <button type="submit" id="update" class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}}
                        <x-btn.button-loader class="d-none"/>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
