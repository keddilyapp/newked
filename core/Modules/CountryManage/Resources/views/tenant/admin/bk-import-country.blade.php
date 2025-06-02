@extends('tenant.admin.admin-master')

@section('title', __('Import Settings'))

@section('style')
    <style>
        li{
            font-size: 16px;
            cursor: pointer;
        }

        ol.level-one{
            list-style-type: none;
        }
        ol.level-one > li{
            background-color: rgb(241, 242, 252);
            border: 1px solid #c1c2cb;
            border-radius: 5px;
            padding: 20px;
            padding-left: 30px;
            margin-bottom: 15px;
        }
        ol.level-one > li:hover{
            border-radius: 5px;
            background-color: rgb(255, 249, 249);
        }

        ol.level-two{
            background-color: rgb(232, 238, 246);
            border: 1px solid #b1b5bb;
            border-radius: 5px;
            padding: 20px;
            padding-left: 30px;
        }
        ol.level-two > li{
            margin-bottom: 15px;
        }
        ol.level-two > li:hover{
            border-radius: 5px;
            background-color: rgb(255, 246, 246);
        }

        ol.level-three{
            background-color: rgb(238, 247, 248);
            border: 1px solid #b7c4c5;
            border-radius: 5px;
            padding: 20px;
            padding-left: 45px;
        }
    </style>
@endsection

@section('content')
    <div class="dashboard__body">
        <div class="row">
            <div class="col-lg-12">
                <x-error-msg/>
                <x-flash-msg/>

                <div class="customMarkup__single">
                    <div class="customMarkup__single__item">
                        <div class="d-flex justify-content-between">
                            <h4 class="customMarkup__single__title text-capitalize">{{ __('Import Countries, States and Cities (Only CSV File)') }}</h4>
                            <a href="{{route('tenant.admin.settings.csv.download.sample')}}" class="btn btn-info btn-sm">{{__('Download Sample File')}}</a>
                        </div>
                    </div>
                </div>

                <div class="customMarkup__single__item">
                    <h4 class="customMarkup__single__title">{{ __('Import Country (only csv file)') }}</h4>
                    <div class="customMarkup__single__inner mt-4">
                        @if(empty($import_data))
                            <form action="{{route('tenant.admin.country.import.csv.update.settings')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="#" class="label-title">{{__('File')}}</label>
                                    <input type="file" name="csv_file" accept=".csv" class="form-control" required>
                                    <div class="text-info">{{__('only csv file are allowed with separate by (,) comma.')}}</div>
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
                            <form action="{{route('tenant.admin.country.import.database')}}" method="post" enctype="multipart/form-data">
                                @csrf
                                <table class="table table-striped">
                                    <thead>
                                    <th style="width: 200px">{{{__('Field Name')}}}</th>
                                    <th>{{{__('Set Field')}}}</th>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><h6>{{__('Title')}}</h6></td>
                                        <td>
                                            <div class="form-group">
                                                <select class="form-control mapping_select">
                                                    <option value="">{{__('Select Field')}}</option>
                                                    {!! $option_markup !!}
                                                </select>
                                                <input type="hidden" name="country">
                                            </div>
                                            <p class="text-info">{{ __('Select country and only unique countries added automatically') }}</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><h6>{{__('Status')}}</h6></td>
                                        <td>
                                            <div class="form-group">
                                                <select class="form-control mapping_select">
                                                    <option value="1">{{__('Publish')}}</option>
                                                    <option value="0">{{__('Draft')}}</option>
                                                </select>
                                                <input type="hidden" name="status" value="1">
                                            </div>
                                        </td>
                                    </tr>

                                    </tbody>
                                </table>
                                <button type="submit" class="btn btn-success loading-btn">{{__('Import')}}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
