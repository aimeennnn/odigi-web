import json
import pandas as pd
import re
import sys
import os
from datetime import datetime

# ==============================================================================
# 1. FUNGSI BANTUAN
# ==============================================================================
def bersihkan_teks(teks):
    if not isinstance(teks, str): return teks
    hasil = ' '.join(teks.split())
    hasil = hasil.replace(" ,", ",")
    return hasil

def ke_angka(nilai):
    try: return float(nilai)
    except (ValueError, TypeError): return 0

def ambil_tahun(tanggal_str):
    if isinstance(tanggal_str, str) and len(tanggal_str) >= 4:
        if tanggal_str[:4].isdigit(): return int(tanggal_str[:4])
    return 0

def normalisasi_super_agresif(teks):
    if not isinstance(teks, str): return ""
    bersih = teks.upper()
    pola = r'\bPT\b|\bCV\b|\bUD\b|\bTN\b|\bNY\b|\bSDR\b|\bMRS\b|\bMR\b'
    bersih = re.sub(pola, '', bersih)
    bersih = re.sub(r'[^A-Z0-9\s]', ' ', bersih)
    bersih = ' '.join(bersih.split())
    return bersih

# ==============================================================================
# 2. KONFIGURASI FILE
# ==============================================================================
if len(sys.argv) < 3:
    print("Usage: python slik.py [input_path] [output_path]")
    sys.exit(1)

nama_file_json = sys.argv[1]   # Input dari Laravel
nama_file_excel = sys.argv[2]  # Output ke Laravel

