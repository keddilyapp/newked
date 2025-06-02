<style>
    td *, th, td{
        text-align: left;
    }
    .tableWrap {
        overflow-x: auto;
    }
    .head-table{
        background-color: var(--section-bg-2);
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }

    .body-table {
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    .body-table tr {
        border-spacing: 0 5px;
    }

    th {
        background-color: #f6f6f6;
        padding: 8px;
        color: #444;
    }

    td {
        text-align: left;
        padding: 8px;
        font-weight: 400;
        color: #666;
        min-width: 60px;
    }
    .head-table td {
        padding:  20px;
    }
    .head-table td p {
        font-size: 16px;
        font-weight: 400;
        line-height: 20px;
        color: #666;
    }
    .head-table td p:not(:last-child) {
        margin-bottom: 5px;
    }
    .head-table td p strong {
        color: #444;
    }
</style>

<div class="p-4 recent-order-wrapper dashboard-table bg-white padding-30 mt-4">
    @if($tracking_data['status'])
        @php
            $tracking_data = json_decode($tracking_data['details']);
            $shipments_data = current($tracking_data)->tracking_data;

            $shipment_track = current($shipments_data->shipment_track);
            $current_status = $shipment_track?->current_status;
            $current_timestamp = \Carbon\Carbon::parse($shipment_track?->edd);
            $current_location = $shipment_track?->delivered_to;

            $origin = $shipment_track?->origin;
            $destination = $shipment_track?->destination;

            $events = $shipments_data?->shipment_track_activities;
            $events = collect($events);
            $events_group = $events->map(function ($item) {
                            $formattedTimestamp = \Carbon\Carbon::parse($item->date)->format('y-m-d');
                            $item->formattedTimestamp = $formattedTimestamp;
                            return $item;
                        })->groupBy('formattedTimestamp');
            $event_counter = count($events);
        @endphp

        <div class="tableWrap">
            <h4>{{__('Result Summary')}}</h4>
            <table class="head-table mt-3">
                <tr>
                    <td>
                        <p><strong>{{__('Tracking Number:')}} {{$shipment_track?->order_id}}</strong></p>
                        <p><strong class="text-danger">{{$current_status}}</strong></p>
                    </td>
                    <td>
                        <p><strong>{{$current_timestamp->format('l, F d, Y')}}
                                at {{$current_timestamp->format('h:i A')}}</strong></p>
                        <p><strong>{{__('Origin Service Area:')}}</strong> {{$origin}}</p>
                        <p><strong>{{__('Destination Service
                                                    Area:')}}</strong> {{$destination}}</p>
                    </td>
                </tr>
            </table>
        </div>
        <div class="tableWrap mt-4">
            <table class="body-table">
                <tbody>
                @foreach($events_group ?? [] as $index => $events)
                    @php
                        $events_group_timestamp = \Carbon\Carbon::parse($index);
                    @endphp
                    <tr class="top-tr">

                        <th colspan="2">{{$events_group_timestamp->format('l, F d, Y')}}</th>
                        <th>{{__('Location')}}</th>
                        <th>{{__('Time')}}</th>
                    </tr>

                    @foreach($events ?? [] as $event)
                        @php
                            $event_timestamp = \Carbon\Carbon::parse($event->date);
                        @endphp
                        <tr class="{{$loop->last ? 'bottom-tr' : ''}}">
                            <td>{{$event_counter--}}</td>
                            <td>
                                <p class="m-0">{{$event?->status}}</p>
                                <p class="m-0">{{$event?->activity}}</p>
                            </td>
                            <td>
                                @if(property_exists($event, 'location'))
                                    {{$event?->location}}
                                @endif
                            </td>
                            <td>{{$event_timestamp->format('h:i A')}}</td>
                        </tr>
                    @endforeach
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="tableWrap">
            <table>
                <tr>
                    <th class="text-center">{{__('No Data Found')}}</th>
                </tr>
            </table>
        </div>
    @endif
</div>
