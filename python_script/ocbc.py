# import os
# import pandas as pd
# import sys
# import json
# import warnings

# # Matikan warning
# warnings.simplefilter(action='ignore', category=FutureWarning)

# # ==========================================
# # 1. KONFIGURASI JAVA (FIXED & SAFETY)
# # ==========================================
# # Ganti baris ini dengan nama folder JDK yang ada di dalam Eclipse Adoptium kamu
# # Contoh: C:\Program Files\Eclipse Adoptium\jdk-17.0.8.101-hotspot
# # (Pastikan kamu cek folder aslinya di File Explorer)
# java_path = r"C:\Program Files\Java\jdk-21"

# # Cek apakah folder Java benar-benar ada
# if not os.path.exists(java_path):
#     # Coba cari otomatis di folder Eclipse Adoptium jika path di atas salah
#     base_adoptium = r"C:\Program Files\Eclipse Adoptium"
#     if os.path.exists(base_adoptium):
#         subfolders = [f.path for f in os.scandir(base_adoptium) if f.is_dir()]
#         if subfolders:
#             java_path = subfolders[0] # Pakai folder pertama yang ditemukan
#         else:
#             print(json.dumps({"status": "error", "message": f"Folder Java kosong di: {base_adoptium}"}))
#             sys.exit(1)
#     else:
#         print(json.dumps({"status": "error", "message": f"Java tidak ditemukan. Cek path: {java_path}"}))
#         sys.exit(1)

# # Set Environment Java
# os.environ["JAVA_HOME"] = java_path
# java_bin = os.path.join(java_path, "bin")

# # --- PERBAIKAN ERROR 'KeyError: PATH' DI SINI ---
# # Kita ambil PATH yang ada, kalau tidak ada kita kasih string kosong ""
# current_path = os.environ.get("PATH", "")
# os.environ["PATH"] = java_bin + os.pathsep + current_path

# # ==========================================
# # 2. TERIMA INPUT DARI LARAVEL
# # ==========================================
# if len(sys.argv) < 3:
#     print(json.dumps({"status": "error", "message": "Argumen kurang. Gunakan: python ocbc.py [input_pdf] [output_csv]"}))
#     sys.exit(1)

# nama_file_input = sys.argv[1]  # Path PDF dari Laravel
# nama_file_output = sys.argv[2] # Path CSV Tujuan dari Laravel

# # ==========================================
# # 3. LOGIKA EKSTRAKSI (SAMA SEPERTI ASLI)
# # ==========================================
# def process_pdf(pdf_path):
#     import tabula 
#     try:
#         # silent=True agar tidak mengotori output JSON
#         dfs = tabula.read_pdf(pdf_path, pages='all', lattice=True, multiple_tables=True, pandas_options={'header': None}, silent=True)
#     except Exception as e:
#         return None, f"Gagal baca PDF: {str(e)}"

#     if not dfs: return None, "Tabel tidak ditemukan"

#     try:
#         cleaned_tables = []
#         for df in dfs:
#             df.dropna(how='all', inplace=True) # Hapus baris kosong
#             if not df.empty:
#                 cleaned_tables.append(df)
        
#         if not cleaned_tables: return None, "Data tabel kosong"

#         final_df = pd.concat(cleaned_tables, ignore_index=True)
#         # Hapus baris/kolom yang full kosong
#         final_df.dropna(how='all', axis=1, inplace=True)
#         final_df.dropna(how='all', axis=0, inplace=True)
        
#         return final_df, None
#     except Exception as e:
#         return None, f"Gagal proses data: {str(e)}"

# # ==========================================
# # 4. EKSEKUSI UTAMA
# # ==========================================
# if __name__ == "__main__":
#     try:
#         if not os.path.exists(nama_file_input):
#             print(json.dumps({"status": "error", "message": "File PDF input tidak ditemukan"}))
#             sys.exit(1)

#         df_hasil, error = process_pdf(nama_file_input)

#         if error:
#             print(json.dumps({"status": "error", "message": error}))
#             sys.exit(1)

#         # Simpan CSV
#         df_hasil.to_csv(nama_file_output, index=False, header=False)

#         # Balas Sukses
#         print(json.dumps({"status": "success", "file": nama_file_output}))

#     except Exception as e:
#         print(json.dumps({"status": "error", "message": str(e)}))
#         sys.exit(1)



import os
import sys
import json
import re
import pandas as pd
import warnings

warnings.simplefilter(action="ignore", category=FutureWarning)

# =====================================================
# 1. KONFIGURASI JAVA (ECLIPSE ADOPTIUM)
# =====================================================
JAVA_PATH = r"C:\Program Files\Eclipse Adoptium\jdk-21.0.1-hotspot"

# Auto-detect folder jika path sedikit berbeda
if not os.path.exists(JAVA_PATH):
    base_adoptium = r"C:\Program Files\Eclipse Adoptium"
    if os.path.exists(base_adoptium):
        try:
            subfolders = [f.path for f in os.scandir(base_adoptium) if f.is_dir()]
            if subfolders: JAVA_PATH = subfolders[0]
        except: pass

if not os.path.exists(JAVA_PATH):
    print(json.dumps({"status": "error", "message": f"Java tidak ditemukan di: {JAVA_PATH}"}))
    sys.exit(1)

os.environ["JAVA_HOME"] = JAVA_PATH
os.environ["PATH"] = os.path.join(JAVA_PATH, "bin") + os.pathsep + os.environ.get("PATH", "")

# =====================================================
# 2. VALIDASI ARGUMEN
# =====================================================
if len(sys.argv) < 3:
    print(json.dumps({"status": "error", "message": "Argumen kurang."}))
    sys.exit(1)

