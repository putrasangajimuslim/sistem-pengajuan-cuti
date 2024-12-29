
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
                <h6 class="mb-0 font-weight-bold text-primary">List Master Users</h6>
                
                <div class="d-flex align-items-center">
                    @can('tambah_user')
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
                            <th>Email</th>
                            <th>Npp</th>
                            <th>Role</th>
                            <th>Divisi</th>
                            <th>Status Karyawan</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            @canany(['edit_user', 'destroy_user'])
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Form Tambah Users</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <input type="hidden" name="id" id="dataId">
                                    <label for="first_name">Nama Depan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Masukkan Nama Depan">
                                    <div class="invalid-feedback">Nama Depan harus diisi.</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="middle_name">Nama Tengah <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Masukkan Nama Tengah">
                                    <div class="invalid-feedback">Nama Tengah tidak valid.</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="last_name">Nama Akhir<span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Masukkan Nama Akhir">
                                    <div class="invalid-feedback">Nama Akhir harus diisi.</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="email">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan Email">
                                    <div class="invalid-feedback">Email tidak valid.</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="password">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control position-relative" id="password" name="password" placeholder="Masukkan Password">
                                    <span class="position-absolute" style="top: 56%; right: 22px">
                                        <i class="fa fa-eye-slash" id="togglePassword"></i>
                                    </span>
                                    <div class="invalid-feedback">Password harus diisi.</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="password_confirmation">Konfirmasi Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control position-relative" id="password_confirmation" name="password_confirmation" placeholder="Konfirmasi Password">
                                    <span class="position-absolute" style="top: 56%; right: 22px">
                                        <i class="fa fa-eye-slash" id="togglePasswordConfirmation"></i>
                                    </span>
                                    <div class="invalid-feedback">Konfirmasi password harus sesuai.</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label for="gender">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <div class="row ml-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="gender" id="genderMale" value="Laki-Laki">
                                            <label class="form-check-label" for="genderMale">Laki-Laki</label>
                                        </div>
                                        <div class="form-check ml-4">
                                            <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="Perempuan">
                                            <label class="form-check-label" for="genderFemale">Perempuan</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Silahkan Pilih jenis kelamin.</div>
                                </div>                                                          
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="role">Role <span class="text-danger">*</span></label>
                                    <select class="form-select form-control" aria-label="Silahkan Pilih Divisi" id="role" name="role">
                                        <option selected>-- Silahkan Pilih Role --</option>
                                        <option value="Manager">Manager</option>
                                        <option value="Supervisor">Supervisor</option>
                                        <option value="Staff">Staff</option>
                                        <option value="HRD">HRD</option>
                                    </select>
                                    <div class="invalid-feedback">Role harus diisi.</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="divisi">Divisi <span class="text-danger">*</span></label>
                                    <select class="form-select form-control"  aria-label="Silahkan Pilih Divisi" id="divisi" name="divisi_id">
                                        <option selected>-- Silahkan Pilih Divisi --</option>
                                        @foreach ($divisions as $division)
                                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                                        @endforeach
                                      </select>
                                    <div class="invalid-feedback">Silahkan Pilih Divisi.</div>
                                </div>
                            </div>

                            {{-- <div class="row">
                                <div class="col-md-12 mt-3" id="roleTableContainer" style="display:none;">
                                    <h6>Silahkan Pilih Staff</h6>

                                    <!-- Search Input -->
                                    <input type="text" id="searchBoxStaff" class="form-control mb-3" placeholder="Search...">

                                    <div class="table-responsive">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                    <th>Npp</th>
                                                </tr>
                                            </thead>
                                            <tbody id="roleTableBody">
                                                <!-- Table data will be populated here -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <nav aria-label="Page navigation">
                                        <ul class="pagination justify-content-center" id="paginationControls">
                                            <!-- Pagination buttons will be generated here -->
                                        </ul>
                                    </nav>
                                </div>
                            </div> --}}

                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="status_karyawan">Status Karyawan <span class="text-danger">*</span></label>
                                    <select class="form-select form-control" aria-label="Silahkan Status Karyawan" name="status_karyawan" id="status_karyawan">
                                        <option selected>-- Silahkan Pilih Status Karyawan --</option>
                                        <option value="Tetap">Tetap</option>
                                        <option value="Kontrak">Kontrak</option>
                                        <option value="Training">Training</option>
                                      </select>
                                    <div class="invalid-feedback">Silahkan Pilih Status Karyawan.</div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tgl_masuk">Tanggal Masuk <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tgl_masuk" name="tgl_masuk" placeholder="Masukkan Tanggal Masuk" required>
                                    <div class="invalid-feedback">Tanggal Masuk diisi.</div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="button" id="saveUserButton">Simpan</button>
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
            url: '{{ route("users.getdatajson") }}',
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
                                <td>${item.first_name} ${item.last_name}</td>
                                <td>${item.email || '-'}</td>
                                <td>${item.npp || '-'}</td>
                                <td>${item.role || '-'}</td>
                                <td>${item.division?.name || '-'}</td>
                                <td>${item.employee_status || '-'}</td>
                                <td>${item.formatted_updated_at || '-'}</td>
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
                            <td colspan="9" class="text-center">Data tidak ada</td>
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

    const togglePassword = document.getElementById('togglePassword');
    const password = document.getElementById('password');

    const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
    const passwordConfirm = document.getElementById('password_confirmation');

    togglePassword.addEventListener('click', function () {
        // Toggle the type attribute
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        // Toggle the eye icon
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    togglePasswordConfirmation.addEventListener('click', function () {
        // Toggle the type attribute
        const type = passwordConfirm.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordConfirm.setAttribute('type', type);
        // Toggle the eye icon
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
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
            $('#userForm')[0].reset();
            $('#dataId').val('');
            $('#modalTitle').text('Tambah Data');
            $('#saveUserButton').text('Simpan').prop('disabled', true);
            $('#dataModal').modal('show');
        });

        $(document).on('click', '.edit-btn', function () {
            const labelConfirm = document.querySelector('label[for="password_confirmation"] span');
            const labelPass = document.querySelector('label[for="password"] span');
            
            if (labelPass) {
                labelPass.remove(); // Menghapus elemen span di dalam label
            }

            if (labelConfirm) {
                labelConfirm.remove(); // Menghapus elemen span di dalam label
            }

            const id = $(this).data('id');
            let editUrl = "{{ route('users.edit', ':id') }}".replace(':id', id);

            $.ajax({
                url: editUrl,
                method: 'GET',
                success: function (response) {
                    $('#dataId').val(response.id);
                    $('#first_name').val(response.first_name);
                    $('#middle_name').val(response.middle_name);
                    $('#last_name').val(response.last_name);
                    $('#tgl_masuk').val(response.join_date);
                    $('#role').val(response.role).change();

                    if (response.gender === 'Laki-Laki') {
                        $('#genderMale').prop('checked', true);
                    } else if (response.gender === 'Perempuan') {
                        $('#genderFemale').prop('checked', true);
                    }
                    
                    $('#divisi').val(response.divisi_id).change();
                    $('#status_karyawan').val(response.employee_status).change();
                    $('#email').val(response.email);
                    $('#modalTitle').text('Edit Data');
                    $('#saveUserButton').text('Update').prop('disabled', false);
                    $('#dataModal').modal('show');
                },
                error: function () {
                    alert('Gagal mengambil data.');
                }
            });
        });

        $(document).on('click', '.remove-btn', function () {
            const id = $(this).data('id');
            const deleteUrl = "{{ route('users.destroy', ':id') }}".replace(':id', id);

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
            const isFormValid = $('#userForm')[0].checkValidity();
            $('#saveUserButton').prop('disabled', !isFormValid);
        }

        $('#userForm').on('input change', function () {
            checkFormValidity();
        });

        $("#saveUserButton").click(function(){
            const id = $('#dataId').val();
            const url = id ? "{{ route('users.update', ':id') }}".replace(':id', id) : "{{ route('users.store') }}";
            const method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    ...$('#userForm').serializeArray().reduce((obj, item) => {
                        obj[item.name] = item.value;
                        return obj;
                    }, {})
                },
                success: function (response) {
                    if(response.success) {
                        $('#dataModal').modal('hide');
                        $('#userForm')[0].reset(); // Reset form
                        $('#saveUserButton').prop('disabled', true); 

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

        // $('#role, #divisi').on('change', function () {
        //     // Ambil nilai Role dan Divisi
        //     const role = $('#role').val();
        //     const division = $('#divisi').val();

        //     if (role === 'Supervisor' && division) {
        //         $('#roleTableContainer').show();
        //     } else {
        //         $('#roleTableContainer').hide();
        //     }
        // });
    });
</script>
@endsection