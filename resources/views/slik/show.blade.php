@extends('layout.master')
@section('main-content')

<div class="container mt-4">
    <!-- Header Card -->
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); border-radius: 18px;">
        <div>
            <h2 class="fw-bold mb-1 text-white">Detail Data SLIK</h2>
            <div class="text-white-50">Informasi lengkap pemeriksaan SLIK nasabah</div>
        </div>
        <span style="font-size:3rem; color:#fff; opacity:0.25;"><i class="bi bi-shield-check"></i></span>
    </div>

    <!-- Tombol Aksi Utama -->
    <div class="mb-3 d-flex gap-2 justify-content-end">
        @if(request('register_id'))
            <a href="{{ route('slik.index', ['register_id' => request('register_id')]) }}" class="btn btn-dark">Kembali ke Data</a>
            @if(\App\Helpers\RoleHelper::canEdit('slik'))
                @php $encS = strtr(\Illuminate\Support\Facades\Crypt::encryptString($slik->id_slik), ['+' => '-', '/' => '_', '=' => '.']); @endphp
                <a href="{{ route('slik.edit.simple') }}?id={{ $encS }}&register_id={{ request('register_id') }}" class="btn btn-warning">Ubah Data</a>
            @endif
            @if(\App\Helpers\RoleHelper::canDelete('slik'))
                <form action="{{ route('slik.destroy', $slik->id_slik) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="register_id" value="{{ request('register_id') }}">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            @endif
        @else
            <a href="{{ route('slik.index') }}" class="btn btn-dark">Kembali ke Data</a>
            @if(\App\Helpers\RoleHelper::canEdit('slik'))
                @php $encS = strtr(\Illuminate\Support\Facades\Crypt::encryptString($slik->id_slik), ['+' => '-', '/' => '_', '=' => '.']); @endphp
                <a href="{{ route('slik.edit.simple') }}?id={{ $encS }}" class="btn btn-warning">Ubah Data</a>
            @endif
            @if(\App\Helpers\RoleHelper::canDelete('slik'))
                <form action="{{ route('slik.destroy', $slik->id_slik) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            @endif
        @endif
    </div>

    <!-- Info Nasabah (on top) -->
    @if($slik->register)
    <div class="card mb-4 section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold"><i class="bi bi-person-badge me-2"></i>Info Nasabah</h5>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <div class="me-3 avatar-circle">{{ strtoupper(substr($slik->register->nama ?? 'N',0,1)) }}</div>
                <div>
                    <h3 class="fw-bold mb-1" style="letter-spacing:1px; color:#162447;">{{ $slik->register->nama ?? '-' }}</h3>
                    <span class="badge d-inline-flex align-items-center" style="background:#b2f2ff; color:#4F8CFF; font-size:1rem;" data-bs-toggle="tooltip" title="ID User yang menginput">
                        <i class="bi bi-person-badge me-1"></i> {{ $slik->status }}
                    </span>
                    <div class="text-muted small">Diinput oleh: {{ $slik->register->input_by }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-item"><div class="info-label">Nomor Registrasi</div><div class="info-value">{{ $slik->register->nomor ?? '-' }}</div></div>
                @php $isBU = ($slik->register->jenis_entitas ?? '') === 'badan_usaha'; @endphp
                <div class="info-item">
                    <div class="info-label">{{ $isBU ? 'Nomor Legalitas Usaha' : 'No Identitas' }}</div>
                    <div class="info-value">{{ $isBU ? ($slik->register->nomor_legalitas_usaha ?? '-') : ($slik->register->no_identitas ?? '-') }}</div>
                </div>
                <div class="info-item"><div class="info-label">Nominal Pengajuan</div><div class="info-value">{{ isset($slik->register->nominal_pengajuan) ? 'Rp '.number_format($slik->register->nominal_pengajuan,0,',','.') : '-' }}</div></div>
                <div class="info-item"><div class="info-label">Tanggal Pengajuan</div><div class="info-value">{{ $slik->register->tgl_pengajuan ?? '-' }}</div></div>
                <div class="info-item"><div class="info-label">Status Register</div><div class="info-value">{{ $slik->register->status ?? '-' }}</div></div>
            </div>
        </div>
    </div>
    @endif

    <!-- Card Detail SLIK -->
    <div class="card mb-4 section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-info-circle me-2"></i>Informasi SLIK</h5>
        </div>
        <div class="card-body">
            <div class="info-row">
                <div class="info-item"><div class="info-label">Nomor SLIK</div><div class="info-value">{{ $slik->nomor }}</div></div>
                <div class="info-item"><div class="info-label">Nomor Register</div><div class="info-value">{{ $slik->register ? $slik->register->nomor : '-' }}</div></div>
                <div class="info-item"><div class="info-label">Tanggal</div><div class="info-value">{{ \Carbon\Carbon::parse($slik->tgl)->format('d/m/Y') }}</div></div>
                <div class="info-item"><div class="info-label">Nama Nasabah</div><div class="info-value">{{ $slik->nama }}</div></div>
                <div class="info-item"><div class="info-label">No Identitas</div><div class="info-value">{{ $slik->no_identitas }}</div></div>
                <div class="info-item"><div class="info-label">Keterkaitan</div><div class="info-value">{{ $slik->keterkaitan }}</div></div>
                <div class="info-item"><div class="info-label">Status</div>
                    <div class="info-value">
                        @if($slik->status == 'proses' || $slik->status == 'Dalam Proses')
                            <span class="badge status-badge" style="background:#e3f0ff; color:#4F8CFF;">Dalam Proses</span>
                        @elseif($slik->status == 'selesai' || $slik->status == 'Selesai')
                            <span class="badge status-badge" style="background:#b2f2e5; color:#1dd1a1;">Selesai</span>
                        @elseif($slik->status == 'ditolak' || $slik->status == 'Ditolak')
                            <span class="badge status-badge" style="background:#e6e6fa; color:#8e44ad;">Ditolak</span>
                        @else
                            <span class="badge bg-secondary status-badge">{{ $slik->status }}</span>
                        @endif
                    </div>
                </div>
                <div class="info-item"><div class="info-label">Dibuat</div><div class="info-value">{{ \Carbon\Carbon::parse($slik->created_at)->format('d/m/Y H:i') }}</div></div>
            </div>
        </div>
    </div>

    <!-- Card Dokumen Hasil -->
    <div class="card mb-4 section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-file-earmark-text me-2"></i>Dokumen Hasil SLIK</h5>
        </div>
        <div class="card-body">
            @php
                // Parse hasil sebagai JSON array
                $hasilFiles = [];
                if ($slik->hasil) {
                    $parsed = is_string($slik->hasil) ? json_decode($slik->hasil, true) : $slik->hasil;
                    if (is_array($parsed)) {
                        $hasilFiles = $parsed;
                    } elseif (is_string($slik->hasil)) {
                        // Backward compatibility: file lama di index 0
                        $hasilFiles[0] = $slik->hasil;
                    }
                }
                // pastikan slot indeks 1 terisi bila ada hasil2 legacy
                if ($slik->hasil2 && !isset($hasilFiles[1])) {
                    $hasilFiles[1] = $slik->hasil2;
                }

                $file1      = $hasilFiles[0] ?? null;
                $file2      = $hasilFiles[1] ?? null;
                $file1Path  = is_array($file1) && isset($file1['path']) ? $file1['path'] : (is_string($file1) ? $file1 : null);
                $file2Path  = is_array($file2) && isset($file2['path']) ? $file2['path'] : (is_string($file2) ? $file2 : null);
                $fileName1  = is_array($file1) && isset($file1['original_name']) ? $file1['original_name'] : ($file1Path ? basename($file1Path) : null);
                $fileName2  = is_array($file2) && isset($file2['original_name']) ? $file2['original_name'] : ($file2Path ? basename($file2Path) : null);
                $ext1       = $file1Path ? strtolower(pathinfo($file1Path, PATHINFO_EXTENSION)) : null;
                $ext2       = $file2Path ? strtolower(pathinfo($file2Path, PATHINFO_EXTENSION)) : null;
                $url        = $file1Path ? route('file.encrypted', \App\Helpers\UrlEncryptionHelper::encryptFileUrl($slik->id_slik, 0, 'slik', $slik->nama)) : null;
                $url2       = $file2Path ? route('file.encrypted', \App\Helpers\UrlEncryptionHelper::encryptFileUrl($slik->id_slik, 1, 'slik', $slik->nama)) : null;
            @endphp
            @if($file1Path || $file2Path)
                @if($file1Path)
                    <div class="file-card p-3 mb-3">
                        <div class="file-title text-truncate mb-3" title="{{ $fileName1 }}">
                            <i class="bi bi-file-earmark me-1"></i>{{ $fileName1 }}
                        </div>
                        @if(in_array($ext1, ['jpg','jpeg','png']))
                            <div class="mb-3">
                                <img src="{{ $url }}" alt="Preview" style="max-width:180px;max-height:140px;object-fit:contain;border-radius:10px;border:1px solid #eef2f7;">
                            </div>
                        @endif
                        <div class="d-flex flex-wrap gap-3">
                            @if(in_array($ext1, ['jpg','jpeg','png']))
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#previewHasilModal"><i class="bi bi-eye me-1"></i>Lihat</button>
                            @else
                                <a href="{{ $url }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye me-1"></i>Lihat</a>
                            @endif
                            <a href="{{ $url }}?download=1" download class="btn btn-success btn-sm"><i class="bi bi-download me-1"></i>Download</a>
                        </div>
                    </div>
                @endif

                @if($file2Path)
                    <div class="file-card p-3">
                        <div class="file-title text-truncate mb-3" title="{{ $fileName2 }}">
                            <i class="bi bi-file-earmark me-1"></i>{{ $fileName2 }}
                        </div>
                        @if(in_array($ext2, ['jpg','jpeg','png']))
                            <div class="mb-3">
                                <img src="{{ $url2 }}" alt="Preview" style="max-width:180px;max-height:140px;object-fit:contain;border-radius:10px;border:1px solid #eef2f7;">
                            </div>
                        @endif
                        <div class="d-flex flex-wrap gap-3">
                            @if(in_array($ext2, ['jpg','jpeg','png']))
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#previewHasil2Modal"><i class="bi bi-eye me-1"></i>Lihat</button>
                            @else
                                <a href="{{ $url2 }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye me-1"></i>Lihat</a>
                            @endif
                            <a href="{{ $url2 }}?download=1" download class="btn btn-success btn-sm"><i class="bi bi-download me-1"></i>Download</a>
                        </div>
                    </div>
                @endif
            @else
                <div class="empty-drop">
                    <div class="ico mb-2"><i class="bi bi-file-earmark-x"></i></div>
                    <div class="fw-semibold mb-1">Belum ada dokumen hasil</div>
                    <div class="text-muted small mb-3">Dokumen hasil pemeriksaan SLIK belum tersedia</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Hasil 2 Section -->
    <div class="card mb-4 section-card">
        <div class="card-header section-head">
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-file-earmark-text me-2"></i>Dokumen Hasil SLIK 2</h5>
        </div>
        <div class="card-body">
            <div class="empty-drop">
                <div class="ico mb-2"><i class="bi bi-file-earmark-x"></i></div>
                <div class="fw-semibold mb-1">Belum ada dokumen hasil 2</div>
                <div class="text-muted small mb-3">Dokumen hasil pemeriksaan SLIK 2 belum tersedia</div>
            </div>
        </div>
    </div>

    @if($file1Path && in_array(strtolower(pathinfo($file1Path, PATHINFO_EXTENSION)), ['jpg','jpeg','png']))
    <!-- Modal Preview Gambar Hasil 1 -->
    <div class="modal fade" id="previewHasilModal" tabindex="-1" aria-labelledby="previewHasilModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="previewHasilModalLabel">Preview Dokumen Hasil 1</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <img src="{{ $url }}" alt="Dokumen Hasil 1" style="max-width:100%;max-height:70vh;object-fit:contain;">
          </div>
        </div>
      </div>
    </div>
    @endif

    @if($file2Path && in_array(strtolower(pathinfo($file2Path, PATHINFO_EXTENSION)), ['jpg','jpeg','png']))
    <!-- Modal Preview Gambar Hasil 2 -->
    <div class="modal fade" id="previewHasil2Modal" tabindex="-1" aria-labelledby="previewHasil2ModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="previewHasil2ModalLabel">Preview Dokumen Hasil 2</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <img src="{{ $url2 }}" alt="Dokumen Hasil 2" style="max-width:100%;max-height:70vh;object-fit:contain;">
          </div>
        </div>
      </div>
    </div>
    @endif

    
</div>

@endsection

@section('css')
<link rel="stylesheet" href="{{ asset('assets/css/slik_style.css') }}">
@endsection

@push('scripts')
<script src="{{ asset('assets/js/slik-show.js') }}"></script>
@endpush