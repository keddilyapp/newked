@extends('tenant.admin.admin-master')
@section('title')
    {{__('Progressive Web Application (PWA)')}}
@endsection
@section('style')
    <x-media-upload.css/>

    <style>
        .icon-image{
            width: 50px;
            height: 50px;
            border-radius: 10px;
        }
        .image-input-wrapper{
            width: 85% !important;
        }
    </style>
@endsection

@section('content')
    <div class="col-lg-12 col-ml-12">
        <div class="row">
            <div class="col-12">
                <x-flash-msg/>
                <x-error-msg/>

                <div class="dashboard__card card mb-4">
                    <div class="dashboard__card__header card-header">
                        <h4 class="dashboard__card__title card-title">{{__("PWA Settings")}}</h4>
                        <div class="dashboard__card__body card-body custom__form mt-4">
                            <form action="{{  route('tenant.admin.pos.pwa-settings') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12 d-flex gap-5">
                                        <div class="form-group w-25">
                                            <label for="name">{{__('Name')}}</label>
                                            <input class="form-control" id="name" type="text" name="name" value="{{$manifest_data->name ?? 'Name'}}">
                                        </div>

                                        <div class="form-group w-25">
                                            <label for="description">{{__('Description')}}</label>
                                            <textarea class="form-control" id="description" name="description">{{$manifest_data->description ?? 'Description'}}</textarea>
                                        </div>

                                        <div class="form-group w-25">
                                            @php
                                                $display_mode = [
                                                    'fullscreen' => 'fullscreen',
                                                    'standalone' => 'standalone'
                                                ];
                                            @endphp
                                            <label for="display_mode">{{__('Display Mode')}}</label>
                                            <select class="form-control" name="display_mode" id="display_mode">
                                                @foreach($display_mode ?? [] as $mode)
                                                    <option value="{{$mode}}" @selected(!empty($manifest_data) && $manifest_data->display == $mode)>{{__($mode)}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-12 d-flex gap-5">
                                        <div class="form-group w-25">
                                            <label for="colorPicker">{{__('Theme Color')}}</label>
                                            <input class="form-control" type="color" value="{{$manifest_data->theme_color ?? '#6777ef'}}" id="colorPicker" name="color">
                                        </div>

                                        <div class="form-group w-25 d-flex justify-content-between">
                                            <div class="image-input-wrapper w-100">
                                                <label for="icon">{{__('Icon')}}</label>
                                                <input class="form-control" type="file" id="icon" name="icon">
                                                <p class="icon-info"><small>{{__('512 X 512 px or 1:1 ratio image recommended')}}</small></p>
                                            </div>
                                            <div class="d-flex align-items-center mb-3">
                                                <img class="icon-image" src="{{$manifest_data ? global_asset("assets/pwa/".$manifest_data->icons[0]->src) : placeholder_image()}}" alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row d-none tax_class_row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label for="tax_class">{{__('Tax Class')}}</label>
                                            <select class="form-control" name="tax_class" id="tax_class">

                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="btn-wrapper mt-4">
                                    <button type="submit" class="btn btn-primary cmn_btn btn_bg_profile">{{__('Update Changes')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-media-upload.markup/>
@endsection
@section('scripts')
    <x-summernote.js/>
    <x-media-upload.js/>
    <script>
        $(document).ready(function() {
            $(document).on('change', '#icon',function() {
                if (this.files && this.files[0]) {
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $('.icon-image').attr('src', e.target.result);
                        $('.icon-image').show();
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    </script>
@endsection
