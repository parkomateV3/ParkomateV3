@extends('dashboard.header')
@section('content')
<style>
    .imgcss {
        border: 2px solid beige;
        border-radius: 10px;
        height: 160px;
        width: 160px;
    }
    .imgcss2 {
        border: 2px solid red;
        border-radius: 10px;
        height: 160px;
        width: 160px;
    }
</style>
<div style="height: 80vh;">
    <div class="grid xl:grid-cols-2 grid-cols-1 gap-6">
        <div class="card xl:col-span-2">
            <div class="card-body flex flex-col p-6">
                <div class="card-text h-full space-y-4">
                    <div class="input-area">
                        <div class="relative text-center" style="width:40%;margin:auto;">
                            <label class="mb-2 block cursor-pointer font-Inter font-medium capitalize text-slate-700 dark:text-slate-50 leading-6">Enter Car Number</label>
                            <input type="text" name="number" class="form-control" id="searchcar" placeholder="Example - MH13MJ1234" value="" required>
                            @php
                            $site_id = Auth::user()->site_id;
                            @endphp
                            <input type="hidden" name="site_id" value="{{$site_id}}" id="site_id">

                        </div>
                        <div id="showresults" style="display: flex;justify-content: center;flex-wrap:wrap;" class="mt-3">

                        </div>
                        <div id="showresults2" style="display: flex;justify-content: center;flex-wrap:wrap;" class="mt-3">
                            
                        </div>
                    </div>
                </div>

            </div>
        </div>

        @if($flag == 1)
        <div class="card xl:col-span-2">
            <div class="card-body flex flex-col p-6">
                <div class="card-text h-full space-y-4">

                    <h6>{{$message}}</h6>

                </div>

            </div>
        </div>
        @endif
    </div>
</div>

<script>
    $(document).ready(function() {
        // Debounce function to limit the rate of function execution
        function debounce(func, delay) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), delay);
            };
        }

        // Function to handle the AJAX request
        function handleSearch() {
            $('#showresults').empty();
            $('#showresults2').empty();
            const input = $('#searchcar').val();
            var site_id = $('#site_id').val();
            // var floor_id = $('#floor_id').val();
            // var clocation = $('#location').val();

            if (input.length > 2) {
                const mainurl = window.location.origin;
                const baseUrl2 = `${mainurl}/dashboard/findmycarsearch/${site_id}/${input}`;
                const baseUrl3 = `${mainurl}/dashboard/findmycarsearch/${site_id}/NA`;
                $.ajax({
                    url: baseUrl2,
                    type: 'GET',
                    success: function(response) {
                        console.log(response);

                        if (response.flag == 3) {
                            $('#showresults').append(
                                `<a href="#" id="not" class="btn btn-sm btn-primary mx-1 mt-2">${response.message}</a>`
                            );
                        } else {
                            $('#showresults').empty();
                            $.each(response.sensorData, function(index, location) {
                                $('#showresults').append(
                                    `<a href="/dashboard/findmycarpost/${location.sensor}" class="mx-2 text-center mb-2"><img class="imgcss" src="{{ asset('uploads/${location.car_image}') }}">${location.number}</a>`
                                );
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
                $.ajax({
                    url: baseUrl3,
                    type: 'GET',
                    success: function(response) {

                        if (response.flag != 3) {
                            $('#showresults2').empty();
                            $('#showresults2').append(`<hr class="mb-3" style="border: 1px solid black;width:90%;">`);
                            $.each(response.sensorData, function(index, location) {
                                $('#showresults2').append(
                                    `<a href="/dashboard/findmycarpost/${location.sensor}" class="mx-2 text-center mb-2"><img class="imgcss2" src="{{ asset('uploads/${location.car_image}') }}">Not Detected</a>`
                                );
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            }
        }

        // Attach the debounced function to the input event
        $('#searchcar').on('input', debounce(handleSearch, 300));
    });
</script>

@endsection