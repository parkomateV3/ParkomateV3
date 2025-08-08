@extends('header')
@section('content')
<style>
    label {
        margin: 0 !important;
    }
</style>

<!-- https://demo.mobiscroll.com/jquery/select/multiple-lines# -->
<div class="container">
    <h2 class="text-center">Display Symbol Info</h2>

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
    </div>
    @endif

    <!-- Display Success Message -->
    @if (session('message'))
    <div class="alert alert-success">
        {{ session('message') }}
    </div>
    @endif
    <button class="btn btn-primary float-right" data-bs-toggle="modal" data-bs-target="#displayModal">Add Data</button>
    <br><br>
    <div class="row m-auto">
        <div class="col-md-12 m-auto">

            <table id="userTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Display</th>
                        <th scope="col">Symbol</th>
                        <th scope="col">Coordinates</th>
                        <th scope="col">Color</th>
                        <th scope="col">Symbol Image</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($displaysymbolData as $displaysymbol)
                    <tr>
                        <th scope="row">{{ $displaysymbol->ondisplay_id }}</th>
                        <td>{{ getDisplayLocation($displaysymbol->display_id) }}</td>
                        <td>{{ symbolName($displaysymbol->symbol_to_show) }}</td>
                        <td>{{ $displaysymbol->coordinates }}</td>
                        <td>{{ $displaysymbol->color }}</td>
                        <td>
                            @php $symbolimage = symbolImage($displaysymbol->symbol_to_show); @endphp
                            <img src="{{ asset('symbols/'.$symbolimage) }}" class="border border-success rounded-3" width="100px" alt="">
                        </td>
                        <td>
                            <form action="{{route('displaysymbol.destroy', $displaysymbol->ondisplay_id)}}" id="deleteForm-{{ $displaysymbol->ondisplay_id }}" onsubmit="return confirmDelete(event, '{{ $displaysymbol->ondisplay_id }}');" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('displaysymbol.edit', $displaysymbol->ondisplay_id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>


</div>

<div class="modal fade" id="displayModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Display Form</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('displaysymbol.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="display_id" class="form-label">Display</label>
                        <select class="form-select" name="display_id" id="display_id" required>
                            <option value="" selected>Select Display</option>
                            @foreach($displayData as $display)
                            <option value="{{$display->display_id}}">{{$display->location_of_the_display_on_site}} ({{ $display->display_unique_no }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="symbol_id" class="form-label">Symbol</label>
                        <select class="form-select" name="symbol_id" id="symbol_id" required>
                            <option value="" selected>Select Symbol</option>
                            @foreach($symbolData as $symbol)
                            <option value="{{$symbol->symbol_id}}">{{$symbol->symbol_name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="coordinates" class="form-label">Coordinates</label>
                        <input type="text" name="coordinates" class="form-control" id="coordinates" placeholder="x, y" value="{{ old('coordinates') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="text" class="form-control" name="color" placeholder="(R,G,B)" id="color" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Function to handle delete confirmation
    function confirmDelete(event, displayId) {
        event.preventDefault(); // Prevent the default form submission

        const form = document.getElementById(`deleteForm-${displayId}`);

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit(); // Submit the correct form
            }
        });

        return false; // Return false to ensure the form does not submit immediately
    }
</script>

@endsection