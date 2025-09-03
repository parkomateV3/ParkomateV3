<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New 3D Object</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">Add New 3D Object</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>There were some problems:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('objects.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label">Object Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">OBJ File <span class="text-danger">*</span></label>
            <input type="file" name="obj_file" class="form-control" accept=".obj" required>
        </div>

        <div class="mb-3">
            <label class="form-label">MTL File (optional)</label>
            <input type="file" name="mtl_file" class="form-control" accept=".mtl">
        </div>

        <div class="mb-3">
            <label class="form-label">JPG File (optional)</label>
            <input type="file" name="jpg_file" class="form-control" accept=".jpg,.jpeg,.png">
        </div>

        <button type="submit" class="btn btn-success">Upload</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
