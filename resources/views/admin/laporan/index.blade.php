@extends('layouts.app')

@section('title')
    {{ __('Halaman Laporan') }}
@endsection

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Laporan</h6>
    </div>
    <div class="card-body">

        <div class="d-flex mb-4">
            <button class="btn btn-success mx-2" onclick="location.reload()">Refresh</button>
            <button class="btn btn-primary" id="btnExport">Excel</button>
        </div>

        <span class="mb-4">Filter Bulan</span>
        <div class="container my-4">
            <div class="row">
                <div class="col-sm">
                    <select name="bulan" id="bulan" class="form-control">
                        <option value="">-- Pilih Bulan --</option>
                        @foreach($months as $key => $month)
                            <option value="{{ $key }}">{{ $month }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm">
                    <select name="tahun" id="tahun" class="form-control">
                        <option value="">-- Pilih Tahun --</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm">
                    <select name="tipe_pengajuan" id="tipe_pengajuan" class="form-control">
                        <option value="">-- Pilih Tipe Pengajuan --</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Izin">Izin</option>
                    </select>
                </div>
            </div>
        </div>

        <span class="mb-4">Filter Hari</span>
        <div class="container my-4">
            <div class="row">
                <div class="col-sm">
                    <input type="date" id="todayFilter" name="todayFilter" class="form-control">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="dtLaporans" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>NPP</th>
                        <th>Tanggal Awal</th>
                        <th>Tanggal Akhir</th>
                        <th>Total Cuti</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody id="data-list">
                </tbody>
            </table>
        </div>

        <div id="pagination">
            <!-- Pagination links akan dimuat di sini -->
        </div>

        <form id="exportLaporan" action="laporan/export-laporan" method="POST" target="_blank">
            @csrf
            <input type="hidden" id="bulan_export" name="bulan_export">
            <input type="hidden" id="tahun_export" name="tahun_export">
            <input type="hidden" id="tipe_pengajuan_export" name="tipe_pengajuan_export">
            <input type="hidden" id="hari_export" name="hari_export">
        </form>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    let currentPage = 1; // Halaman saat ini
let searchQuery = ''; // Query pencarian saat ini
let filterBulan = '';
let filterTahun = '';
let filterDate = '';
let filterTipePengajuan = '';

function loadData(page = 1, search = '', bulan = '', tahun = '', tipePengajuan = '') {
    $.ajax({
        url: '{{ route("laporan.getdatajson") }}',
        method: 'GET',
        data: {
            page: page,
            search: search,
            bulan: bulan,
            tahun: tahun,
            tipe_pengajuan: tipePengajuan,
            filter_date: filterDate
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
                            <td>${item.status || '-'}</td>
                            <td>${item.formatted_created_at || '-'}</td>
                            <td>${item.formatted_updated_at || '-'}</td>
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
    loadData(currentPage, searchQuery, filterBulan, filterTahun, filterTipePengajuan, filterDate);
});

// Event: Ketika tombol pagination diklik
$(document).on('click', '.pagination-link', function() {
    const page = $(this).data('page');
    if (page) {
        currentPage = page;
        loadData(currentPage, searchQuery, filterBulan, filterTahun, filterTipePengajuan, filterDate);
    }
});

$(document).ready(function() {
    loadData(currentPage, searchQuery, filterBulan, filterTahun, filterTipePengajuan, filterDate);

    $('#bulan').on('change', function() {
        filterBulan = $(this).val();
        $("#bulan_export").val(filterBulan);
        
        currentPage = 1; // Reset ke halaman pertama
         loadData(currentPage, searchQuery, filterBulan, filterTahun, filterTipePengajuan, filterDate);
    });

    $('#tahun').on('change', function() {
        filterTahun = $(this).val();
        $("#tahun_export").val(filterTahun);

        currentPage = 1; // Reset ke halaman pertama
         loadData(currentPage, searchQuery, filterBulan, filterTahun, filterTipePengajuan, filterDate);
    });

    $('#tipe_pengajuan').on('change', function() {
        filterTipePengajuan = $(this).val();
        $("#tipe_pengajuan_export").val(filterTipePengajuan);

        currentPage = 1; // Reset ke halaman pertama
         loadData(currentPage, searchQuery, filterBulan, filterTahun, filterTipePengajuan, filterDate);
    });

    $('#todayFilter').on('change', function() {
        filterDate = $(this).val();
        $("#hari_export").val(filterDate);
        
        currentPage = 1; // Reset ke halaman pertama
        loadData(currentPage, searchQuery, filterBulan, filterTahun, filterTipePengajuan, filterDate);
    });

    $('#btnExport').on('click', function() {
        const bulan = $("#bulan_export").val();
        const tahun = $("#tahun_export").val();
        const tipe_pengajuan = $("#tipe_pengajuan_export").val();
        const hari = $("#hari_export").val();
        
        $("#exportLaporan").submit();
    });
});

</script>
@endsection
