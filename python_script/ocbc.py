import os
import pandas as pd
import sys
import json
import warnings

# Matikan warning
warnings.simplefilter(action='ignore', category=FutureWarning)

# ====================================================================
# --- KONFIGURASI DINAMIS ---
# ====================================================================

# Kita butuh 2 argumen: [script.py, input.pdf, output.csv]
if len(sys.argv) < 3:
    print(json.dumps({"status": "error", "message": "Argumen kurang: butuh input_pdf dan output_csv"}))
    sys.exit(1)

nama_file_input = sys.argv[1] # Argumen 1: Lokasi PDF
nama_file_output = sys.argv[2] # Argumen 2: Lokasi Simpan CSV Nanti

# ====================================================================
# --- FUNGSI HELPER (LOGIKA EKSTRAKSI) ---
# ====================================================================

def extract_and_process(pdf_path):
    import tabula
    
    # 1. BACA PDF
    try:
        # Baca semua tabel
        dfs = tabula.read_pdf(pdf_path, pages='all', lattice=True, multiple_tables=True, pandas_options={'header': None}, silent=True)
    except Exception as e:
        return None, str(e)

    if not dfs:
        return None, "Tidak ada tabel ditemukan"

    # 2. BERSIHKAN TABEL (Logika Cleaning dari script aslimu)
    cleaned_tables = []
    for df in dfs:
        # --- Logika Cleaning Sederhana (adaptasi dari script aslimu) ---
        # Mencari header yang valid
        header_idx = -1
        for i, row in df.iterrows():
            row_str = ' '.join(str(x) for x in row.values).lower()
            if 'description' in row_str or 'debit' in row_str or 'credit' in row_str:
                header_idx = i
                break
        
        if header_idx != -1:
            # Ambil data setelah header
            new_df = df[header_idx + 1:].copy()
            new_df.columns = df.iloc[header_idx] # Set header
            cleaned_tables.append(new_df)
    
    if not cleaned_tables:
        # Fallback: jika cleaning gagal, gabung apa adanya (opsional)
        return None, "Gagal membersihkan tabel"

    # 3. GABUNG SEMUA
    try:
        final_df = pd.concat(cleaned_tables, ignore_index=True)
    except:
        return None, "Gagal menggabungkan tabel"

    # 4. POST PROCESSING (Hapus kolom kosong & baris duplikat)
    final_df = final_df.dropna(how='all', axis=1) # Hapus kolom full kosong
    final_df = final_df.dropna(how='all', axis=0) # Hapus baris full kosong
    
    return final_df, None

# ====================================================================
# --- EKSEKUSI UTAMA ---
# ====================================================================

if __name__ == "__main__":
    try:
        if not os.path.exists(nama_file_input):
            print(json.dumps({"status": "error", "message": "File PDF input tidak ditemukan"}))
            sys.exit(1)

        # Jalankan Proses
        df_hasil, err = extract_and_process(nama_file_input)

        if err:
            print(json.dumps({"status": "error", "message": err}))
            sys.exit(1)

        # SIMPAN KE CSV (Sesuai request kamu)
        # index=False agar nomor baris (0,1,2...) tidak ikut tersimpan
        df_hasil.to_csv(nama_file_output, index=False)

        # Beri kabar ke Laravel bahwa sukses & file sudah jadi
        print(json.dumps({
            "status": "success", 
            "file": nama_file_output,
            "rows": len(df_hasil)
        }))

    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)