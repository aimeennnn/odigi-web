@php
    $sort = request('sort', '');
    $order = request('order', 'asc');
    $sort_link = function($col, $label) {
        $sort = request('sort', '');
        $order = request('order', 'asc');
        $isActive = $sort === $col;
        $nextOrder = $isActive && $order === 'asc' ? 'desc' : 'asc';
        $upColor = $isActive && $order === 'asc' ? 'style="color:#1dd1a1;font-weight:bold;"' : 'style="color:#bbb;"';
        $downColor = $isActive && $order === 'desc' ? 'style="color:#1dd1a1;font-weight:bold;"' : 'style="color:#bbb;"';
        $icon = '<span class="ms-1" style="font-size:1rem;vertical-align:middle;">
            <span '.$upColor.'>&uarr;</span><span '.$downColor.'>&darr;</span>
        </span>';
        $url = request()->fullUrlWithQuery(['sort' => $col, 'order' => $nextOrder]);
        return '<a href="'.$url.'" class="text-dark fw-bold" style="text-decoration:none;">'.$label.$icon.'</a>';
    };
@endphp
@extends('layout.master')

@section('main-content')
<!-- Tambahkan link ke CSS register -->
<link rel="stylesheet" href="{{ asset('assets/css/register_style.css') }}">
<div class="container mt-4">
    <!-- Header Card Gradient Hijau-Biru -->
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); border-radius: 18px; position: relative; min-height: 120px;">
        <div>
            <h2 class="fw-bold mb-1 text-white">Register Data</h2>
            <div class="text-white-50" style="font-size: 1.1rem;">Data Registrasi SLIK OJK & Pengajuan</div>
        </div>
        <span style="position:absolute; top:16px; right:32px;">
            <i class="bi bi-journal-check" style="font-size:4rem; color:#fff; opacity:0.25;"></i>
        </span>
    </div>

    <!-- Card Filter Putih -->
    <div class="card mb-4 shadow-sm" style="border-radius: 18px;">
        <div class="card-body">
            <div class="mb-3 fw-bold d-flex align-items-center gap-2" style="font-size: 1.2rem;">
                <i class="bi bi-funnel"></i> Filter Arsip
            </div>
            <form class="row align-items-end g-3">
                <div class="col-md-4">
                    <label for="filter_nama" class="form-label">Nama</label>
                    <input type="text" class="form-control" id="filter_nama" name="filter_nama" placeholder="CARI NAMA NASABAH..." style="text-transform: uppercase;">
                </div>
                <div class="col-md-4">
                    <label for="filter_status" class="form-label">Status Pengajuan</label>
                    <select class="form-select" id="filter_status" name="filter_status">
                        <option value="">Semua Status</option>
                        <option value="Dalam Proses">Dalam Proses</option>
                        <option value="Menunggu Komite">Menunggu Komite</option>
                        <option value="Disetujui">Disetujui</option>
                        <option value="Ditolak">Ditolak</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter_jns_pengajuan" class="form-label">Jenis Pengajuan</label>
                    <select class="form-select" id="filter_jns_pengajuan" name="filter_jns_pengajuan">
                        <option value="">Semua Jenis</option>
                        @foreach(\App\Models\Register::jenisPengajuanList() as $key => $label)
                            <option value="{{ $key }}" {{ request('filter_jns_pengajuan') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-register-blue"><i class="bi bi-search me-1"></i>Filter</button>
                    <!-- <button type="button" class="btn" style="background:#FFB84C; color:white; font-weight:600;"><i class="bi bi-printer me-1"></i>Print</button> -->
                    <button type="button" class="btn" style="background:#FF8B7B; color:white; font-weight:600;" onclick="window.location='{{ route('register.index') }}'">
                        <i class="bi bi-x-circle me-1"></i>Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification untuk Success -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show position-fixed" style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tombol dan Tabel Data SLIK -->
    <div class="card mb-4 shadow-sm" style="border-radius: 18px;">
        <div class="card-body">
            <div class="d-flex justify-content-end mb-3 gap-2">
                @if(\App\Helpers\RoleHelper::canCreate('register'))
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalPilihEntitas"><i class="bi bi-plus-circle me-1"></i>Tambah Data</button>
                @endif
                <!-- <button class="btn btn-register-yellow"><i class="bi bi-download me-1"></i>Ekspor Data Terpilih</button> -->
                @if(\App\Helpers\RoleHelper::canDelete('register'))
                    <button id="btnBulkRegister" type="button" class="btn btn-register-danger"><i class="bi bi-trash me-1"></i>Hapus</button>
                @endif
            </div>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <label class="form-label mb-0 me-2">Show
                        <select class="form-select d-inline-block w-auto mx-1" style="min-width:60px;" onchange="changePerPage(this.value)">
                            <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5</option>
                            <option value="10" {{ request('per_page', 5) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 5) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 5) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 5) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        entries
                    </label>
                </div>
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" placeholder="CARI ARSIP..." id="searchRegister" style="text-transform: uppercase;" autocomplete="off">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-bordered">
                    <thead style="background: #e6f9f5;">
                        <tr>
                            <th scope="col">Pilih</th>
                            <th scope="col">{!! $sort_link('nomor', 'Nomor') !!}</th>
                            <th scope="col">{!! $sort_link('tgl_pengajuan', 'Tanggal Pengajuan') !!}</th>
                            <th scope="col">{!! $sort_link('nama', 'Nama') !!}</th>
                            <th scope="col">{!! $sort_link('jenis_entitas', 'Jenis Entitas') !!}</th>
                            <!-- <th scope="col">{!! $sort_link('nama_badan_usaha', 'Nama Badan Usaha') !!}</th> -->
                            <!-- <th scope="col">{!! $sort_link('jns_kelamin', 'Jenis Kelamin') !!}</th> -->
                            <!-- <th scope="col">{!! $sort_link('pekerjaan', 'Pekerjaan') !!}</th> -->
                            <!-- <th scope="col">{!! $sort_link('alamat', 'Alamat') !!}</th> -->
                            <!-- <th scope="col">{!! $sort_link('jns_pengajuan', 'Jenis Pengajuan') !!}</th> -->
                            <th scope="col">{!! $sort_link('nominal_pengajuan', 'Nominal Pengajuan') !!}</th>
                            <th scope="col">{!! $sort_link('jw_pengajuan', 'Jangka Waktu Pengajuan') !!}</th>
                            <!-- <th scope="col">{!! $sort_link('jaminan', 'Jaminan') !!}</th> -->
                            <th scope="col">Tanggal Realisasi</th>
                            <th scope="col">Nominal Disetujui</th>
                            <th scope="col">{!! $sort_link('status', 'Status') !!}</th>
                            <th scope="col">{!! $sort_link('id_user', 'Nama Petugas') !!}</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registers as $reg)
                        <tr>
                            <td><input type="checkbox" value="{{ $reg->id_reg }}"></td>
                            <td class="text-start">{{ $reg->nomor }}</td>
                            <td>{{ \Carbon\Carbon::parse($reg->tgl_pengajuan)->translatedFormat('d F Y') }}</td>
                            <td class="text-start">
                                <a href="{{ route('register.show', \Illuminate\Support\Facades\Crypt::encryptString($reg->id_reg)) }}" 
                                   class="nama-link">
                                    {{ $reg->nama }}
                                </a>
                            </td>
                            <td>
                                @php
                                    $label = \App\Models\Register::jenisEntitasList()[$reg->jenis_entitas] ?? '-' ;
                                @endphp
                                {{ $label }}
                            </td>
                            <!-- <td>{{ $reg->nama_badan_usaha ?? '-' }}</td> -->
                            <!-- <td>{{ $reg->jns_kelamin }}</td> -->
                            <!-- <td>{{ $reg->pekerjaan }}</td>
                            <td>{{ $reg->alamat }}</td> -->
                            <!-- <td>
                              @php
                                $jnsPengajuanMap = [1 => 'Umum', 2 => 'KTA', 3 => 'Lain-lain'];
                              @endphp
                              {{ $jnsPengajuanMap[$reg->jns_pengajuan] ?? $reg->jns_pengajuan }}
                            </td> -->
                            <td class="text-start">{{ number_format($reg->nominal_pengajuan,0,',','.') }}</td>
                            <td class="text-start">{{ $reg->jw_pengajuan }}</td>
                            <!-- <td>{{ $reg->jaminan }}</td> -->
                            <td>
                                {{ $reg->tanggal_realisasi ? \Carbon\Carbon::parse($reg->tanggal_realisasi)->translatedFormat('d F Y') : '-' }}
                            </td>
                            <td>
                                {{ $reg->nominal_disetujui ? number_format($reg->nominal_disetujui,0,',','.') : '-' }}
                            </td>
                            <td>
                                @php
                                    // Default warna untuk "Dalam Proses"
                                    $badgeColor = '#b2f2e5';
                                    $textColor = '#1dd1a1';
                                    
                                    // Mapping warna berdasarkan status (mendukung numerik dan teks)
                                    $statusValue = strtolower($reg->status);
                                    if($statusValue == '2' || $statusValue == 'menunggu komite'){
                                        $badgeColor='#fff3cd';
                                        $textColor='#f1c40f';
                                    }
                                    elseif($statusValue == '4' || $statusValue == 'ditolak'){
                                        $badgeColor='#ffe0e0';
                                        $textColor='#ff6b6b';
                                    }
                                    elseif($statusValue == '3' || $statusValue == 'disetujui'){
                                        $badgeColor='#e3f0ff';
                                        $textColor='#4F8CFF';
                                    }
                                @endphp
                                <span class="badge" style="background:{{ $badgeColor }}; color:{{ $textColor }};">
                                    {{ $reg->status_label }}
                                </span>
                            </td>
                            <td class="text-start">{{ $reg->input_by }}</td>
                            <td>
                                <div class="d-flex gap-2 justify-content-center">
                                    @if(\App\Helpers\RoleHelper::canViewDetailRegister())
                                        <a href="{{ route('register.show', \Illuminate\Support\Facades\Crypt::encryptString($reg->id_reg)) }}" class="btn-action-register view" title="Lihat"><i class="bi bi-eye"></i></a>
                                    @endif
                                    @if(\App\Helpers\RoleHelper::canUploadRegister())
                                        <button type="button" class="btn-action-register upload" title="Aksi Upload" data-bs-toggle="modal" data-bs-target="#modalAksiUpload"
                                            data-id="{{ \Illuminate\Support\Facades\Crypt::encryptString($reg->id_reg) }}" 
                                            data-status="{{ $reg->status }}"
                                            data-nominal-disetujui="{{ $reg->nominal_disetujui ?? '' }}"
                                            data-tanggal-realisasi="{{ $reg->tanggal_realisasi ?? '' }}">
                                            <i class="bi bi-upload"></i>
                                        </button>
                                    @endif
                                    @if(\App\Helpers\RoleHelper::canDelete('register'))
                                        <form action="{{ route('register.destroy', \Illuminate\Support\Facades\Crypt::encryptString($reg->id_reg)) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-register delete" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="text-center text-muted py-4">
                                <i class="bi bi-folder-x" style="font-size:2.5rem; color: #1dd1a1;"></i><br>
                                <h5 class="mt-2">Data tidak ditemukan</h5>
                                <p class="text-muted">Belum ada data registrasi nasabah yang tersimpan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    @if($registers->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4 px-3">
        <div class="pagination-info">
            <span class="text-muted" style="font-size: 14px;">Showing {{ $registers->firstItem() ?? 0 }} to {{ $registers->lastItem() ?? 0 }} of {{ $registers->total() }} entries</span>
        </div>
        <div class="pagination-container">
            {{ $registers->appends(request()->query())->onEachSide(1)->links('vendor.pagination.custom') }}
        </div>
    </div>
    @endif

    <!-- Modal Pilih Entitas -->
    <div class="modal fade" id="modalPilihEntitas" tabindex="-1" aria-labelledby="modalPilihEntitasLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%);">
                    <h5 class="modal-title text-white d-flex align-items-center gap-2" id="modalPilihEntitasLabel">
                        <i class="bi bi-building me-2"></i>Pilih Jenis Entitas
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-4 text-muted">Pilih jenis entitas untuk data yang akan ditambahkan:</p>
                    <div class="row g-3">
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-primary w-100 py-3" onclick="pilihEntitas('perorangan')" style="border-radius: 12px;">
                                <i class="bi bi-person-fill d-block mb-2" style="font-size: 2rem;"></i>
                                <strong>Perorangan</strong>
                                <small class="d-block text-muted mt-1">Individu</small>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-outline-success w-100 py-3" onclick="pilihEntitas('badan_usaha')" style="border-radius: 12px;">
                                <i class="bi bi-building d-block mb-2" style="font-size: 2rem;"></i>
                                <strong>Badan Usaha</strong>
                                <small class="d-block text-muted mt-1">Perusahaan</small>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Data -->
    @include('register._modal')

    <!-- Modal Aksi Upload Register (Realisasi) -->
    @include('register._modal_realisasi')
</div>
@endsection 
@push('scripts')
<!-- JavaScript telah dipindahkan ke public/assets/js/register-index.js -->
<script src="{{ asset('assets/js/register-index.js') }}"></script>
<script src="{{ asset('assets/js/register-modal.js') }}"></script>
@if (session('success'))
<script>
    // Auto hide success notification setelah 3 detik
    initSuccessNotification();
</script>
@endif
<script>
  // Biar saat tombol upload di-click, action, nama & nominal pengajuan di-set pada modal
  const modalAksiUpload = document.getElementById('modalAksiUpload');
  modalAksiUpload.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var id = button.getAttribute('data-id');
    var status = button.getAttribute('data-status');
    var nominalDisetujui = button.getAttribute('data-nominal-disetujui');
    var tanggalRealisasi = button.getAttribute('data-tanggal-realisasi');
    var nama = button.closest('tr').querySelector('td:nth-child(4) .nama-link').textContent;
    var nominalPengajuan = button.closest('tr').querySelector('td:nth-child(6)').textContent;
    var form = document.getElementById('formAksiUploadRegister');
    form.action = '/register/' + id + '/aksi-upload';
    form.nama.value = nama.trim();
    // Auto-resize textarea untuk nama nasabah
    if (form.nama) {
      form.nama.style.height = 'auto';
      form.nama.style.height = (form.nama.scrollHeight) + 'px';
    }
    form.nominal_pengajuan.value = nominalPengajuan;
    
    // Set status sesuai data yang sudah tersimpan atau dari data-status
    if (status && form.status) {
      for (let i=0; i<form.status.options.length; i++) {
        if (form.status.options[i].value.toLowerCase() === status.toLowerCase()) {
          form.status.selectedIndex = i;
          break;
        }
      }
    }
    
    // Set nominal disetujui jika sudah ada, format ke Rupiah
    if (nominalDisetujui && nominalDisetujui !== '') {
      let nominal = parseInt(nominalDisetujui);
      if (!isNaN(nominal)) {
        form.nominal_disetujui.value = 'Rp ' + nominal.toLocaleString('id-ID');
      } else {
        form.nominal_disetujui.value = '';
      }
    } else {
      form.nominal_disetujui.value = '';
    }
    
    // Set tanggal realisasi jika sudah ada, jika tidak set default hari ini
    if (tanggalRealisasi && tanggalRealisasi !== '') {
      form.tanggal_realisasi.value = tanggalRealisasi;
    } else {
      let today = new Date();
      let yyyy = today.getFullYear();
      let mm = String(today.getMonth()+1).padStart(2,'0');
      let dd = String(today.getDate()).padStart(2,'0');
      let todayStr = `${yyyy}-${mm}-${dd}`;
      form.tanggal_realisasi.value = todayStr;
    }
  });

  // Format input nominal disetujui otomatis Rupiah
  document.addEventListener('input', function(e){
    if(e.target.classList.contains('format-rupiah')){
      let value = e.target.value.replace(/[^\d]/g, '');
      if(value) {
        value = parseInt(value).toLocaleString('id-ID');
        e.target.value = 'Rp ' + value;
      } else {
        e.target.value = '';
      }
    }
  });
  // Supaya sebelum submit, value rupiah diformat ke angka
  document.getElementById('formAksiUploadRegister').addEventListener('submit', function(e){
    let inp = this.nominal_disetujui;
    inp.value = inp.value.replace(/[^\d]/g,'');
  });
</script>
@endpush