
@extends('layouts.app')

@section('title')
    {{ __('Halaman Masters Divisi') }}
@endsection

@section('content')
    <!-- Page Heading -->
    {{-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">List Master Users</h1>
    </div> --}}
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold text-primary">List Master Divisi</h6>
                
                <div class="d-flex align-items-center">
                    {{-- <a href="#" class="btn btn-primary mr-2 d-flex align-items-center justify-content-center custom-button-size" 
                       data-toggle="modal" data-target=".NewInputDivisi">
                        <span class="icon text-white-50 mr-2">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="text">Tambah</span>
                    </a> --}}

                    @can('tambah_divisi')
                        <button class="btn btn-primary mr-2 d-flex align-items-center justify-content-center custom-button-size" id="addButton">
                            <span class="icon text-white-50 mr-2">
                                <i class="fas fa-plus"></i>
                            </span>
                            Tambah
                        </button>
                    @endcan
                    <input type="text" id="searchBox" class="form-control custom-input-size" placeholder="Search...">
                </div>
            </div>
            
            <div class="table-responsive mt-3">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            @canany(['edit_divisi', 'destroy_divisi'])
                                <th>Action</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody id="data-list">
                    </tbody>
                </table>
            </div>

            <div id="pagination">
                <!-- Pagination links akan dimuat di sini -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="dataModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Form Tambah Divisi</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="divisiForm">
                        <div class="container-fluid">
                            <div class="form-group">
                                <input type="hidden" name="id" id="dataId">
                                <label for="nama">Nama <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama" name="name" placeholder="Masukkan Nama" required>
                                <div class="invalid-feedback">Nama harus diisi.</div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="button" id="saveDivisiButton">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    
    
    <!-- Content Row -->
@endsection

@section('script')
<script>
    let currentPage = 1; // Halaman saat ini
    let searchQuery = ''; // Query pencarian saat ini

    function loadData(page = 1, search = '') {
        $.ajax({
            url: '{{ route("divisi.getdatajson") }}',
            method: 'GET',
            data: {
                page: page,
                search: search
            },
            success: function(response) {
                let rows = '';
                if (response.data.length > 0) {
                    response.data.forEach(function(item) {
                        rows += `
                            <tr>
                                <td>${item.name}</td>
                                <td>${item.formatted_created_at || '-'}</td>
                                <td>${item.formatted_updated_at || '-'}</td>
                                <td>
                                    <div class="dropdown no-arrow mb-4">
                                        <button class="btn btn-primary dropdown-toggle" type="button"
                                            id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">
                                            Action
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item edit-btn" href="#" data-id="${item.id}">Edit</a>
                                            <a class="dropdown-item remove-btn" href="#" data-id="${item.id}">Hapus</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    rows = `
                        <tr>
                            <td colspan="4" class="text-center">Data tidak ada</td>
                        </tr>
                    `;
                }

                $('#data-list').html(rows);

                // Render pagination
                if (response.data.length > 0 && response.links) {
                    let pagination = response.links.map((link, index) => {
                        let label = link.label;
                        if (index === 0) label = 'Previous';
                        if (index === response.links.length - 1) label = 'Next';

                        return `
                            <button class="btn ${link.active ? 'btn-primary' : 'btn-light'} pagination-link" 
                                    data-page="${link.url ? new URL(link.url).searchParams.get('page') : ''}">
                                ${label}
                            </button>
                        `;
                    }).join('');

                    $('#pagination').html(pagination).show();
                } else {
                    $('#pagination').hide();
                }
            }
        });
    }

    // Event: Ketika pencarian berubah
    $('#searchBox').on('input', function() {
        searchQuery = $(this).val();
        currentPage = 1; // Reset ke halaman pertama
        loadData(currentPage, searchQuery);
    });

    // Event: Ketika tombol pagination diklik
    $(document).on('click', '.pagination-link', function() {
        const page = $(this).data('page');
        if (page) {
            currentPage = page;
            loadData(currentPage, searchQuery);
        }
    });

    $(document).ready(function() {
        loadData(currentPage, searchQuery);

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });

        $('#addButton').click(function () {
            $('#divisiForm')[0].reset();
            $('#dataId').val('');
            $('#modalTitle').text('Tambah Data');
            $('#saveDivisiButton').text('Simpan').prop('disabled', true);
            $('#dataModal').modal('show');
        });

        $(document).on('click', '.edit-btn', function () {
            const id = $(this).data('id');
            let editUrl = "{{ route('divisi.edit', ':id') }}".replace(':id', id);

            $.ajax({
                url: editUrl,
                method: 'GET',
                success: function (response) {
                    $('#dataId').val(response.id);
                    $('#nama').val(response.name);
                    $('#modalTitle').text('Edit Data');
                    $('#saveDivisiButton').text('Update').prop('disabled', false);
                    $('#dataModal').modal('show');
                },
                error: function () {
                    alert('Gagal mengambil data.');
                }
            });
        });

        $(document).on('click', '.remove-btn', function () {
            const id = $(this).data('id');
            const deleteUrl = "{{ route('divisi.destroy', ':id') }}".replace(':id', id);

            Swal.fire({
                title: "Anda Yakin?",
                text: "Anda tidak akan dapat mengembalikan ini!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, hapus saja!",
                cancelButtonText: 'Batal'
                }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}' // Token CSRF Laravel
                        },
                        success: function (response) {
                            Toast.fire({
                                text: response.message,
                                icon: "success"
                            });
                            // Reload data setelah sukses
                            loadData(currentPage, searchQuery);
                        },
                        error: function (xhr) {
                            Swal.fire(
                                'Gagal!',
                                xhr.responseJSON.message || 'Terjadi kesalahan saat menghapus data.',
                                'error'
                            );
                        }
                    });
                }
            });
        });

        function checkFormValidity() {
            const isFormValid = $('#divisiForm')[0].checkValidity();
            $('#saveDivisiButton').prop('disabled', !isFormValid);
        }

        $('#divisiForm').on('input change', function () {
            checkFormValidity();
        });

        $("#saveDivisiButton").click(function(){
            const id = $('#dataId').val();
            const url = id ? "{{ route('divisi.update', ':id') }}".replace(':id', id) : "{{ route('divisi.store') }}";
            const method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    ...$('#divisiForm').serializeArray().reduce((obj, item) => {
                        obj[item.name] = item.value;
                        return obj;
                    }, {})
                },
                success: function (response) {
                    if(response.success) {
                        $('#dataModal').modal('hide');
                        $('#divisiForm')[0].reset(); // Reset form
                        $('#saveDivisiButton').prop('disabled', true); 

                        Toast.fire({
                            text: response.message,
                            icon: "success"
                        });

                        loadData(currentPage, searchQuery);
                    }
                },
                error: function (xhr) {
                    alert(xhr.responseJSON.message || 'Terjadi kesalahan.');
                }
            });
        }); 

        checkFormValidity();
    });
</script>
@endsection