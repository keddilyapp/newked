@section('title', 'Page Not Found - 404')
@section('page-title', 'Page Not Found - 404')

@include(route_prefix('frontend.partials.header'))

<style>
    .single-page-area {
        padding: {{tenant() ? '80px 0' : '200px 0'}};
        padding-bottom: 120px;
    }
</style>

<section class="single-page-area">
    <div class="container container-one">
        <div class="single-page-wrapper center-text">
            <div class="single-page text-center">
                <div class="single-page-thumb">
                    @if(!empty(get_static_option('error_image')))
                        {!! render_image_markup_by_attachment_id(get_static_option('error_image')) !!}
                    @else
                        <img src="{{global_asset('assets/landlord/uploads/media-uploader/404.png')}}" alt="">
                    @endif
                </div>
                <div class="single-page-contents mt-4 mt-lg-5">
                    <h2 class="single-page-contents-title fw-600"> {{__('Sorry! We can\'t find the page')}} </h2>
                    <div class="btn-wrapper">
                        <a href="{{url('/')}}" class="cmn-btn cmn-btn-bg-1 radius-0 mt-4"> {{__('Back to Home')}} </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include(route_prefix('frontend.partials.footer'))
