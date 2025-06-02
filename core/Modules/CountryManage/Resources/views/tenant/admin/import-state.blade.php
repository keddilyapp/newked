@extends('tenant.admin.admin-master')

@section('title', __('Import States'))

@section('style')
    <x-datatable.css />
@endsection
@section('content')
    <div class="dashboard__body">
        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex justify-content-between">
                    <h4 class="customMarkup__single__title">{{ __('Import State (only csv file)') }}</h4>
                    <a href="{{route('tenant.admin.settings.csv.download.sample', 'state')}}" class="btn btn-info btn-sm" download="sample-state-data.csv">{{__('Download Sample File')}}</a>
                </div>
            </div>

            <div class="col-lg-8">
                <x-error-msg/>
                <x-flash-msg/>

                <div class="customMarkup__single">
                    <div class="customMarkup__single__item">
                        <div class="customMarkup__single__inner mt-4">
                            @if(empty($import_data))
                                <form action="{{route('tenant.admin.state.import.csv.update.settings')}}" method="post" enctype="multipart/form-data">
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

                                <form action="{{route('tenant.admin.state.import.database')}}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    <table class="table table-striped">
                                        <thead>
                                        <th style="width: 200px">{{{__('Field Name')}}}</th>
                                        <th>{{{__('Set Field')}}}</th>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><h6>{{__('Country')}}</h6></td>
                                            @php $all_countries = \Modules\CountryManage\Entities\Country::all_countries(); @endphp
                                            <td>
                                                <div class="form-group">
                                                    <select class="form-control" name="country_id" id="country_id">
                                                        <option value="">{{ __('Select Country') }}</option>
                                                        @foreach($all_countries as $country)
                                                            <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <p class="text-info">{{ __('Select your states country') }}</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><h6>{{__('State')}}</h6></td>
                                            <td>
                                                <div class="form-group">
                                                    <select class="form-control mapping_select">
                                                        <option value="">{{__('Select Field')}}</option>
                                                        {!! $option_markup !!}
                                                    </select>
                                                    <input type="hidden" name="name">
                                                </div>
                                                <p class="text-info">{{ __('Select state and only unique states added automatically according to the selected country.') }}</p>
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
                    var allValue = $('.mapping_select');
                    $.each(allValue,function (index,item){
                        $('.mapping_select option[value="'+$(this).val()+'"]').attr('disabled',true);
                    });

                })

            });
        }(jQuery));
    </script>
@endsection
