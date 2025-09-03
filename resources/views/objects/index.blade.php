<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage 3D Objects</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Manage 3D Objects</h2>
        <a href="{{ route('objects.create') }}" class="btn btn-primary">Add New Object</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Name</th>
                    <th>OBJ</th>
                    <th>MTL</th>
                    <th>JPG</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($objects as $object)
                <tr>
                    <td>{{ $object->name }}</td>
                    <td>{{ basename($object->obj_file) }}</td>
                    <td>{{ $object->mtl_file ? basename($object->mtl_file) : '-' }}</td>
                    <td>{{ $object->jpg_file ? basename($object->jpg_file) : '-' }}</td>
                    <td>
                        <form action="{{ route('objects.destroy', $object->id) }}" method="POST" onsubmit="return confirm('Delete this object?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">No objects found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
