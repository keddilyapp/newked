@extends('tenant.admin.admin-master')

@section('title', __('Import Cities'))

@section('style')
    <x-datatable.css />
@endsection

@section('content')
    <div class="dashboard__body">
        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex justify-content-between">
                    <h4 class="customMarkup__single__title">{{ __('Import City (only csv file)') }}</h4>
                    <a href="{{route('tenant.admin.settings.csv.download.sample', 'city')}}" class="btn btn-info btn-sm" download="sample-city-data.csv">{{__('Download Sample File')}}</a>
                </div>
            </div>

            <div class="col-lg-8">
                <x-error-msg/>
                <x-flash-msg/>

                <div class="customMarkup__single">
                    <div class="customMarkup__single__item">
                        <div class="customMarkup__single__inner mt-4">
                            @if(empty($import_data))
                                <form action="{{route('tenant.admin.city.import.csv.update.settings')}}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group">
                                        <label for="#" class="label-title">{{__('File')}}</label>
                                        <input type="file" name="csv_file" accept=".csv" class="form-control" required>
                                        <div class="info-text">{{__('only csv file are allowed with separate by (,) comma.')}}</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary loading-btn">{{__('Submit')}}</button>
                                </form>
                            @else
                                @php
                                    $option_markup = '';
                                        foreach(current($import_data) as $map_item ){
                                            $option_markup .= '<option value="'.trim($map_item).'">'.$map_item.'</option>';
                                        }
                                @endphp

                                <form action="{{route('tenant.admin.city.import.database')}}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <table class="table table-striped">
                                        <thead>
                                        <th style="width: 200px">{{{__('Field Name')}}}</th>
                                        <th>{{{__('Set Field')}}}</th>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><h6>{{__('Country')}}</h6></td>
                                            @php $countries = \Modules\CountryManage\Entities\Country::all_countries(); @endphp
                                            <td>
                                                <div class="form-group">
                                                    <select class="form-control" name="country_id" id="country_id">
                                                        <option value="">{{ __('Select Country') }}</option>
                                                        @foreach($countries as $country)
                                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <p class="text-info">{{ __('Select state country ') }}</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><h6>{{__('State')}}</h6></td>
                                            @php $cities = \Modules\CountryManage\Entities\State::all_states(); @endphp
                                            <td>
                                                <div class="form-group">
                                                    <select name="state_id" id="state_id" class="get_country_state form-control">
                                                        <option value="">{{ __('Select State') }}</option>
                                                    </select>
                                                </div>
                                                <p class="text-info">{{ __('Select cities state') }}</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><h6>{{__('City')}}</h6></td>
                                            <td>
                                                <div class="form-group">
                                                    <select class="form-control mapping_select">
                                                        <option value="">{{__('Select Field')}}</option>
                                                        {!! $option_markup !!}
                                                    </select>
                                                    <input type="hidden" name="name">
                                                </div>
                                                <p class="text-info">{{ __('Select city and only unique cities added automatically according to the selected country and state.') }}</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><h6>{{__('Status')}}</h6></td>
                                            <td>
                                                <div class="form-group">
                                                    <select class="form-control mapping_select">
                                                        <option value="publish">{{__('Publish')}}</option>
                                                        <option value="draft">{{__('Draft')}}</option>
                                                    </select>
                                                    <input type="hidden" name="status" value="publish">
                                                </div>
                                            </td>
                                        </tr>

                                        </tbody>
                                    </table>
                                    <button type="submit" class="btn btn-primary loading-btn">{{__('Import')}}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script>
        (function($){
            "use strict";
            $(document).ready(function(){

                $(document).on('click','.loading-btn',function (){
                    $(this).append('<i class="ml-2 fas fa-spinner fa-spin"></i>')
                });

                $(document).on('change','.mapping_select',function (){
                    $('.mapping_select option').attr('disabled',false);
                    $(this).next('input').val($(this).val());
                    let allValue = $('.mapping_select');
                    $.each(allValue,function (index,item){
                        $('.mapping_select option[value="'+$(this).val()+'"]').attr('disabled',true);
                    });
                })

                // change country and get state
                $('#country_id').on('change', function() {
                    let country_id = $(this).val();

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        method: 'post',
                        url: "{{ route('tenant.admin.state.countries.state') }}",
                        data: {
                            country_id: country_id
                        },
                        success: function(res) {
                            if (res.status == 'success') {
                                let all_options = "<option value=''>{{__('Select State')}}</option>";
                                let all_state = res.states;
                                console.log(all_state)
                                $.each(all_state, function(index, value) {
                                    all_options += "<option value='" + index +
                                        "'>" + value + "</option>";
                                });
                                console.log(all_options)
                                $(".get_country_state").html(all_options);
                                if(all_state.length <= 0){
                                    $(".info_msg").html('<span class="text-danger"> {{ __('No state found for selected country!') }} <span>');
                                }
                            }
                        }
                    })
                })

            });
        }(jQuery));
    </script>
@endsection
