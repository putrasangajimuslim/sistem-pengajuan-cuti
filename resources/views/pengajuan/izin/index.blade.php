
@extends('layouts.app')

@section('title')
    {{ __('Halaman Pengajuan Izin') }}
@endsection

@section('content')
    <!-- Page Heading -->
    {{-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">List Master Users</h1>
    </div> --}}
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold text-primary">List Pengajuan Izin</h6>
                
                <div class="d-flex align-items-center">
                    {{-- <a href="#" class="btn btn-primary mr-2 d-flex align-items-center justify-content-center custom-button-size" 
                       data-toggle="modal" data-target=".NewInputDivisi">
                        <span class="icon text-white-50 mr-2">
                            <i class="fas fa-plus"></i>
                        </span>
                        <span class="text">Tambah</span>
                    </a> --}}
                    <button class="btn btn-primary mr-2 d-flex align-items-center justify-content-center custom-button-size" id="addButton">
                        <span class="icon text-white-50 mr-2">
                            <i class="fas fa-plus"></i>
                        </span>
                        Tambah
                    </button>
                    <input type="text" id="searchBox" class="form-control custom-input-size" placeholder="Search...">
                </div>
            </div>
            
            <div class="table-responsive mt-3">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>NPP</th>
                            <th>Tanggal Awal</th>
                            <th>Tanggal Akhir</th>
                            <th>Total Izin</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Action</th>
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
                    <h5 class="modal-title" id="modalTitle">Form Tambah Program Izin</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="Programizinform" method="POST" enctype="multipart/form-data">
                        <div class="container-fluid">
                            <!-- Hidden ID Field -->
                            <input type="hidden" name="id" id="dataId">
                        
                            <!-- Input Tanggal Liburan -->
                            <div class="form-group">
                                <label for="start_date">Tanggal Izin</label>
                                <div class="d-flex align-items-center">
                                    <!-- Start Date -->
                                    <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Tanggal Mulai" required>
                                    <div class="text-center">
                                        <div class="bg-primary text-white px-3 py-2 rounded">s/d</div>
                                    </div>
                                    <!-- End Date -->
                                    <input type="text" class="form-control" style="background-color: #fff;" id="end_date" name="end_date" placeholder="Tanggal Akhir" disabled required>
                                </div>
                                <div class="invalid-feedback">Tanggal Mulai dan Tanggal Akhir harus diisi.</div>
                            </div>
    
                            <!-- Informasi Total Pengajuan dan Sisa Cuti -->
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="total_pengajuan_cuti">Total Pengajuan Izin</label>
                                    <input type="text" class="form-control" id="total_pengajuan_izin" name="total_pengajuan_izin" placeholder="0" disabled>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="gender">Jenis <span class="text-danger">*</span></label>
                                    <div class="row ml-auto">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="jenis_izin" id="jenisSakit" value="Sakit">
                                            <label class="form-check-label" for="jenisSakit">Sakit</label>
                                        </div>
                                        <div class="form-check ml-4">
                                            <input class="form-check-input" type="radio" name="jenis_izin" id="jenisIzin" value="Izin Urgent">
                                            <label class="form-check-label" for="jenisIzin">Izin</label>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Silahkan Pilih jenis Izin.</div>
                                </div>
                            </div>

                            <div class="form-group" id="file_upload_container">
                                <label for="file_upload">Unggah Dokumen (Opsional)</label>
                                <input type="file" class="form-control" id="file_upload" name="file_upload" accept=".jpg,.png,.pdf,.doc,.docx" required>
                                <div class="invalid-feedback">Silahkan unggah dokumen yang sesuai format.</div>
                            </div>
    
                            <!-- Keterangan -->
                            <div class="form-group">
                                <label for="keterangan">Keterangan <span class="text-danger">*</span></label>
                                <textarea id="keterangan" name="keterangan" class="form-control" required></textarea>
                                <div class="invalid-feedback">Keterangan harus diisi.</div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Batal</button>
                    <button class="btn btn-primary" type="button" id="saveIzinButton">Simpan</button>
                </div>
            </div>
        </div>
    </div>    

    <div class="modal fade" id="actionModalStatusAppproval" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetailOrApproved">Form Tambah Program Izin</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formStatusApproval">
                        <div class="container-fluid">
                            <p><strong>No Ticket:</strong> <span id="ticket-no"></span></p>
                            <p><strong>Nama Karyawan:</strong> <span id="name-staff"></span></p>
                            <p><strong>Tanggal Mulai:</strong> <span id="start-date"></span></p>
                            <p><strong>Tanggal Akhir:</strong> <span id="end-date"></span></p>
                            <p><strong>Total Pengajuan Izin:</strong> <span id="total-request-days"></span></p>
                            <p><strong>Keterangan:</strong> <span id="keterangan_label"></span></p>
                            <div class="support_document_container">
                                <p><strong>Dokument Pendukung:</strong> <span id="support_document"></span></p>
                                <a href="#" id="redirect-link" target="_blank">
                                    <img src="{{ asset('img/default-image.png') }}" id="default-image">
                                </a>
                            </div>
                            <div class="separator"></div>
                            <p>Riwayat Approval <span id="approval-status"></span></p>

                            <div class="vertical-stepper" id="approval-steps"></div>

                            <div class="separator"></div>
                            
                            <div id="approval-status-action">
                                <p>Approval<span id="approval-status"></span></p>
    
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <div class="row ml-auto">
                                            <input type="hidden" name="id" id="dataId">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="status_approval" id="status_approval" value="APPROVED">
                                                <label class="form-check-label" for="status_approval">Setuju</label>
                                            </div>
                                            <div class="form-check ml-4">
                                                <input class="form-check-input" type="radio" name="status_approval" id="status_approval" value="REJECTED">
                                                <label class="form-check-label" for="status_approval">Tidak</label>
                                            </div>
                                        </div>
                                        <div class="invalid-feedback">Silahkan Pilih Status.</div>
                                    </div>                                                          
                                </div>

                                <div class="form-group" id="reason_rejected">
                                    <label for="reason">Alasan <span class="text-danger">*</span></label>
                                    <textarea id="alasan" name="alasan" class="form-control"></textarea>
                                    <div class="invalid-feedback">Alasan harus diisi.</div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" id="modal-status-action-button" class="btn btn-primary">Submit</button>
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
            url: '{{ route("pengajuan.izin.getdatajson") }}',
            method: 'GET',
            data: {
                page: page,
                search: search
            },
            success: function(response) {
                let rows = '';
                if (response.data.length > 0) {
                    response.data.forEach(function(item) {
                        const userRole = item.user_role;
                        const approved = item.is_approver;

                        let approveButton = '';

                        if (approved) {
                            approveButton = `<a class="dropdown-item status-btn" href="#" data-id="${item.id}" data-action="approve">Approve</a>`;
                        }

                        rows += `
                            <tr>
                                <td>${item.no_ticket || '-'}</td>
                                <td>${item.npp || '-'}</td>
                                <td>${item.start_date || '-'}</td>
                                <td>${item.end_date || '-'}</td>
                                <td>${item.total_days || '-'}</td>
                                <td>${item.approval_status || '-'}</td>
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
                                            <a class="dropdown-item status-btn" href="#" data-id="${item.id}" data-action="detail">Detail</a>
                                            ${approveButton}
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

    $(document).ready(function() {
        loadData(currentPage, searchQuery);

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });

        $('#years').on('input', function () {
            const yearInput = $(this).val();
            const regex = /^[0-9]{0,4}$/; // Hanya angka dengan maksimal 4 digit

            if (!regex.test(yearInput)) {
                $(this).val(yearInput.replace(/[^0-9]/g, '')); // Hapus karakter non-angka
                $(this).val(yearInput.slice(0, 4));
                $('.error-message').show(); // Tampilkan pesan error
            } else {
                $('.error-message').hide(); // Sembunyikan pesan error
            }
        });

        $('#start_date').multiDatesPicker({
            dateFormat: "yy-mm-dd", // Format tanggal
            maxPicks: 2,           // Maksimal tanggal yang dapat dipilih
            onSelect: function () {
                let dates = $('#start_date').multiDatesPicker('getDates');
                
                // Periksa jika ada dua tanggal yang dipilih
                if (dates.length >= 2) {
                    let startDate = new Date(dates[0]);
                    let endDate = new Date(dates[1]);

                    // Set value untuk start_date dan end_date berdasarkan array dates
                    $('#start_date').val(dates[0]); // Tanggal pertama
                    $('#end_date').val(dates[1]);   // Tanggal kedua

                    let diffDays = calculateDaysDifference(startDate, endDate);

                    $('#total_pengajuan_izin').val(diffDays);
                } else {
                    const oneLeave = 1;
                    $('#start_date').val(dates[0]); // Tanggal pertama
                    $('#end_date').val(dates[0]); 
                    $('#total_pengajuan_izin').val(oneLeave);
                }
            },
        });

        $('#addButton').click(function () {
            $('#Programizinform')[0].reset();
            $('#dataId').val('');
            $('#modalTitle').text('Form Pengajuan Izin');
            $('#saveIzinButton').text('Simpan').prop('disabled', true);
            $('#dataModal').modal('show');
            $('#file_upload_container').hide();
        });
        
        function calculateDaysDifference(startDate, endDate) {
            // Menghitung selisih dalam milidetik
            let timeDifference = endDate - startDate;

            // Menghitung jumlah hari
            let dayDifference = timeDifference / (1000 * 3600 * 24);

            return dayDifference + 1; // Tambah 1 untuk menghitung termasuk tanggal awal
        }

        function checkFormValidity() {
            const isFormValid = $('#Programizinform')[0].checkValidity();
        
            $('#saveIzinButton').prop('disabled', !isFormValid);
        }

        function checkFormValidityModalApprovedOrRejected() {
            const isFormValid = $('#formStatusApproval')[0].checkValidity();

            $('#modal-status-action-button').prop('disabled', !isFormValid);
        }

        $('#Programizinform').on('input change', function () {
            checkFormValidity();
        });

        $('#formStatusApproval').on('input change', function () {
            checkFormValidityModalApprovedOrRejected();
        });

        $("#modal-status-action-button").click(function(){
            const id = $('#dataId').val();
            let status_approval = $('input[name="status_approval"]:checked').val();
            const alasan = $('#alasan').val();
            
            $.ajax({
                url: '{{ route("pengajuan.cuti.updatestatusleave") }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    data_id: id,
                    status_approval: status_approval,
                    alasan: alasan,
                },
                success: function (response) {
                    if(response.success) {
                        $('#actionModalStatusAppproval').modal('hide');
                        $('#formStatusApproval')[0].reset(); // Reset form
                        $('#modal-status-action-button').prop('disabled', true); 

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

        $('input[name="status_approval"]').change(function() {
            // var selectedValue = $('input[name="status_approval"]:checked').val();

            var value = $(this).val();

            $('#reason_rejected').hide();
            $('#alasan').removeAttr('required');

            if (value == 'REJECTED') {
                $('#reason_rejected').show();
                $('#alasan').attr('required', true);
            }
        });

        $('input[name="jenis_izin"]').change(function() {

            var value = $(this).val();

            $('#file_upload_container').hide();
            $('#file_upload').removeAttr('required');

            $('#reason').removeAttr('required');
            if (value == 'Sakit') {
                $('#file_upload_container').show();
                $('#file_upload').attr('required', true);
            }
        });

        $(document).on('click', '.status-btn', function () {
            const action = $(this).data('action');
            $('#reason_rejected').hide();
            
            const id = $(this).data('id');
            let dataId = $("#dataId").val(id);
            $('#actionModalStatusAppproval').modal('show');

            let Url = "{{ route('pengajuan.izin.getdataleaveticketjson', ':id') }}".replace(':id', id);

            $.ajax({
                url: Url,
                method: 'GET',
                success: function (res) {
                    let response = res.data;
                    let history_approval = res.request_steps;
                    $('#ticket-no').html(`<strong>${response.no_ticket}</strong>`);

                    let totalPengajuan = response.total_days;

                    if (response.leave_requests && response.leave_requests.length > 0) {
                        var leaveRequest = response.leave_requests[0]; // Ambil leave request terbaru
                        
                        $('#name-staff').html(`<strong>${leaveRequest.requester.first_name}</strong>`);
                        $('#total-request-days').html(`<strong>${totalPengajuan}</strong>`);
                    }

                    if (history_approval && history_approval.length > 0) {

                        // Menambahkan langkah-langkah ke dalam elemen #approval-steps
                        var stepsContainer = $('#approval-steps');
                        
                        // Menghapus isi lama agar data baru tidak digandakan
                        stepsContainer.empty();

                        history_approval.forEach(function(step, index) {
                            var stepHtml = `
                                <div class="step">
                                    <div class="step-marker">${index + 1}</div>
                                    <div class="step-line"></div>
                                    <div class="step-content">
                                        <div class="step-title">${step.step}</div>
                                        <div class="step-description">${step.message}</div>
                                    </div>
                                </div>
                            `;
                            stepsContainer.append(stepHtml);
                        });
                    }

                    $('#start-date').html(`<strong>${response.start_date}</strong>`);
                    $('#end-date').html(`<strong>${response.end_date}</strong>`);
                    $('#keterangan_label').html(`<strong>${response.reason}</strong>`);

                    if (response.media_url) {
                        // Replace the src attribute of the default image
                        const fileUrl = `${window.location.origin}/storage/${response.media_url}`;

                        $('#redirect-link').attr('href', fileUrl);
                        $('#default-image').attr('src', fileUrl);
                    }

                },
                error: function (xhr) {
                    alert(xhr.responseJSON.message || 'Terjadi kesalahan.');
                }
            });

            let text = '';
            if (action == 'detail') {
                text = 'Detail Persetujuan Pengajuan Izin Ticket';
                $('#approval-status-action').hide();
                $('#approval-status-action').hide();
                $('.modal-footer').hide();
                
            } else {
                text = 'Form Persetujuan Pengajuan Izin Ticket';
                $('#approval-status-action').show();
                $('.modal-footer').show();
                $('#modal-status-action-button').text('Submit').prop('disabled', true);
            }

            $('#modalDetailOrApproved').text(text);
        });

        $("#saveIzinButton").click(function(){
            let start_date = $("#start_date").val();
            let end_date = $("#end_date").val();
            let keterangan = $("#keterangan").val();
            var jenis_izin = $('input[name="jenis_izin"]:checked').val();
            let total_pengajuan_izin = $("#total_pengajuan_izin").val();

            const fileInput = $('#file_upload')[0].files[0]; // Ambil file dari input
            let formData = new FormData();

            // Menambahkan data lainnya ke FormData
            formData.append('start_date', start_date);
            formData.append('end_date', end_date);
            formData.append('keterangan', keterangan);
            formData.append('jenis_izin', jenis_izin);
            formData.append('total_pengajuan_izin', total_pengajuan_izin);

            // Jika ada file yang diunggah, tambahkan ke FormData
            if (fileInput) {
                formData.append('file_upload', fileInput);
            }
            
            formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

            $.ajax({
                url: '{{ route("pengajuan.izin.store") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if(response.success) {
                        $('#dataModal').modal('hide');
                        $('#Programizinform')[0].reset(); // Reset form
                        $('#saveIzinButton').prop('disabled', true); 

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
        checkFormValidityModalApprovedOrRejected();
    });
</script>
@endsection