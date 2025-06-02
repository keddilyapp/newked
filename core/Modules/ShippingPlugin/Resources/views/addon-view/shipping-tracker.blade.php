<section class="category-area" data-padding-top="{{$data['padding_top']}}" data-padding-bottom="{{$data['padding_bottom']}}">
    <div class="container-two">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title section-title-two" style="justify-content: {{$data['section_title_position'] ?? 'start'}}">
                    <h2 class="title"> {{$data['section_title'] ?? ''}} </h2>
                </div>
            </div>
        </div>
        <div class="row margin-top-10">
            <div class="col-lg-12">
                <form method="POST">
                    <div class="form-group">
                        <label for="tracking_number">{{$data['label_title'] ?? ''}}</label>
                        <input type="text" class="form-control" id="tracking_number" name="tracking_number"
                               placeholder="{{$data['input_placeholder'] ?? __('eg: 6458412354')}}">
                    </div>

                    <div class="form-group text-{{$data['button_position'] ?? 'end'}}">
                        @php
                            $background_color = !empty($data['button_color']) ? $data['button_color'] : 'red';
                            $border_color = $background_color;
                        @endphp

                        <button class="btn text-white track-btn" type="button" style="background-color: {{$background_color}};border-color: {{$background_color}}">{{$data['button_text'] ?? __('Track')}}
                            <x-btn.button-loader icon="las la-spinner" class="d-none"/>
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-lg-12 tracking-result-wrapper d-none">

            </div>
        </div>
    </div>
</section>
<!-- Category area end -->

<script>

    let track_btn = document.querySelector('.track-btn');
    track_btn.addEventListener('click', function (e) {
        e.preventDefault();

        let tracking_number = document.getElementById('tracking_number');
        if(tracking_number.value === '')
        {
            toastr.error(`{{__('Tracking number is required')}}`);
            return ;
        }

        $.ajax({
            url: `{{route('tenant.shipping.plugin.track')}}`,
            type: 'POST',
            data: {
                '_token': `{{csrf_token()}}`,
                'tracking_number': tracking_number.value
            },
            beforeSend: function () {
                track_btn.querySelector('.loading-icon').classList.remove('d-none');
            },
            success: function (response) {
                let tracking_result_wrapper = document.querySelector('.tracking-result-wrapper');
                tracking_result_wrapper.innerHTML = response.markup;

                tracking_result_wrapper.classList.remove('d-none');
                track_btn.querySelector('.loading-icon').classList.add('d-none');

                let type = response.type === 'danger' ? 'error' : 'success';
                toastr[type](response.msg)
            },
            error: function (response) {
                track_btn.querySelector('.loading-icon').classList.add('d-none');
            }
        });
    });
</script>
