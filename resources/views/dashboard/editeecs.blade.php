@extends('dashboard.header')
@section('content')


<div class="grid xl:grid-cols-2 grid-cols-1 gap-6">
    <div class="card">
        <div class="card-body flex flex-col p-6">
            <header
                class="flex mb-5 items-center border-b border-slate-100 dark:border-slate-700 pb-5 -mx-6 px-6">
                <div class="flex-1">
                    <div class="card-title text-slate-900 dark:text-white">Edit Measured EECS Count</div>
                </div>
            </header>
            <div class="card-text h-full space-y-4">

                <form action="{{ route('dashboard/updatecount') }}" method="post">
                    @csrf
                    <input type="hidden" name="counttype" value="measured">
                    <div class="card-text h-full space-y-4">
                        <div class="input-area">
                            <label for="floor_id" class="form-label">Select Floor</label>
                            <select name="floor_id" id="floor_id" class="form-control">
                                <option selected="Selected" disabled="disabled" value="" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600">Select Floor</option>
                                @foreach($floorData as $floor)
                                <option value="{{ $floor->floor_id }}" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600">{{ $floor->floor_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-area">
                            <label for="type" class="form-label">Select Type</label>
                            <select name="type" id="type" class="form-control">
                                <option selected="Selected" disabled="disabled" value="" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600">Select Type</option>
                                
                            </select>
                        </div>
                        <div class="input-area">
                            <label for="count" class="form-label">Update Occupied Count</label>
                            <input type="text" name="count" id="count" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body flex flex-col p-6">
            <header
                class="flex mb-5 items-center border-b border-slate-100 dark:border-slate-700 pb-5 -mx-6 px-6">
                <div class="flex-1">
                    <div class="card-title text-slate-900 dark:text-white">Edit Max EECS Count</div>
                </div>
            </header>
            <div class="card-text h-full space-y-4">

                <form action="{{ route('dashboard/updatecount') }}" method="post">
                    @csrf
                    <input type="hidden" name="counttype" value="max">
                    <div class="card-text h-full space-y-4">
                        <div class="input-area">
                            <label for="maxfloor_id" class="form-label">Select Floor</label>
                            <select name="floor_id" id="maxfloor_id" class="form-control">
                                <option selected="Selected" disabled="disabled" value="" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600">Select Floor</option>
                                @foreach($floorData as $floor)
                                <option value="{{ $floor->floor_id }}" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600">{{ $floor->floor_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-area">
                            <label for="maxtype" class="form-label">Select Type</label>
                            <select name="type" id="maxtype" class="form-control">
                                <option selected="Selected" disabled="disabled" value="" class="py-1 inline-block font-Inter font-normal text-sm text-slate-600">Select Type</option>
                               
                            </select>
                        </div>
                        <div class="input-area">
                            <label for="maxcount" class="form-label">Update Max Count</label>
                            <input type="text" name="count" id="maxcount" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        var typeData = {};
        $('#floor_id').on('change', function() {
            var selectedValue = $(this).val();

            if (selectedValue) {
                $.ajax({
                    url: '{{ url("dashboard/gettypes") }}/' + selectedValue,
                    type: 'GET',
                    success: function(response) {

                        $('#type').empty();
                        $('#type').append('<option value="" selected>Select Type</option>');
                        Object.entries(response).forEach(([key, value]) => {
                            typeData[value.type_id] = value;
                            $('#type').append('<option value="' + value.type_id + '">' + value.type_name + '</option>');
                        });


                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            }

        });

        // On change, set count value
        $('#type').on('change', function() {
            var selectedTypeId = $(this).val();

            if (typeData[selectedTypeId]) {
                $('#count').val(typeData[selectedTypeId].count);
            } else {
                $('#count').val('');
            }
        });


        var maxtypeData = {};
        $('#maxfloor_id').on('change', function() {
            var selectedValue = $(this).val();

            if (selectedValue) {
                $.ajax({
                    url: '{{ url("dashboard/maxgettypes") }}/' + selectedValue,
                    type: 'GET',
                    success: function(response) {

                        $('#maxtype').empty();
                        $('#maxtype').append('<option value="" selected>Select Type</option>');
                        Object.entries(response).forEach(([key, value]) => {
                            maxtypeData[value.type_id] = value;
                            $('#maxtype').append('<option value="' + value.type_id + '">' + value.type_name + '</option>');
                        });


                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            }

        });

        // On change, set count value
        $('#maxtype').on('change', function() {
            var selectedTypeId = $(this).val();

            if (maxtypeData[selectedTypeId]) {
                $('#maxcount').val(maxtypeData[selectedTypeId].count);
            } else {
                $('#maxcount').val('');
            }
        });
    });
</script>
@endsection