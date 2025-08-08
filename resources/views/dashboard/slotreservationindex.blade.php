@extends('dashboard.header')
@section('content')

<style>
    #radarData {
        /* max-width: 400px; */
        touch-action: manipulation;
        margin: auto;
    }

    .text-center {
        text-align: center !important;
    }

    @keyframes blink {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0;
        }

        100% {
            opacity: 1;
        }
    }
</style>

<div class="flex justify-between flex-wrap items-center mb-4" style="margin-bottom: 0px !important;">
    <h4 class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex ce-x-3 rtl:space-x-reverse">
        Dashboard</h4>

    <div class="flex sm:space-x-4 space-x-2 sm:justify-end items-center rtl:space-x-reverse">
        @if($maxcount > 0)
        <strong style="color:red;animation: blink 1s infinite;" class="text-slate-900 inline-block flex space-x-3 rtl:space-x-reverse">Number of overnight stay vehicles: {{$maxcount}}</strong>
        @endif
    </div>

</div>
<div class="flex justify-between flex-wrap items-center mb-4">
    <p class="font-medium capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse dark:text-white" id="lastUpdate">
        Last Update: Just now</p>
    <div class="flex sm:space-x-4 space-x-2 sm:justify-end items-center rtl:space-x-reverse mobileview">
        <button class="btn inline-flex justify-center btn-light shadow-base2" id="live-status-refresh" style="display: none;">
            <span class="flex items-center">
                <i class="bx bx-loader bx-spin font-size-16 align-middle"></i>
                <span>Refreshing</span>
            </span>
        </button>
        <button class="btn inline-flex justify-center btn-success shadow-base2 mobilebtn" id="live">
            <span class="flex items-center">
                <i class="bx bx-bullseye bx-burst font-size-16 align-middle mx-1"></i>
                <span>Live</span>
            </span>
        </button>
        <button class="btn inline-flex justify-center btn-danger shadow-base2 mobilebtn" id="offline" style="display: none;">
            <span class="flex items-center">
                <i class="bx bx-bullseye bx-burst font-size-16 align-middle mx-1"></i>
                <span>Offline</span>
            </span>
        </button>
        <!-- <button id="live-status-refresh" type="button" class="btn btn-light waves-effect" >
            <i class="bx bx-loader bx-spin font-size-16 align-middle me-2"></i>Refreshing
        </button>
        <button id="app-status-online" type="button" class="btn btn-success waves-effect">
            <i class="bx bx-bullseye bx-burst font-size-16 align-middle me-2"></i>Live
        </button>
        <button id="app-status-offline" type="button" class="btn btn-danger waves-effect">
            <i class="bx bx-bullseye bx-burst font-size-16 align-middle me-2"></i>Offline
        </button> -->
    </div>
</div>

<div>
    <div class="grid grid-cols-12 gap-5 apex-charts" id="radarData">

    </div>
</div>

<br>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- dashboard init -->
<script src="{{ asset('dashboard/assets/js/reservationhome.init.js') }}"></script>

@endsection