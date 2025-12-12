<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Register;
use App\Models\Slik;
use App\Models\Bank;
use App\Models\Komite;
use App\Models\User;
use App\Models\Data;

class DashboardController extends Controller
{
    public function index()
    {
        // Tahun yang dipilih (default: tahun berjalan)
        $tahun = request()->get('tahun');
        if (!$tahun || !is_numeric($tahun)) {
            $tahun = now()->year;
        } else {
            $tahun = (int) $tahun;
        }
        // Opsi tahun (rentang 5 tahun ke belakang sampai 1 tahun ke depan)
        $startYear = now()->year - 5;
        $endYear = now()->year + 1;
        $tahunOptions = range($startYear, $endYear);

        // Statistik pengajuan (dibatasi per tahun menggunakan tgl_pengajuan)
        $total = Register::whereYear('tgl_pengajuan', $tahun)->count();
        // Opsi B:
        // - Pengajuan Masuk: hanya status 1 (atau '1')
        // - Pengajuan Dalam Proses: status 2 / 'Menunggu Komite' / 'Dalam Proses' (termasuk variasi huruf kecil)
        $proses = Register::whereYear('tgl_pengajuan', $tahun)
            ->whereIn('status', ['1', 1])
            ->count();
        $menunggu_komite = Register::whereYear('tgl_pengajuan', $tahun)
            ->whereIn('status', ['2', 2, 'Menunggu Komite', 'menunggu komite', 'Dalam Proses', 'dalam proses', 'proses'])
            ->count();
        $disetujui = Register::whereYear('tgl_pengajuan', $tahun)
            ->whereIn('status', ['3', 3, 'Disetujui', 'disetujui'])
            ->count();
        $ditolak = Register::whereYear('tgl_pengajuan', $tahun)
            ->whereIn('status', ['4', 4, 'Ditolak', 'ditolak'])
            ->count();

        // Pengajuan baru hari ini (hanya relevan untuk tahun berjalan)
        $baru_hari_ini = ($tahun === (int) now()->year)
            ? Register::whereDate('tgl_pengajuan', now()->toDateString())->count()
            : 0;

        // Data SLIK
        $jumlah_slik = Slik::count();
        // Data Bank
        $jumlah_bank = Bank::count();
        // Data Komite
        $jumlah_komite = Komite::count();
        // Data Nasabah
        $jumlah_data = Data::count();
        // User aktif (online = 1)
        $user_aktif = User::where('online', 1)->count();

        // Data untuk grafik per hari (7 hari terakhir) - biarkan mengikuti tanggal aktual
        $grafik = Register::selectRaw('DATE(tgl_pengajuan) as tanggal, COUNT(*) as total')
            ->where('tgl_pengajuan', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
        // Buat array lengkap untuk 7 hari terakhir dengan data kosong jika tidak ada
        $nama_hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $grafik_harian = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $tanggal = now()->subDays($i)->format('Y-m-d');
            $hari = now()->subDays($i)->dayOfWeek;
            $nama_hari_indo = $nama_hari[$hari];
            $angka_hari = 6 - $i; // Menghasilkan angka 0-6
            $label_hari = $angka_hari == 0 ? '0' : $nama_hari_indo;
            $data_hari = $grafik->where('tanggal', $tanggal)->first();
            $totalH = $data_hari ? $data_hari->total : 0;
            $grafik_harian[] = [
                'hari' => $label_hari,
                'nama_hari' => $nama_hari_indo,
                'tanggal' => $tanggal,
                'total' => $totalH,
                'index' => $angka_hari
            ];
        }

        // Grafik Realisasi Kredit (per bulan, per jenis pengajuan, nominal_disetujui) dengan MAPPING kode ke label
        // OTOMATIS mengambil seluruh jenis pengajuan dari model (key=>label)
        $jenisKodeToLabel = \App\Models\Register::jenisPengajuanList();
        $bulanLabels = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $realisasiPerJenis = [];

        // Siapkan array kosong per label untuk grafik
        foreach ($jenisKodeToLabel as $key => $label) {
            foreach (range(1, 12) as $m) {
                $realisasiPerJenis[$label][$m] = 0;
            }
        }
        $totalPerBulan = array_fill(1, 12, 0);

        // Query & mapping ke label ENUM terbaru
        $dataRealisasi = \DB::table('registers')
            ->selectRaw('MONTH(tanggal_realisasi) as bulan, jns_pengajuan, SUM(nominal_disetujui) as total')
            ->whereYear('tanggal_realisasi', $tahun)
            ->whereNotNull('nominal_disetujui')
            ->whereNotNull('tanggal_realisasi')
            ->whereNotIn('status', ['4', 4, 'Ditolak','ditolak'])
            ->whereIn('jns_pengajuan', array_keys($jenisKodeToLabel))
            ->groupByRaw('MONTH(tanggal_realisasi), jns_pengajuan')
            ->get();
        foreach ($dataRealisasi as $row) {
            $label = $jenisKodeToLabel[$row->jns_pengajuan] ?? null;
            if (!$label) continue;
            $bulan = intval($row->bulan);
            $realisasiPerJenis[$label][$bulan] = (int)$row->total;
            $totalPerBulan[$bulan] += (int)$row->total;
        }

        return view('dashboard.index', compact(
            'total', 'proses', 'menunggu_komite', 'disetujui', 'ditolak',
            'jumlah_slik', 'jumlah_bank', 'jumlah_komite', 'jumlah_data', 'user_aktif', 'grafik_harian',
            'realisasiPerJenis', 'totalPerBulan', 'bulanLabels', 'jenisKodeToLabel', 'baru_hari_ini',
            'tahun', 'tahunOptions'
        ));
    }
}