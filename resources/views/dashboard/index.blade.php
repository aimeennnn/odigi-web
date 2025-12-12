@extends('layout.master')

@section('main-content')
<div class="container-fluid mt-4">
    <!-- Header Card Gradient Hijau-Biru dengan Profil -->
    <div class="p-4 mb-4 d-flex align-items-center justify-content-between" 
        style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%); border-radius: 18px; position: relative; min-height: 120px;">
        
        <!-- Bagian Kiri: Logo Huruf + Teks -->
        <div class="d-flex align-items-center">
            <div class="d-flex align-items-center justify-content-center me-3" 
                 style="width:70px; height:70px; border-radius: 50%; border: 2px solid rgba(255,255,255,0.5); box-shadow:0 3px 8px rgba(0,0,0,0.1); background: rgba(255,255,255,0.2);">
                <span style="color: #fff; font-size: 2.5rem; font-weight: 600;">
                    {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->username, 0, 1)) }}
                </span>
            </div>
            <div>
            <h2 class="fw-bold mb-1 text-white">Selamat Datang Kembali</h2>
            <div class="text-white-50" style="font-size: 1.1rem;">{{ auth()->user()->nama ?? 'USER' }}</div>
        </div>
        </div>

        <!-- Icon Dekoratif -->
        <span style="position:absolute; top:16px; right:32px;">
            <i class="bi bi-person-circle" style="font-size:4rem; color:#fff; opacity:0.25;"></i>
        </span>
    </div>

    <!-- Filter Tahun - Dipindahkan ke bawah header Selamat Datang -->
    <div class="d-flex justify-content-end align-items-center mb-3">
        <form method="GET" action="" class="d-flex align-items-center gap-2">
            <label class="me-2 fw-semibold">Tahun</label>
            <select name="tahun" class="form-select" style="width:auto;">
                @foreach($tahunOptions as $th)
                    <option value="{{ $th }}" {{ (int)$tahun === (int)$th ? 'selected' : '' }}>{{ $th }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Terapkan</button>
        </form>
    </div>

    <!-- <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="background: #eaf2ff;">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <img src="https://randomuser.me/api/portraits/men/1.jpg" class="rounded-circle me-3" width="60" height="60" alt="User">
                        <div>
                            <h5 class="mb-0">Selamat Datang Kembali</h5>
                            <h6 class="mb-0">ADMIN</h6>
                        </div>
                    </div>
                    <img src="https://assets10.lottiefiles.com/packages/lf20_1pxqjqps.json" alt="Welcome" style="height:90px;">
                </div>
            </div>
        </div>
    </div> -->
    <div class="row g-3 mb-4">
        <!-- Total Pengajuan -->
        <div class="col-md-4">
            <div class="card text-white p-3 shadow-sm" style="border-radius:16px; background-color:#50c878;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0 text-white">{{ $total }}</h3>
                        <small>Total Pengajuan</small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width:45px; height:45px;">
                        <i class="bi bi-collection fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pengajuan Baru Hari Ini -->
        <div class="col-md-4">
            <div class="card text-white p-3 shadow-sm" style="border-radius:16px; background-color:#007BFF;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0 text-white">{{ $baru_hari_ini }}</h3>
                        <small>Pengajuan Masuk</small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width:45px; height:45px;">
                        <i class="bi bi-calendar-plus fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Permohonan Pengajuan Dalam Proses (status 2) -->
        <div class="col-md-4">
            <div class="card text-white p-3 shadow-sm" style="border-radius:16px; background-color:#17a2b8;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0 text-white">{{ $menunggu_komite }}</h3>
                        <small>Permohonan Pengajuan Dalam Proses</small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width:45px; height:45px;">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Permohonan Pengajuan Disetujui (status 3) -->
        <div class="col-md-4">
            <div class="card text-white p-3 shadow-sm" style="border-radius:16px; background-color:#28a745;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0 text-white">{{ $disetujui }}</h3>
                        <small>Permohonan Pengajuan Disetujui</small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width:45px; height:45px;">
                        <i class="bi bi-check-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Permohonan Pengajuan Ditolak (status 4) -->
        <div class="col-md-4">
            <div class="card text-white p-3 shadow-sm" style="border-radius:16px; background-color:#dc3545;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-0 text-white">{{ $ditolak }}</h3>
                        <small>Permohonan Pengajuan Ditolak</small>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-white bg-opacity-25" style="width:45px; height:45px;">
                        <i class="bi bi-x-circle fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-4">Grafik Rekapitulasi Realisasi (Per Bulan)</h5>
            <canvas id="grafikRealisasiKredit" height="110"></canvas>
            <div style="font-size:12px;color:#888;">Sumber: Nominal Disetujui, berdasarkan jenis pengajuan</div>
        </div>
    </div>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <h5 class="mb-3">Rekapitulasi Realisasi (Per Bulan)</h5>
            <div class="table-responsive" style="overflow-x:auto;">
                <table class="table table-bordered align-middle">
                    <thead style="background:#f7f9fb;">
                        <tr>
                            <th style="white-space:nowrap;">No</th>
                            <th style="white-space:nowrap;">Keterangan</th>
                            @foreach($bulanLabels as $bulan)
                                <th style="white-space:nowrap;">{{ $bulan }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach($realisasiPerJenis as $jenis => $byMonth)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td style="white-space:nowrap; text-transform:uppercase;">{{ 'KREDIT ' . $jenis }}</td>
                                @foreach(range(1,12) as $m)
                                    @php $val = $byMonth[$m] ?? 0; @endphp
                                    <td style="white-space:nowrap;">Rp. {{ number_format($val,0,',','.') }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        <tr style="font-weight:700; background:#fbfcfe;">
                            <td colspan="2">TOTAL</td>
                            @foreach(range(1,12) as $m)
                                @php $tot = $totalPerBulan[$m] ?? 0; @endphp
                                <td style="white-space:nowrap;">Rp. {{ number_format($tot,0,',','.') }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const bulanLabels = {!! json_encode($bulanLabels) !!};
    const realisasiData = {!! json_encode($realisasiPerJenis) !!};
    const totalPerBulan = {!! json_encode(array_values($totalPerBulan)) !!};
    const legendLabels = Object.keys(realisasiData);
    const warnaJenis = {
        "Umum": "rgba(41,200,207,0.19)",
        "KTA": "rgba(255,193,7,0.15)",
        "Lain-lain": "rgba(252,79,156,0.16)"
    };
    const borderWarna = {
        "Umum": "#29c8cf",
        "KTA": "#ffc107",
        "Lain-lain": "#fc4f9c"
    };
    const datasets = legendLabels.map(jenis => ({
        label: jenis,
        data: Object.values(realisasiData[jenis]),
        borderColor: borderWarna[jenis] || '#888',
        backgroundColor: warnaJenis[jenis] || '#eee',
        fill: true,
        tension: 0.42,
        pointRadius: 4.5,
        pointBackgroundColor: borderWarna[jenis] || '#888',
        borderWidth: 2.2,
        order: 1
    }));
    datasets.push({
        label: "Total Keseluruhan",
        data: totalPerBulan,
        borderColor: "#222",
        backgroundColor: "rgba(0,0,0,0.10)",
        fill: true,
        borderWidth: 3,
        pointRadius: 6,
        pointBackgroundColor: "#222",
        order: 0,
        tension: 0.33
    });
    new Chart(document.getElementById('grafikRealisasiKredit'), {
        type: 'line',
        data: {
            labels: bulanLabels,
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { usePointStyle: true, font: { size: 15 } }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            let value = context.parsed.y || 0;
                            return (label ? label + ': ' : '') + 'Rp ' + (value).toLocaleString('id-ID');
                        }
                    }
                }
            },
            interaction: { mode: 'nearest', axis: 'x', intersect: false },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => 'Rp ' + value.toLocaleString('id-ID'),
                        font: { size: 13 }
                    },
                    grid: { color: '#e4e6ef' }
                },
                x: {
                    ticks: { font: { size: 13 } },
                    grid: { color: '#f3f3f3' }
                }
            }
        }
    });
</script>
@endpush