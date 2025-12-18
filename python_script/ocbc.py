import os
import pandas as pd
import sys
import json
import warnings

# Matikan warning
warnings.simplefilter(action='ignore', category=FutureWarning)

# ==========================================
# 1. KONFIGURASI JAVA (FIXED & SAFETY)
# ==========================================
# Ganti baris ini dengan nama folder JDK yang ada di dalam Eclipse Adoptium kamu
# Contoh: C:\Program Files\Eclipse Adoptium\jdk-17.0.8.101-hotspot
# (Pastikan kamu cek folder aslinya di File Explorer)
java_path = r"C:\Program Files\Java\jdk-21"

# Cek apakah folder Java benar-benar ada
if not os.path.exists(java_path):
    # Coba cari otomatis di folder Eclipse Adoptium jika path di atas salah
    base_adoptium = r"C:\Program Files\Eclipse Adoptium"
    if os.path.exists(base_adoptium):
        subfolders = [f.path for f in os.scandir(base_adoptium) if f.is_dir()]
        if subfolders:
            java_path = subfolders[0] # Pakai folder pertama yang ditemukan
        else:
            print(json.dumps({"status": "error", "message": f"Folder Java kosong di: {base_adoptium}"}))
            sys.exit(1)
    else:
        print(json.dumps({"status": "error", "message": f"Java tidak ditemukan. Cek path: {java_path}"}))
        sys.exit(1)

# Set Environment Java
os.environ["JAVA_HOME"] = java_path
java_bin = os.path.join(java_path, "bin")

# --- PERBAIKAN ERROR 'KeyError: PATH' DI SINI ---
# Kita ambil PATH yang ada, kalau tidak ada kita kasih string kosong ""
current_path = os.environ.get("PATH", "")
os.environ["PATH"] = java_bin + os.pathsep + current_path

# ==========================================
# 2. TERIMA INPUT DARI LARAVEL
# ==========================================
if len(sys.argv) < 3:
    print(json.dumps({"status": "error", "message": "Argumen kurang. Gunakan: python ocbc.py [input_pdf] [output_csv]"}))
    sys.exit(1)

nama_file_input = sys.argv[1]  # Path PDF dari Laravel
nama_file_output = sys.argv[2] # Path CSV Tujuan dari Laravel

# ==========================================
# 3. LOGIKA EKSTRAKSI (SAMA SEPERTI ASLI)
# ==========================================
def process_pdf(pdf_path):
    import tabula 
    try:
        # silent=True agar tidak mengotori output JSON
        dfs = tabula.read_pdf(pdf_path, pages='all', lattice=True, multiple_tables=True, pandas_options={'header': None}, silent=True)
    except Exception as e:
        return None, f"Gagal baca PDF: {str(e)}"

    if not dfs: return None, "Tabel tidak ditemukan"

    try:
        cleaned_tables = []
        for df in dfs:
            df.dropna(how='all', inplace=True) # Hapus baris kosong
            if not df.empty:
                cleaned_tables.append(df)
        
        if not cleaned_tables: return None, "Data tabel kosong"

        final_df = pd.concat(cleaned_tables, ignore_index=True)
        # Hapus baris/kolom yang full kosong
        final_df.dropna(how='all', axis=1, inplace=True)
        final_df.dropna(how='all', axis=0, inplace=True)
        
        return final_df, None
    except Exception as e:
        return None, f"Gagal proses data: {str(e)}"

# ==========================================
# 4. EKSEKUSI UTAMA
# ==========================================
if __name__ == "__main__":
    try:
        if not os.path.exists(nama_file_input):
            print(json.dumps({"status": "error", "message": "File PDF input tidak ditemukan"}))
            sys.exit(1)

        df_hasil, error = process_pdf(nama_file_input)

        if error:
            print(json.dumps({"status": "error", "message": error}))
            sys.exit(1)

        # Simpan CSV
        df_hasil.to_csv(nama_file_output, index=False, header=False)

        # Balas Sukses
        print(json.dumps({"status": "success", "file": nama_file_output}))

    except Exception as e:
        print(json.dumps({"status": "error", "message": str(e)}))
        sys.exit(1)