@php
    $id = isset($id) ? $id : null;
    $section_id = isset($section) ? $section : '';
@endphp
<div class="form-group">
    <label for="{{$name}}">{{__($title)}}</label>
    @php $signature_image_upload_btn_label = __('Upload'); @endphp
    <div class="media-upload-btn-wrapper" id="{{$name}}_section">
        <div class="img-wrap">
            @php
                if (is_null($id)){
                    $id = get_static_option($name);
                }
                $signature_img = get_attachment_image_by_id($id);
            @endphp
            @if (!empty($signature_img))
                @if(!empty($signature_img['img_url']))
                    <div class="rmv-span"><i class="mdi mdi-close"></i></div>
                    <div class="attachment-preview">
                        <div class="thumbnail">
                            <div class="centered">
                                <img class="avatar user-thumb" src="{{$signature_img['img_url']}}" >
                            </div>
                        </div>
                    </div>
                    @php $signature_image_upload_btn_label = __('Change'); @endphp
                @else
{{--                    <div class="attachment-preview">--}}
                        <div class="card card-flush py-4 w-244">
                            <div class="card-body text-center pt-0">
                                <style>
                                    .image-input-placeholder {
                                        background-image: url('{{ asset('assets/img/blank-image.svg') }}');
                                    }
                                </style>
                                <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3" data-kt-image-input="true">
                                    <!--begin::Preview existing avatar-->
                                    <div class="image-input-wrapper w-150px h-150px"></div>
                                    <!--end::Preview existing avatar-->
                                </div>
                                <div class="text-muted fs-7">{{ __('allow image format: png, jpg, jpeg') }}</div>
                            </div>
                        </div>
{{--                    </div>--}}
                @endif
            @endif
        </div>
        <br>
        <input type="hidden" name="{{$name}}" value="{{$id}}">
        <button type="button" class="btn btn-info media_upload_form_btn" data-btntitle="{{__('Select Image')}}" data-modaltitle="{{__('Upload Image')}}" data-imgid="{{$id ?? ''}}">
            {{__($signature_image_upload_btn_label)}}
        </button>
    </div>
    @if(isset($dimentions))
        <small>{{__('recommended image size is')}} {{$dimentions}}</small>
    @endif
</div>