try:
    # [FIX] Ganti Emoji 📂 dengan [INFO]
    print("1. [INFO] Membaca file JSON...")
    
    if not os.path.exists(nama_file_json):
        raise Exception(f"File input tidak ditemukan: {nama_file_json}")

    with open(nama_file_json, 'r', encoding='latin-1') as f:
        content = f.read().strip()
        if not content:
            raise Exception("File TXT/JSON kosong (0 bytes).")
        f.seek(0)
        data_json = json.load(f)

    # AUTO-DETECT
    if 'individual' in data_json: data_utama = data_json['individual']
    elif 'perusahaan' in data_json: data_utama = data_json['perusahaan']
    else: data_utama = {}

    data_fasilitas = data_utama.get('fasilitas', {})
    list_kredit_raw = data_fasilitas.get('kreditPembiayan', [])
    if not list_kredit_raw: list_kredit_raw = data_fasilitas.get('kreditPembiayaan', [])

    list_profil = data_utama.get('dataPokokDebitur', [])
    nama_debitur_raw = list_profil[0].get('namaDebitur', '') if list_profil else ""
    nama_debitur_norm = normalisasi_super_agresif(nama_debitur_raw)

    print(f"   Debitur: {nama_debitur_raw}")

    # ==============================================================================
    # 3. PROSES DATA
    # ==============================================================================
    list_kredit_bersih = [] 
    list_agunan_all = []    

    for kredit in list_kredit_raw:
        info_bank = kredit.get('ljkKet', 'Tidak Diketahui')
        info_akad = kredit.get('noAkadAwal', '-')
        
        # AGUNAN
        agunan_list = kredit.get('agunan', [])
        if isinstance(agunan_list, list) and len(agunan_list) > 0:
            for item in agunan_list:
                row = item.copy()
                row['(REF) Bank'] = info_bank
                row['(REF) No Akad'] = info_akad
                row['Status Agunan'] = 'Ada'
                list_agunan_all.append(row)
        else:
            list_agunan_all.append({'(REF) Bank': info_bank, '(REF) No Akad': info_akad, 'Status Agunan': '[] (Kosong)'})

        # KREDIT
        kredit_clean = kredit.copy()
        kredit_clean.pop('agunan', None)
        kredit_clean.pop('penjamin', None)
        list_kredit_bersih.append(kredit_clean)

    # DF & Cleaning
    df_profil = pd.DataFrame(list_profil)
    df_kredit = pd.DataFrame(list_kredit_bersih)
    df_agunan = pd.DataFrame(list_agunan_all)

    for df in [df_profil, df_kredit, df_agunan]:
        if not df.empty:
            for col in df.select_dtypes(include=['object']).columns:
                df[col] = df[col].apply(bersihkan_teks)

    # ==============================================================================
    # 4. ANALISIS DASHBOARD KOMPLIT
    # ==============================================================================
    # [FIX] Ganti Emoji 📊 dengan [INFO]
    print("   [INFO] Membuat Analisis Dashboard Directors Cut...")
    
    # Init DataFrame
    df_keuangan = pd.DataFrame()
    df_risk = pd.DataFrame()
    df_behavior = pd.DataFrame()
    df_agunan_summary = pd.DataFrame()
    df_status = pd.DataFrame()
    df_bank_detail = pd.DataFrame()
    df_histori_detail = pd.DataFrame()
    df_tenor = pd.DataFrame()
    
    # Standard Breakdowns
    df_tujuan = pd.DataFrame()
    df_sifat = pd.DataFrame()
    df_lokasi = pd.DataFrame()
    df_program = pd.DataFrame()

    total_nilai_agunan = 0 

    # --- ANALISIS AGUNAN ---
    if not df_agunan.empty and 'nilaiAgunanMenurutLJK' in df_agunan.columns:
        df_agunan['nilaiAgunanMenurutLJK'] = df_agunan['nilaiAgunanMenurutLJK'].apply(ke_angka)
        total_nilai_agunan = df_agunan['nilaiAgunanMenurutLJK'].sum()
        
        list_pemilik_beda = []
        if 'namaPemilikAgunan' in df_agunan.columns:
            for pemilik in df_agunan['namaPemilikAgunan']:
                if isinstance(pemilik, str) and pemilik.strip():
                    pn = normalisasi_super_agresif(pemilik)
                    if pn and nama_debitur_norm and (pn != nama_debitur_norm): list_pemilik_beda.append(pn)
        
        pu = sorted(list(set(list_pemilik_beda)))
        jp = len(pu)
        ket_pihak_ketiga = f"{jp} Pihak ({', '.join(pu[:3])})" if jp > 0 else "Tidak Ada"

        df_agunan_summary = pd.DataFrame({
            'INDIKATOR AGUNAN': ['Total Nilai Agunan', 'Pemilik Pihak Ketiga'],
            'KETERANGAN': [total_nilai_agunan, ket_pihak_ketiga]
        })

    # --- ANALISIS KREDIT ---
    if not df_kredit.empty:
        # Konversi
        cols_num = ['plafon', 'bakiDebet', 'tunggakanPokok', 'tunggakanBunga', 'denda', 
                    'jumlahHariTunggakan', 'kualitas', 'sukuBungaImbalan', 'nilaiProyek', 'realisasiBulanBerjalan']
        for c in cols_num:
            if c in df_kredit.columns: df_kredit[c] = df_kredit[c].apply(ke_angka)

        # Hitung Utama
        total_plafon = df_kredit['plafon'].sum() if 'plafon' in df_kredit.columns else 0
        total_baki = df_kredit['bakiDebet'].sum() if 'bakiDebet' in df_kredit.columns else 0
        total_tunggakan = df_kredit[['tunggakanPokok', 'tunggakanBunga', 'denda']].sum().sum()
        total_denda = df_kredit['denda'].sum() if 'denda' in df_kredit.columns else 0
        total_realisasi = df_kredit['realisasiBulanBerjalan'].sum() if 'realisasiBulanBerjalan' in df_kredit.columns else 0
        max_dpd = df_kredit['jumlahHariTunggakan'].max() if 'jumlahHariTunggakan' in df_kredit.columns else 0
        sisa_plafon = total_plafon - total_baki
        if sisa_plafon < 0: sisa_plafon = 0

        # 1. KEUANGAN
        df_keuangan = pd.DataFrame({
            'INDIKATOR KEUANGAN': ['Total Plafon', 'Total Sisa Hutang', 'Sisa Plafon', 'Realisasi Bln Ini', 'Total Tunggakan', 'Total Denda'],
            'NILAI': [total_plafon, total_baki, sisa_plafon, total_realisasi, total_tunggakan, total_denda]
        })

        # 2. RISK (NO LTV)
        npl_desc = "0.00% (AMAN)"
        if 'kualitas' in df_kredit.columns and total_baki > 0:
            baki_macet = df_kredit[df_kredit['kualitas'] >= 3]['bakiDebet'].sum()
            npl = (baki_macet / total_baki * 100)
            npl_desc = f"{npl:.2f}% {'(BAHAYA)' if npl > 5 else '(AMAN)'}"

        df_risk = pd.DataFrame({
            'RISK METRICS': ['NPL Ratio', 'Max DPD'],
            'NILAI': [npl_desc, f"{max_dpd} Hari"]
        })

        # 3. BEHAVIOR
        utilisasi = (total_baki / total_plafon * 100) if total_plafon > 0 else 0
        kredit_baru = 0
        curr_year = pd.Timestamp.now().year
        if 'tanggalMulai' in df_kredit.columns:
            df_kredit['ThnMulai'] = df_kredit['tanggalMulai'].apply(ambil_tahun)
            kredit_baru = df_kredit[df_kredit['ThnMulai'].astype(str) >= str(curr_year - 1)].shape[0]
        
        df_behavior = pd.DataFrame({
            'BEHAVIOR': ['Utilisasi Limit', 'Aggressiveness (<1 Thn)'],
            'NILAI': [f"{utilisasi:.2f}%", f"{kredit_baru} Fasilitas"]
        })

        # 4. STATUS
        if 'kondisiKet' in df_kredit.columns:
            jml_aktif = df_kredit['kondisiKet'].str.contains('Aktif', na=False, case=False).sum()
            df_status = pd.DataFrame({
                'STATUS': ['Fasilitas Aktif', 'Fasilitas Non-Aktif'],
                'JUMLAH': [jml_aktif, len(df_kredit)-jml_aktif]
            })

        # 5. BANK BREAKDOWN
        if 'ljkKet' in df_kredit.columns and 'bakiDebet' in df_kredit.columns:
            df_bank_detail = df_kredit.groupby('ljkKet').agg({'ljkKet': 'count', 'bakiDebet': 'sum'}) \
                .rename(columns={'ljkKet': 'Jml Fasilitas', 'bakiDebet': 'Total Sisa Hutang'}).reset_index() \
                .sort_values(by='Total Sisa Hutang', ascending=False)

        # A. ANALISIS SISA TENOR
        if 'tanggalJatuhTempo' in df_kredit.columns:
            df_kredit['ThnJT_Int'] = df_kredit['tanggalJatuhTempo'].apply(ambil_tahun)
            short_term = df_kredit[(df_kredit['ThnJT_Int'] > 0) & (df_kredit['ThnJT_Int'] <= curr_year + 1)].shape[0]
            medium_term = df_kredit[(df_kredit['ThnJT_Int'] > curr_year + 1) & (df_kredit['ThnJT_Int'] <= curr_year + 5)].shape[0]
            long_term = df_kredit[df_kredit['ThnJT_Int'] > curr_year + 5].shape[0]
            df_tenor = pd.DataFrame({
                'SISA TENOR': ['Short Term (< 1 Thn)', 'Medium Term (1-5 Thn)', 'Long Term (> 5 Thn)'],
                'JUMLAH': [short_term, medium_term, long_term]
            })

        # B. HISTORI KOLEKTIBILITAS
        count_kol1 = 0
        count_kol2 = 0
        count_macet = 0
        cols_hist = [c for c in df_kredit.columns if c.startswith('tahunBulan') and c.endswith('Kol')]
        for c in cols_hist:
            vals = df_kredit[c].astype(str)
            count_kol1 += vals.isin(['1']).sum()
            count_kol2 += vals.isin(['2']).sum()
            count_macet += vals.isin(['3', '4', '5']).sum()
        df_histori_detail = pd.DataFrame({
            'HISTORI 24 BULAN': ['Kol 1 (Lancar)', 'Kol 2 (DPK)', 'Kol 3,4,5 (Macet)'],
            'TOTAL': [count_kol1, count_kol2, count_macet]
        })

        # C. KREDIT PROGRAM
        if 'kreditProgramPemerintahKet' in df_kredit.columns:
            df_program = df_kredit['kreditProgramPemerintahKet'].value_counts().reset_index()
            df_program.columns = ['Jenis Program', 'Jumlah']

        # D. Breakdowns Lainnya
        if 'jenisPenggunaanKet' in df_kredit.columns:
            df_tujuan = df_kredit['jenisPenggunaanKet'].value_counts().reset_index()
            df_tujuan.columns = ['Tujuan', 'Jumlah']
        if 'sifatKreditPembiayaanKet' in df_kredit.columns:
            df_sifat = df_kredit['sifatKreditPembiayaanKet'].value_counts().reset_index()
            df_sifat.columns = ['Sifat', 'Jumlah']
        if 'lokasiProyekKet' in df_kredit.columns:
            df_lokasi = df_kredit['lokasiProyekKet'].value_counts().head(5).reset_index()
            df_lokasi.columns = ['Lokasi', 'Jumlah']

    # ==============================================================================
    # 5. SIMPAN
    # ==============================================================================
    # [FIX] Ganti Emoji 💾 dengan [INFO]
    print("2. [INFO] Menyimpan ke Excel...")
    
    with pd.ExcelWriter(nama_file_excel, engine='openpyxl') as writer:
        row = 0
        pd.DataFrame({'DASHBOARD SLIK (DIRECTORS CUT)': []}).to_excel(writer, sheet_name='Dashboard Rangkuman', startrow=row, index=False)
        row += 2
        
        def tulis(df, w, r, sheet):
            if not df.empty:
                df.to_excel(w, sheet_name=sheet, startrow=r, index=False)
                return r + len(df) + 3
            return r

        row = tulis(df_keuangan, writer, row, 'Dashboard Rangkuman')
        row = tulis(df_risk, writer, row, 'Dashboard Rangkuman')
        row = tulis(df_behavior, writer, row, 'Dashboard Rangkuman')
        row = tulis(df_agunan_summary, writer, row, 'Dashboard Rangkuman')
        row = tulis(df_status, writer, row, 'Dashboard Rangkuman')
        row = tulis(df_tenor, writer, row, 'Dashboard Rangkuman')
        row = tulis(df_histori_detail, writer, row, 'Dashboard Rangkuman')
        row = tulis(df_program, writer, row, 'Dashboard Rangkuman')
        row = tulis(df_tujuan, writer, row, 'Dashboard Rangkuman') 
        row = tulis(df_sifat, writer, row, 'Dashboard Rangkuman')
        row = tulis(df_lokasi, writer, row, 'Dashboard Rangkuman')

        pd.DataFrame({'RINCIAN HUTANG PER BANK': []}).to_excel(writer, sheet_name='Dashboard Rangkuman', startrow=row, index=False)
        row += 1
        row = tulis(df_bank_detail, writer, row, 'Dashboard Rangkuman')

        if not df_profil.empty: 
            df_profil.to_excel(writer, sheet_name='Data Profil', index=False)
        if not df_kredit.empty: 
            cols_to_drop = [c for c in df_kredit.columns if c in ['ThnMulai', 'ThnJT', 'ThnJT_Int']]
            df_kredit.drop(columns=cols_to_drop, inplace=True, errors='ignore')
            df_kredit.to_excel(writer, sheet_name='Data Fasilitas Kredit', index=False)
        if not df_agunan.empty:
            cols = df_agunan.columns.tolist()
            cols_ref = [c for c in cols if c.startswith('(REF)')]
            cols_stat = [c for c in cols if c == 'Status Agunan']
            cols_rest = [c for c in cols if c not in cols_ref and c not in cols_stat]
            df_agunan = df_agunan[cols_ref + cols_stat + cols_rest]
            df_agunan.to_excel(writer, sheet_name='Data Agunan', index=False)

    # [FIX] Ganti Emoji ✅ dengan [SUCCESS]
    print(f"\n[SUCCESS] File tersimpan di: {nama_file_excel}")
    print(json.dumps({"status": "success", "file": nama_file_excel}))

except Exception as e:
    # [FIX] Ganti Emoji ❌ dengan [ERROR]
    print(f"[ERROR] TERJADI ERROR: {e}")
    print(json.dumps({"status": "error", "message": str(e)}))
    sys.exit(1)