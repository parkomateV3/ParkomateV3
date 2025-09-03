@extends('dashboard.header')
@section('content')
<style>
    .form-control[readonly] {
        cursor: pointer;
        background-color: white !important;
        color: black !important;
    }

    .text-center {
        text-align: center !important;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- BEGIN: Breadcrumb -->
<!-- <div class="mb-5">
    <ul class="m-0 p-0 list-none">
        <li class="inline-block relative top-[3px] text-base text-primary-500 font-Inter ">
            <a href="index.html">
                <iconify-icon icon="heroicons-outline:home"></iconify-icon>
                <iconify-icon icon="heroicons-outline:chevron-right"
                    class="relative text-slate-500 text-sm rtl:rotate-180"></iconify-icon>
            </a>
        </li>
        <li class="inline-block relative text-sm text-slate-500 font-Inter dark:text-white">
            Summery Report</li>
    </ul>
</div> -->
<!-- END: BreadCrumb -->

<div class="grid xl:grid-cols-1 grid-cols-1 gap-6">
    <div class="card">
        <div class="card-body flex flex-col p-6">
            <header
                class="flex mb-5 items-center border-b border-slate-100 dark:border-slate-700 pb-5 -mx-6 px-6">
                <div class="flex-1">
                    <div class="card-title text-slate-900 dark:text-white">Summary Report</div>
                </div>
            </header>
            <div class="card-text h-full ">
                <div class="grid xl:grid-cols-2 grid-cols-1 gap-6">
                    <div class="card">
                        <div class="card-body flex flex-col p-1">
                            <div class="card-text h-full ">
                                <form class="space-y-4" method="POST" action="{{ route('dashboard/summary-report-post') }}">
                                    @csrf
                                    <div class="grid xl:grid-cols-3 grid-cols-1 gap-6">
                                        <div class="card">
                                            <div class="card-body flex flex-col p-1">
                                                <div class="card-text h-full ">
                                                    <div class="input-area relative" style="margin-top: 0;">
                                                        <label for="startDate" class="form-label">Start Date</label>
                                                        <input type="text" id="startDate" value="{{$startDate}}" name="startdate" class="form-control themecolor" required>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-body flex flex-col p-1">
                                                <div class="card-text h-full ">
                                                    <div class="input-area relative">
                                                        <label for="endDate" class="form-label">End Date</label>
                                                        <input type="text" id="endDate" value="{{$endDate}}" name="enddate" class="form-control themecolor" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card">
                                            <div class="card-body flex flex-col p-1">
                                                <div class="card-text h-full ">
                                                    <div class="input-area relative">
                                                        <label for="filter" class="form-label">Select Filter</label>
                                                        <select name="filter" id="filter" class="form-control themecolor w-full mt-2">

                                                            <option value="site" {{ $floorId == 'site' ? 'selected' : '' }}
                                                                class="py-1 inline-block font-Inter font-normal themecolor text-sm text-slate-600">Complete Site
                                                            </option>
                                                            @if(count($floorData) > 1)
                                                            @foreach($floorData as $floor)
                                                            <option value="{{$floor->floor_id}}"
                                                                class="py-1 inline-block font-Inter font-normal themecolor text-sm text-slate-600" {{ $floorId == $floor->floor_id ? 'selected' : '' }}>{{$floor->floor_name}} (Floor)
                                                            </option>
                                                            @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn inline-flex justify-center btn-dark shadow-base2">Generate</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body flex flex-col p-1">
                            <div class="card-text h-full ">
                                <table class="min-w-full divide-y divide-slate-100 table-fixed dark:divide-slate-700" style="width:fit-content">
                                    <thead class="bg-slate-200 dark:bg-slate-700">
                                        <tr>

                                            <th scope="col" colspan="4" class=" table-th ">
                                                Session Timings
                                            </th>

                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">

                                        <tr class="even:bg-slate-50 dark:even:bg-slate-700">
                                            <td class="table-th">Overnight:</td>
                                            <td class="table-td">12AM to 8AM</td>
                                            <td class="table-th">Morning:</td>
                                            <td class="table-td">8AM to 12PM</td>
                                        </tr>

                                        <tr class="even:bg-slate-50 dark:even:bg-slate-700">
                                            <td class="table-th">Afternoon:</td>
                                            <td class="table-td">12PM to 4PM</td>
                                            <td class="table-th">Evening:</td>
                                            <td class="table-td">4PM to 8PM</td>
                                        </tr>

                                        <tr class="even:bg-slate-50 dark:even:bg-slate-700">
                                            <td class="table-th">Night:</td>
                                            <td class="table-td">8PM to 12AM</td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($flag == 1)
<div class="card mt-5">
    <div class="card-body px-6 pb-6">
        <div class="overflow-x-auto -mx-6">
            <div class="inline-block min-w-full align-middle">
                <div class="container mt-5" style="display: table;">
                    <table class="min-w-full divide-y divide-slate-100 table-fixed dark:divide-slate-700">
                        <thead class="bg-slate-200 dark:bg-slate-700">
                            <tr>

                                @if($floorFlag)
                                <th scope="col" class="text-center table-th ">
                                    Floor
                                </th>
                                @endif

                                <th scope="col" class="text-center table-th ">
                                    Date
                                </th>

                                <th scope="col" class="text-center table-th ">
                                    Day
                                </th>

                                <th scope="col" class="text-center table-th ">
                                    Session
                                </th>

                                <th scope="col" class="text-center table-th ">
                                    Check-In Count
                                </th>

                                <th scope="col" class="text-center table-th ">
                                    Check-Out Count
                                </th>

                                <th scope="col" class="text-center table-th ">
                                    Min Count
                                </th>

                                <th scope="col" class="text-center table-th ">
                                    Max Count
                                </th>

                                <th scope="col" class="text-center table-th ">
                                    Min Time<br>(Minutes)
                                </th>

                                <th scope="col" class="text-center table-th ">
                                    Max Time<br>(Minutes)
                                </th>

                                <th scope="col" class="text-center table-th ">
                                    Avg Time<br>(Minutes)
                                </th>

                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                            @foreach ($combined_data as $date => $sessions)
                            @foreach ($sessions as $sessionData)
                            <tr class="even:bg-slate-50 dark:even:bg-slate-700">
                                @if($floorFlag)
                                <td class="text-center table-td">{{ getFloorname($floorId) }}</td>
                                @endif
                                <td class="text-center table-td">{{ $sessionData['date'] ?? '-' }}</td>
                                <td class="text-center table-td">{{ $sessionData['day'] ?? '-' }}</td>
                                <td class="text-center table-td ">{{ ucfirst($sessionData['session'] ?? '-') }}</td>
                                <td class="text-center table-td ">{{ $sessionData['check_in_count'] ?? '0' }}</td>
                                <td class="text-center table-td ">{{ $sessionData['check_out_count'] ?? '0' }}</td>
                                <td class="text-center table-td ">{{ $sessionData['min_count'] ?? '0' }}</td>
                                <td class="text-center table-td ">{{ $sessionData['max_count'] ?? '0' }}</td>
                                <td class="text-center table-td ">{{ $sessionData['min_time'] ?? '0' }}</td>
                                <td class="text-center table-td ">{{ $sessionData['max_time'] ?? '0' }}</td>
                                <td class="text-center table-td ">{{ $sessionData['avg_time'] ?? '0' }}</td>
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                        <form method="POST" action="{{ route('export-summary-report') }}">
                            @csrf
                            <input type="hidden" name="startdate" value="{{$startDate}}">
                            <input type="hidden" name="enddate" value="{{$endDate}}">
                            <input type="hidden" name="floorFlag" value="{{$floorFlag}}">
                            <input type="hidden" name="floorId" value="{{$floorId}}">
                            <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">
                                <tr class="even:bg-slate-50 dark:even:bg-slate-700">
                                    <td colspan="6">
                                        <button type="submit" class="btn inline-flex justify-center btn-dark shadow-base2 mt-5 mb-1">
                                            Download Report
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </form>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script>
    flatpickr("#startDate", {
        dateFormat: "Y-m-d"
    });
    flatpickr("#endDate", {
        dateFormat: "Y-m-d"
    });
    $(document).ready(function() {
        var theme = localStorage.getItem('theme');

        if (theme == 'dark') {

            $(".themecolor").attr("style", "background-color: #334155 !important;color: white !important;");
        }
    });
</script>
@endsection