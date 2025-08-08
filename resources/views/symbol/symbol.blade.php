@extends('header')
@section('content')
<style>
    label {
        margin: 0 !important;
    }

    .sitelogo {
        display: block;
        width: 130px;
        border: 1px solid black;
        border-radius: 10px;
        margin-top: 5px;
    }
</style>

<!-- https://demo.mobiscroll.com/jquery/select/multiple-lines# -->
<div class="container">
    <h2 class="text-center">Symbol Info</h2>

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
    <a href="https://javl.github.io/image2cpp/" target="_blank" class="btn btn-dark float-left">Image to Binary</a>
    <br><br>
    <div class="row m-auto">
        <div class="col-md-12 m-auto">

            <table id="userTable" class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Symbol Name</th>
                        <!-- <th scope="col">Binary Data</th> -->
                        <th scope="col">Symbol Size</th>
                        <th scope="col">Symbol Image</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($symbolData as $symbol)
                    <tr>
                        <th scope="row">{{ $symbol->symbol_id }}</th>
                        <td>{{ $symbol->symbol_name }}</td>
                        <!-- <td>{{ $symbol->binary_data }}</td> -->
                        <td>{{ $symbol->symbol_size }}</td>
                        <td>
                            <img src="{{ asset('symbols/'.$symbol->symbol_img) }}" class="border border-success rounded-3" width="100px" alt="">
                        </td>
                        <td>
                            <form action="{{route('symbol.destroy', $symbol->symbol_id)}}" id="deleteForm-{{ $symbol->symbol_id }}" onsubmit="return confirmDelete(event, '{{ $symbol->symbol_id }}');" method="post">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                            </form>
                            <a href="{{ route('symbol.edit', $symbol->symbol_id) }}" class="btn btn-sm btn-outline-success ">Edit</a>
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
            <form action="{{ route('symbol.store') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="symbol_name" class="form-label">Symbol Name</label>
                        <input type="text" name="symbol_name" class="form-control" id="symbol_name" value="{{ old('symbol_name') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="binary_data" class="form-label">Binary Data</label>
                        <input type="text" name="binary_data" class="form-control" id="binary_data" value="{{ old('binary_data') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="symbol_size" class="form-label">Symbol Size</label>
                        <input type="text" name="symbol_size" class="form-control" id="symbol_size" placeholder="width, height" value="{{ old('symbol_size') }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="symbol_img" class="form-label">Symbol Image ( Max 2MB )</label>
                        <input type="file" name="symbol_img" class="form-control" id="symbol_img" accept="image/*">
                        <img id="uploadedImage" class="sitelogo" src="#" alt="Uploaded Image" style="display:none;">
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

    document.getElementById('symbol_img').addEventListener('change', function() {
        if (this.files[0]) {
            var picture = new FileReader();
            picture.readAsDataURL(this.files[0]);
            picture.addEventListener('load', function(event) {
                document.getElementById('uploadedImage').setAttribute('src', event.target.result);
                document.getElementById('uploadedImage').style.display = 'block';
            });
        } else {
            document.getElementById('uploadedImage').style.display = 'none';
        }
    });
</script>

@endsection