@php
    $size = $size ?? '1920x1280';
@endphp

<div class="form-group">
    <label for="og_meta_image">{{ __($label) ?? __('Image Gallery')}}</label>
    <div class="media-upload-btn-wrapper mb-2">
        <div class="img-wrap">
            {!! render_gallery_image_attachment_preview($value  ?? '') !!}
            @if(empty($value))
            <!--begin::Media-->
            <div class="card card-flush py-4 w-244">
                <div class="card-body pt-0">
                    <div class="fv-row mb-2">
                        <div class="dropzone">
                            <div class="dz-message">
                                <i class="mdi mdi-upload btn-icon-prepend text-primary"></i>
                                <div class="ms-4">
                                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">{{ __('Upload Gallery Image ') }}.</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Media-->
            @endif
        </div>
        <input type="hidden" id="{{$id ?? ''}}" name="{{$name ?? ''}}"
               value="{{$value ?? ''}}">
        <button type="button" class="btn btn-info media_upload_form_btn"
                data-btntitle="{{__('Select Image')}}"
                data-modaltitle="{{__('Upload  Image')}}" data-toggle="modal"
                data-mulitple="true"
                data-target="#media_upload_modal">
            @if(empty($value))
                {{__('Upload')}}
            @else
                {{__('Change')}}
            @endif
        </button>
    </div>
    <small class="form-text text-muted mt-2">{{__('allowed image format: jpg,jpeg,png')}}</small><br>
    <small class="form-text text-muted">{{__('allowed image size :') . $size ?? '1920x1280'  }}</small>
</div>
