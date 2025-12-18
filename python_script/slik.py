import json
import pandas as pd

# --- SETTING NAMA FILE ---
nama_file_json = r"D:\NIK_3515132410900002.txt"  # Pastikan path sesuai
nama_file_excel = 'Laporan_Fiks_NIK_3515132410900002.xlsx'

try:
    print("1. Membaca file JSON...")
    with open(nama_file_json, 'r', encoding='latin-1') as f:
        data_json = json.load(f)

    # Navigasi ke data
    data_individu = data_json.get('individual', {})
    data_fasilitas = data_individu.get('fasilitas', {})
    list_kredit_raw = data_fasilitas.get('kreditPembiayan', [])

    print(f"   Ditemukan {len(list_kredit_raw)} fasilitas kredit.")

    # --- LIST PENAMPUNG ---
    list_profil = data_individu.get('dataPokokDebitur', [])
    list_kredit_bersih = [] 
    list_agunan_all = []    

    # --- LOOPING UTAMA ---
    for kredit in list_kredit_raw:
        
        # Info Referensi
        info_bank = kredit.get('ljkKet', 'Tidak Diketahui')
        info_akad = kredit.get('noAkadAwal', '-')
        info_jenis = kredit.get('jenisKreditPembiayaanKet', '-')
        
        # 1. PROSES DATA AGUNAN
        agunan_list = kredit.get('agunan', [])
        
        if isinstance(agunan_list, list) and len(agunan_list) > 0:
            # Jika Ada Isinya
            for item_agunan in agunan_list:
                row = item_agunan.copy()
                row['(REF) Bank'] = info_bank
                row['(REF) No Akad'] = info_akad
                row['(REF) Jenis Kredit'] = info_jenis
                row['Status Agunan'] = 'Ada'
                list_agunan_all.append(row)
        else:
            # Jika Kosong
            row = {}
            row['(REF) Bank'] = info_bank
            row['(REF) No Akad'] = info_akad
            row['(REF) Jenis Kredit'] = info_jenis
            row['Status Agunan'] = '[] (Kosong/Tanpa Agunan)'
            list_agunan_all.append(row)

        # 2. PROSES DATA KREDIT (BERSIH-BERSIH)
        kredit_clean = kredit.copy()
        
        # Hapus kolom nested yang mengganggu
        kredit_clean.pop('agunan', None)
        kredit_clean.pop('penjamin', None)
        
        list_kredit_bersih.append(kredit_clean)

    # --- BUAT DATAFRAME ---
    df_profil = pd.DataFrame(list_profil)
    df_kredit = pd.DataFrame(list_kredit_bersih)
    df_agunan = pd.DataFrame(list_agunan_all)

    # --- BAGIAN HAPUS KOLOM TAHUNBULAN (HISTORY) ---
    if not df_kredit.empty:
        # Cari semua nama kolom yang berawalan 'tahunBulan'
        # Contoh: tahunBulan01, tahunBulan01Ht, dst.
        kolom_histori = [c for c in df_kredit.columns if c.startswith('tahunBulan')]
        
        if kolom_histori:
            print(f"   Menghapus {len(kolom_histori)} kolom history (tahunBulan...) agar rapi.")
            df_kredit.drop(columns=kolom_histori, inplace=True)

    # --- SIMPAN KE EXCEL ---
    print("2. Menyimpan ke Excel...")
    with pd.ExcelWriter(nama_file_excel, engine='openpyxl') as writer:
        
        # Sheet 1
        if not df_profil.empty:
            df_profil.to_excel(writer, sheet_name='Data Profil', index=False)
        
        # Sheet 2 (Kredit tanpa history panjang)
        if not df_kredit.empty:
            df_kredit.to_excel(writer, sheet_name='Data Fasilitas Kredit', index=False)
        
        # Sheet 3
        if not df_agunan.empty:
            # Rapikan urutan kolom
            cols = df_agunan.columns.tolist()
            cols_ref = [c for c in cols if c.startswith('(REF)')]
            cols_status = [c for c in cols if c == 'Status Agunan']
            cols_data = [c for c in cols if c not in cols_ref and c not in cols_status]
            
            df_agunan = df_agunan[cols_ref + cols_status + cols_data]
            df_agunan.to_excel(writer, sheet_name='Data Agunan', index=False)

    print(f"\n✅ SUKSES! File Clean tersimpan di: {nama_file_excel}")

except Exception as e:
    print(f"❌ TERJADI ERROR: {e}")