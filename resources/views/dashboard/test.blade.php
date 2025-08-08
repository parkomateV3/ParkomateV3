<div style="position: relative; display: inline-block;">
    <img src="{{ asset('floors/' . $floorImg) }}" alt="Parking Map" style="width: 1137px;height: 656px;">

    @foreach ($coordinates as $coord)
    <button
        class="parking-btn {{ $coord->status == '1' ? 'occupied' : 'available' }}"
        data-id="{{ $coord->reservation_id }}"
        style="position: absolute; 
               left: {{ $coord->x }}px; 
               top: {{ $coord->y }}px;
               width: 20px; 
               height: 20px;
               border-radius: 50%;">
    </button>
    @endforeach
</div>

<style>
    .parking-btn {
        border: 1px solid #000;
        cursor: pointer;
    }

    .available {
        background-color: green;
    }

    .occupied {
        background-color: red;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $('.parking-btn').on('click', function() {
        const button = $(this);
        const id = button.data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You want to change status!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: '{{ route("toggleStatus") }}',
                    type: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if (res.status == 1) {
                            button.removeClass('available').addClass('occupied');
                        } else {
                            button.removeClass('occupied').addClass('available');
                        }
                    }
                });
            }
        });

    });
</script>