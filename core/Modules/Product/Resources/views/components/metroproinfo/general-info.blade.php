@php
    if(!isset($product)){
        $product = null;
    }
@endphp

<div class="general-info-wrapper">
    <h4 class="dashboard-common-title-two"> {{ __("General Information") }} </h4>
    <div class="general-info-form mt-0 mt-lg-4">
        <form action="#">
            <div class="dashboard-input mt-4">
                <label class="dashboard-label color-light mb-2"> {{ __("Name") }} </label>
                <input type="text" class="form-control radius-10" id="product-name" value="{!! $product?->name ?? "" !!}" name="name" placeholder="{{ __("Write product Name...") }}">
            </div>

            <div class="dashboard-input mt-4">
                <label class="dashboard-label color-light mb-2">
                    {{ __("Slug") }}
                    <i class="mdi mdi-alert-circle-outline" data-bs-toggle="tooltip" data-bs-placement="right" data-bs-title="{{__('Only selected language text will convert into slug')}}"></i>
                </label>

                <input type="text" class="form-control radius-10" id="product-slug" value="{{ $product?->slug ?? "" }}" name="slug" placeholder="{{ __("Write product slug...") }}">
            </div>

            <div class="dashboard-input mt-4">
                <label class="dashboard-label color-light mb-2"> {{ __("Summery") }} </label>
                <textarea style="height: 120px" class="form-control form--message  radius-10"  name="summery" placeholder="{{ __("Write product Summery...") }}">{!! $product?->summary ?? "" !!} </textarea>
            </div>

            <div class="dashboard-input mt-4">
                <label class="dashboard-label color-light mb-2"> {{ __("Description") }} </label>
                <textarea class="form-control summernote radius-10" name="description" placeholder="{{ __("Type Description") }}">{!! $product?->description ?? "" !!}</textarea>
            </div>

            <div class="dashboard-input mt-4">
                <label class="dashboard-label color-light mb-2"> {{ __("Brand") }} </label>
                <div class="nice-select-two">
                    <select name="brand" class="form-control" id="brand_id">
                        <option value="">{{ __("Select brand") }}</option>
                        @foreach($brands as $item)
                            <option {{ $item->id == $product?->brand_id ? "selected" : "" }} value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
</div>