INPUT_PDF = sys.argv[1]
OUTPUT_XLSX = sys.argv[2]

if not os.path.exists(INPUT_PDF):
    print(json.dumps({"status": "error", "message": "File PDF tidak ditemukan"}))
    sys.exit(1)

# =====================================================
# 3. IMPORT TABULA
# =====================================================
try:
    import tabula
except Exception as e:
    print(json.dumps({"status": "error", "message": f"Gagal load tabula: {str(e)}"}))
    sys.exit(1)

# =====================================================
# 4. LOGIKA EKSTRAKSI & CLEANING (DIPERBAIKI)
# =====================================================
def extract_tables(pdf_path):
    try:
        return tabula.read_pdf(
            pdf_path, 
            pages="all", 
            lattice=True, 
            multiple_tables=True, 
            pandas_options={"header": None}, 
            silent=True
        )
    except: return []

def clean_individual_table(df):
    header_idx = None
    header_content = None
    
    # Cari baris header
    for i, row in df.iterrows():
        text = (" ".join(map(str, df.columns)) + " " + " ".join(map(str, row.values))).lower()
        # Deteksi keyword keuangan
        if "description" in text or "debit" in text or "credit" in text or "balance" in text:
            header_idx = i
            header_content = row.values
            break
            
    if header_idx is None: return pd.DataFrame()

    cleaned = df[header_idx + 1:].copy()
    headers = pd.Series(header_content).astype(str)

    # --- PERBAIKAN LOGIKA HEADER (FIX NAN & TYPO) ---
    new_headers = []
    
    # Mapping untuk memperbaiki kata yang terpotong (sisi kiri/kanan)
    header_corrections = {
        "nsaction": "TransactionDate",
        "actionda": "TransactionDate",
        "valuedat": "ValueDate",
        "eference": "ReferenceNo",
        "chequen": "ChequeNo",
        "escriptio": "Description",
        "debit": "Debit",
        "credit": "Credit",
        "balance": "Balance"
    }

    for h in headers:
        h_str = str(h).strip()
        h_lower = h_str.lower()
        
        # 1. Cek apakah ini kolom 'nan' atau 'unnamed' -> Paksa jadi Balance
        if h_lower == 'nan' or 'unnamed' in h_lower:
            new_headers.append('Balance')
            continue
            
        # 2. Cek apakah perlu diperbaiki typo-nya
        replaced = False
        for key, correct_val in header_corrections.items():
            if key in h_lower:
                new_headers.append(correct_val)
                replaced = True
                break
        
        if not replaced:
            new_headers.append(h_str)

    cleaned.columns = new_headers
    cleaned.dropna(how="all", inplace=True)
    return cleaned

def combine_tables(dfs):
    cleaned = []
    for df in dfs:
        c = clean_individual_table(df)
        if not c.empty: cleaned.append(c)
    
    if not cleaned: return pd.DataFrame()
    
    final = pd.concat(cleaned, ignore_index=True)
    final = final.loc[:, ~final.columns.isna()]
    final.columns = [str(c) for c in final.columns]
    
    # Handle duplikasi nama kolom
    seen = {}
    cols = []
    for c in final.columns:
        if c in seen:
            seen[c] += 1
            cols.append(f"{c}.{seen[c]}")
        else:
            seen[c] = 1
            cols.append(c)
    final.columns = cols
    
    final.dropna(how="all", inplace=True)
    final.reset_index(drop=True, inplace=True)
    return final

def merge_description_lines(df):
    desc_cols = [c for c in df.columns if "description" in c.lower()]
    if not desc_cols: return df
    
    main_desc = desc_cols[0]
    non_desc = [c for c in df.columns if c not in desc_cols]
    
    rows = []
    for _, row in df.iterrows():
        # Cek apakah baris ini punya data di kolom lain (Tanggal/Angka)
        is_main = not row[non_desc].isnull().all()
        
        if is_main: 
            rows.append(row.copy())
        else:
            # Ini baris lanjutan deskripsi
            if rows:
                extra = " ".join(str(row[c]) for c in desc_cols if pd.notna(row[c])).strip()
                # Filter agar tidak menggabungkan sampah
                if extra and not re.fullmatch(r"[0-9\s/]{12,}", extra):
                    rows[-1][main_desc] = str(rows[-1][main_desc]) + " " + extra
                    
    final = pd.DataFrame(rows)
    
    # Bersihkan isi teks deskripsi
    for c in desc_cols:
        final[c] = (final[c].astype(str)
                    .str.replace("nan", "", case=False)
                    .str.replace(r"\s+", " ", regex=True)
                    .str.strip())
                    
    final.columns = (final.columns
                     .str.replace(" ", "", regex=False)
                     .str.replace(r"[^\w]", "", regex=True))
    return final

# =====================================================
# 5. EKSEKUSI & SIMPAN KE EXCEL
# =====================================================
try:
    if not os.path.exists(INPUT_PDF):
        print(json.dumps({"status": "error", "message": "Input PDF missing"}))
        sys.exit(1)

    raw_tables = extract_tables(INPUT_PDF)
    df_combined = combine_tables(raw_tables)

    if df_combined.empty:
        df_final = pd.DataFrame()
    else:
        df_final = merge_description_lines(df_combined)

    # Simpan ke Excel
    df_final.to_excel(OUTPUT_XLSX, index=False)

    print(json.dumps({
        "status": "success",
        "file": OUTPUT_XLSX,
        "rows": int(len(df_final))
    }))

except Exception as e:
    print(json.dumps({"status": "error", "message": f"System Error: {str(e)}"}))
    sys.exit(1)