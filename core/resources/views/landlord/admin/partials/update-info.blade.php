<div class="floating-card-wraper">
    <div class="floating-card">
        <div class="info-btn-wrapper d-flex gap-2">
            <div class="floating-card-close-wrapper mark" id="update-info-mark-btn"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="Mark as read"
                 data-url="{{route('landlord.admin.update.info')}}"
            ></div>

            <div class="floating-card-close-wrapper info-close" id="update-info-close-btn"
                 data-bs-toggle="tooltip"
                 data-bs-placement="top"
                 title="Close"
            ></div>
        </div>

        <h2 class="info-card-title fs-5">{{__('Updates')}}</h2>

        @foreach($update_info as $index => $info)
            <h2 class="floating-card-title">{{$index + 1}}. {{$info->title ?? ''}}</h2>
            <p class="floating-card-pera">
                @if($info->url)
                    <a href="{{$info->url}}" target="_blank">{{$info->description ?? ''}}</a>
                @else
                    {{$info->description ?? ''}}
                @endif
            </p>
        @endforeach
    </div>
</div>
