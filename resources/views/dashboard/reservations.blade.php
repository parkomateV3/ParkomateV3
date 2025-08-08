@extends('dashboard.header')
@section('content')
<style>
    .swal2-confirm {
        background-color: rgb(61, 161, 255) !important;
    }

    .swal2-cancel {
        background-color: rgb(255, 56, 56) !important;
    }

    .map-container {
        position: relative;
        width: 100%;
        max-width: 100%;
        margin: auto;
    }

    .map-container img {
        width: 100%;
        height: auto;
        display: block;
    }

    .parking-btn {
        position: relative;
        border: 2px solid #333;
        cursor: pointer;
        border-radius: 50%;
    }

    /* Tooltip text */
    .parking-btn::after {
        content: attr(data-tooltip);
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(0, 0, 0, 0.7);
        color: #fff;
        padding: 5px 8px;
        border-radius: 3px;
        white-space: nowrap;
        font-size: 12px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease-in;
        z-index: 1000;
    }

    /* Tooltip arrow */
    .parking-btn::before {
        content: "";
        position: absolute;
        bottom: calc(100% - 4px);
        left: 50%;
        transform: translateX(-50%);
        border-width: 6px;
        border-style: solid;
        border-color: transparent transparent rgba(0, 0, 0, 0.7) transparent;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s;
        z-index: 1000;
    }

    .parking-btn:hover::after,
    .parking-btn:hover::before {
        opacity: 1;
        visibility: visible;
    }

    .available {
        background-color: green;
        border: none;
    }

    .occupied {
        background-color: red;
        border: none;
    }

    .swal2-container {
        z-index: 20000 !important;
    }
</style>
<div class="flex justify-between flex-wrap items-center mb-5">
    <h4 class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse" id="floorname">
        Floor name
    </h4>

    <h4 class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse m-auto">
        <span class="text-success-500" id="available">Unreserved: 0</span> &nbsp; <span class="text-danger-500" id="reserved">Reserved: 0</span> &nbsp; <span id="total">Total: 0</span>
    </h4>
</div>

<div class="map-container">
    <img src="{{ asset('floors/' . $floorImg) }}" alt="Parking Map">
    @foreach ($filtered as $coord)
    <button
        class="parking-btn {{ $coord->status ? 'occupied' : 'available' }}"
        data-id="{{ $coord->reservation_id }}"
        data-tooltip="Slot {{ $coord->reservation_id }} — {{ $coord->status ? 'Reserved' : 'Unreserved' }}"
        style="
              position: absolute;
              left: {{ $coord->x_pct }}%;
              top: {{ $coord->y_pct }}%;
              width: 2%;
              height: 3%;
              transform: translate(-50%, -50%);
            "></button>
    @endforeach
</div>

<div id="data-container" class="mb-6"
    data-floor_id='@json($floor_id)'>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        refreshDashboard();
        // getLast4HourGraphInitialData();


        setInterval(function() {
            refreshDashboard();
        }, 5000);
    });

    function refreshDashboard() {
        var container = document.getElementById('data-container');
        var floor_id = JSON.parse(container.dataset.floor_id);
        const baseUrl = window.location.origin + "/dashboard/reservation-data/" + floor_id;

        $.get(baseUrl, function(data, status) {
            if (data.data) {
                $('#available').text('Unreserved: ' + data.available);
                $('#reserved').text('Reserved: ' + data.reserved);
                $('#total').text('Total: ' + data.total);
                $('#floorname').text(data.floor_name);
            } else {
                console.log('Data not found!');
            }


        });
    }
</script>

<script>
    $(document).on('click', '.parking-btn', function() {
        const btn = $(this);
        const id = btn.data('id');

        // Determine current status by checking class
        const isOccupied = btn.hasClass('occupied'); // true if reserved

        const actionText = isOccupied ? 'Unreserve' : 'Reserve';
        const confirmColor = isOccupied ? '#d33' : '#3085d6';

        Swal.fire({
            title: `Do you want to ${actionText.toLowerCase()} this slot?`,
            text: null,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Yes, ${actionText}`,
            confirmButtonColor: confirmColor,
            cancelButtonText: 'Cancel',
            target: document.body
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.post('{{ route("toggleStatus") }}', {
                id,
                _token: '{{ csrf_token() }}'
            }, function(res) {
                btn.toggleClass('occupied available');
                Swal.fire({
                    title: 'Success',
                    text: isOccupied ? 'Slot has been unreserved.' : 'Slot has been reserved.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            });
        });
    });
</script>

@endsection