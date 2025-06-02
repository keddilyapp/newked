@extends('landlord.admin.admin-master')
@section('title')
    {{__('Price Plan Settings')}}
@endsection

@section('style')
    <link rel="stylesheet" href="{{global_asset('assets/common/css/select2.min.css')}}">

    <style>
        .upload-area {
            width: 150px;
            height: 150px;
            border: 2px dashed #ddd;
            border-radius: 5px;
            text-align: center;
            overflow: auto;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .upload-area span {
            font-size: 13px;
            line-height: 1.5;
        }

        .custom-img-thumbnail {
            height: 150px;
            width: 150px;
        }

        .custom-dd-btn {
            position: absolute;
            top: 10px;
            right: 10px
        }
    </style>
@endsection

@section('content')
    <div class="col-lg-12 col-ml-12">
        <div class="row">
            <div class="col-12">
                <x-error-msg/>
                <x-flash-msg/>
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">{{__("Price Plan Settings")}}</h4>
                        <form action="{{route('landlord.admin.price.plan.settings')}}" method="POST"
                              enctype="multipart/form-data">
                            @csrf
                            @php
                                $fileds = [
                                        1 => __('One Day'),
                                        2 => __('Two Day'),
                                        3 => __('Three Day'),
                                        4 => __('Four Day'),
                                        5 => __('Five Day'),
                                        6 => __('Six Day'),
                                        7 => __('Seven Day')
                                    ];
                            @endphp
                            <div class="form-group  mt-3">
                                <label
                                    for="site_logo">{{__('Select How many days earlier expiration mail alert will be send')}}</label>
                                <select name="package_expire_notify_mail_days[]" class="form-control expiration_dates"
                                        multiple="multiple">

                                    @foreach($fileds as $key => $field)
                                        @php
                                            $package_expire_notify_mail_days = get_static_option('package_expire_notify_mail_days');
                                            $decoded = json_decode($package_expire_notify_mail_days) ?? [];
                                        @endphp
                                        <option value="{{$key}}"
                                        @foreach($decoded as  $day)
                                            {{$day == $key ? 'selected' : ''}}
                                            @endforeach
                                        >{{__($field)}}</option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="form-group">
                                @php
                                    $themes = getAllThemeData();
                                @endphp
                                <label for="default-theme">{{__('Default Theme')}}</label>
                                <select name="default_theme" id="default-theme" class="form-control">
                                    @foreach($themes as $theme)
                                        @php
                                            $custom_theme_name = get_static_option_central($theme->slug.'_theme_name');
                                        @endphp
                                        <option
                                            value="{{$theme->slug}}" {{get_static_option('default_theme') == $theme->slug ? 'selected' : ''}}>{{ !empty($custom_theme_name) ? $custom_theme_name : $theme->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                @php
                                    $languages = [
                                            'en_GB' => 'English (UK)',
                                            'ar' => 'العربية',
                                            'hi_IN' => 'हिन्दी',
                                            'tr_TR' => 'Türkçe',
                                            'it_IT' => 'Italiano',
                                            'pt_PT' => 'Português',
                                            'pt_BR' => 'Português do Brasil',
                                            'pt_AO' => 'Português de Angola'
                                        ];
                                @endphp
                                <label for="default-language">{{__('Default Languages')}}</label>
                                <select name="default_language" id="default-language" class="form-control">
                                    @foreach($languages as $index => $language)
                                        <option
                                            value="{{$index}}" {{get_static_option_central('default_language') == $index ? 'selected' : ''}}>{{ $language }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="default-logo">{{__('Default Logo')}}</label>
                                <div id="drag-drop-form">
                                    <div id="preview" style="display: flex; gap:14px;">
                                        <div class="upload-area" id="uploadfile">
                                            <span>Drag and Drop image here<br/>or<br/>Click to select image</span>
                                        </div>
                                    </div>

                                    <input style="display:none;" class="form-control" name="has_file" id="has-file" type="hidden" value="{{get_static_option_central('plan_default_logo') ? 'yes' : 'no'}}">
                                    <input style="display:none;" class="form-control" name="file_input" id="file-input"
                                           type="file" multiple>
                                </div>
                            </div>

                            <div class="form-group">
                                <label
                                    for="zero-plan-limit">{{__('Zero Price Plan Purchase Limit (Free Plan)')}}</label>
                                <input class="form-control" type="number" name="zero_plan_limit" id="zero-plan-limit"
                                       placeholder="{{__('Example: 1')}}"
                                       value="{{get_static_option('zero_plan_limit')}}">
                                <small>{{__('Purchase limitation for a price plan which price is zero')}}</small>
                            </div>

                            <div class="form-group">
                                <x-fields.input type="text" name="tenant_admin_default_username"
                                                value="{{get_static_option_central('tenant_admin_default_username')}}"
                                                label="{{__('Tenant Admin Default Username')}}"/>
                                <x-fields.input type="text" name="tenant_admin_default_password"
                                                value="{{get_static_option_central('tenant_admin_default_password')}}"
                                                label="{{__('Tenant Admin Default Password')}}"/>
                            </div>

                            <button type="submit" id="update"
                                    class="btn btn-primary mt-4 pr-4 pl-4">{{__('Update Changes')}}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{global_asset('assets/common/js/select2.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            $('.expiration_dates').select2();
        });
    </script>

    <script>
        $(function () {
            // on submit, store array of image data in the hidden field
            // document.getElementById('form').addEventListener("submit",function(){
            //     let img_array = [];
            //     let i = 0;
            //     $(".thumb-image").each(function(i) {
            //         imgsrc = this.src;
            //         img_array.push('"'+imgsrc+'"');
            //         i+=1;
            //     });
            //     document.getElementById('base64').value = '['+img_array+']';
            // }, false);

            // preventing page from redirecting
            // $("html").on("dragover", function(e) {
            //     e.preventDefault();
            //     e.stopPropagation();
            //     $("#uploadfile").css("background-color", "#fff");
            // });

            // Drag over
            $('.upload-area').on('dragover', function (e) {
                e.stopPropagation();
                e.preventDefault();
                $("#uploadfile").css("background-color", "#eee");
            });

            // Open file selector on div click
            $("#uploadfile").on('click', function () {
                $("#file-input").click();
            });

            // Drop files on upload area
            $('.upload-area').on('drop', function (e) {
                e.stopPropagation();
                e.preventDefault();
                e.dataTransfer = e.originalEvent.dataTransfer;
                $("#uploadfile").css("background-color", "#fff");
                document.querySelector('#file-input').files = e.originalEvent.dataTransfer.files;
                $('#file-input').trigger('change');
            });

            // Trigger previewImages on file input value change
            $('#file-input').on('change', function () {
                previewImages();
            });
        });

        function previewImages() {
            let file_lenght = 1; //number of files are allowed to be uploaded
            let preview = document.querySelector('#preview');
            if (document.querySelector('#file-input').files) {
                [].forEach.call(document.querySelector('#file-input').files, readAndPreview);
            }

            function readAndPreview(file) {
                // validate image file extension
                if (!/\.(jpe?g|png|gif|webp)$/i.test(file.name)) {
                    return toastr.error(file.name + " is not an image");
                }

                let reader = new FileReader();
                reader.addEventListener("load", function () {

                    let image = $(
                        "<div style='display:inline-block; position:absolute;'>" +
                        "<img src='" + this.result + "' class='custom-img-thumbnail img-thumbnail thumb-image'>" +
                        "<a href='javascript:void(0)' class='btn btn-danger btn-sm btn-delete custom-dd-btn'>x</a>" +
                        "</div>");

                    if ($("#preview img.thumb-image").length < file_lenght) {
                        $('#preview').append(image);
                    }

                    $('.btn-delete').click(function () {
                        $(this).parent('div').remove();
                    });
                });

                reader.readAsDataURL(file);
            }
        }

        let prevImage = `{{get_static_option_central('plan_default_logo')}}`;
        if (prevImage !== '') {
            let fullPath = `{{global_asset('assets/landlord/uploads/default-uploads')}}`;
            fullPath += `/${prevImage}`;

            let image = $(
                "<div style='display:inline-block; position:absolute;'>" +
                "<img src='" + fullPath + "' class='custom-img-thumbnail img-thumbnail thumb-image'>" +
                "<a href='javascript:void(0)' class='btn btn-danger btn-sm btn-delete custom-dd-btn'>x</a>" +
                "</div>");

            $('#preview').append(image);

            $('.btn-delete').click(function () {
                $(this).parent('div').remove();
                $('#has-file').val('no');
            });
        }
    </script>
@endsection
