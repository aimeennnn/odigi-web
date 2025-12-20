import os
import sys
import json
import re
import pandas as pd
import warnings
import numpy as np

# Matikan warning pandas biar output bersih
warnings.simplefilter(action="ignore")

# =====================================================
# 1. KONFIGURASI JAVA (WAJIB UNTUK TABULA)
# =====================================================
# Sesuaikan path ini dengan lokasi JDK di laptop/server kamu
JAVA_PATH = r"C:\Program Files\Java\jdk-21"

if not os.path.exists(JAVA_PATH):
    # Fallback cek environment variable kalau path hardcode tidak ada
    if not os.environ.get("JAVA_HOME"):
        print(json.dumps({
            "status": "error",
            "message": "JAVA_HOME tidak ditemukan. Install Java atau set path di script."
        }))
        sys.exit(1)
else:
    os.environ["JAVA_HOME"] = JAVA_PATH
    os.environ["PATH"] = os.path.join(JAVA_PATH, "bin") + os.pathsep + os.environ.get("PATH", "")

# =====================================================
# 2. INPUT DARI LARAVEL
# =====================================================
# Kita pakai logic sys.argv agar dinamis dari Laravel
if len(sys.argv) < 3:
    # Fallback buat testing manual di VS Code (Biar gak error kalau di-run tanpa argumen)
    # Ganti path ini sesuai file di laptopmu untuk test
    INPUT_PDF = r"D:\Kuliah\Magang\Mandiri Gajian - JANUARI.pdf" 
    OUTPUT_XLSX = "Hasil_Test_Laptop.xlsx"
else:
    INPUT_PDF = sys.argv[1]
    OUTPUT_XLSX = sys.argv[2]

if not os.path.exists(INPUT_PDF):
    print(json.dumps({
        "status": "error",
        "message": "File PDF input tidak ditemukan"
    }))
    sys.exit(1)

# =====================================================
# 3. LOAD TABULA
# =====================================================
try:
    import tabula
except ImportError:
    print(json.dumps({
        "status": "error",
        "message": "Library 'tabula-py' belum terinstall"
    }))
    sys.exit(1)

# =====================================================
# 4. HELPER FUNCTIONS (DARI mandiri_baru.py)
# =====================================================
def clean_currency(val):
    """Membersihkan format uang string ke float."""
    if not val: return 0.0
    s = str(val).replace(',', '').strip()
    if '(' in s and ')' in s: 
        s = '-' + s.replace('(', '').replace(')', '')
    try:
        return float(s)
    except:
        return 0.0

def is_looks_like_money(text):
    """Cek format uang: angka, koma, titik desimal."""
    if not text: return False
    return bool(re.match(r'^-?[\d,]+\.\d{2}\)?$', str(text)))

def is_date_start(text):
    """Cek tanggal: 01 Jan 2025"""
    return bool(re.search(r'^\d{1,2}\s+[A-Za-z]{3}\s+\d{4}', str(text)))

def process_line_logic(items):
    """Logika mundur: Ambil Saldo -> Kredit -> Debit dari belakang list."""
    balance = 0.0
    credit = 0.0
    debit = 0.0
    
    # 1. Cari BALANCE (Pasti item paling belakang yang valid uang)
    if items and is_looks_like_money(items[-1]):
        balance = clean_currency(items.pop()) 
        
        # 2. Cari CREDIT
        if items and is_looks_like_money(items[-1]):
            credit = clean_currency(items.pop())
            
            # 3. Cari DEBIT
            if items and is_looks_like_money(items[-1]):
                debit = clean_currency(items.pop())
    
    return debit, credit, balance, items

# =====================================================
# 5. CORE PARSER (LOGIKA mandiri_baru.py)
# =====================================================
def process_mandiri_parser(pdf_path):
    try:
        # BACA PDF (Silent mode biar gak ngerusak JSON output)
        dfs = tabula.read_pdf(
            pdf_path, 
            pages='all', 
            stream=True, 
            guess=False, 
            pandas_options={'header': None},
            silent=True 
        )
    except Exception as e:
        # Jangan print error text, tapi return empty biar di-handle main block
        return pd.DataFrame()

    if not dfs: return pd.DataFrame()
    raw_df = pd.concat(dfs, ignore_index=True)

    structured_data = []
    current_trx = None

    # Keyword Sampah (Sesuai file asli)
    skip_keywords = [
        'account statement', 'account no', 'period', 'currency', 
        'opening balance', 'closing balance', 'total amount', 
        'posting date', 'remark', 'reference no', 'debit', 'credit', 'balance',
        'halaman', 'page', 'tanggal cetak'
    ]

    for _, row in raw_df.iterrows():
        # Gabung baris jadi satu string
        full_line_str = " ".join([str(x).strip() for x in row.values if pd.notna(x) and str(x).strip() != ''])
        
        if not full_line_str: continue
        if any(kw in full_line_str.lower() for kw in skip_keywords): continue
        if ' - ' in full_line_str and 'jan' in full_line_str.lower(): continue

        word_list = full_line_str.split()
        first_3_words = " ".join(word_list[:3])
        
        if is_date_start(first_3_words):
            # === TRANSAKSI BARU ===
            if current_trx: structured_data.append(current_trx)
            
            debit, credit, balance, sisa_kata = process_line_logic(word_list)
            
            # Ambil Tanggal
            raw_date = " ".join(sisa_kata[:3])
            del sisa_kata[:3]
            
            # Cek Jam
            if sisa_kata and re.match(r'\d{1,2}:\d{1,2}:\d{1,2}', sisa_kata[0]):
                raw_date += " " + sisa_kata.pop(0)
            
            remark_text = " ".join(sisa_kata)
            
            current_trx = {
                'PostingDate': raw_date,
                'RemarkLines': [remark_text] if remark_text else [],
                'ReferenceNo': '-',
                'Debit': debit,
                'Credit': credit,
                'Balance': balance
            }
            
        elif current_trx:
            # === BARIS LANJUTAN ===
            if word_list and re.match(r'^\d{1,2}:\d{1,2}:\d{1,2}$', word_list[0]):
                current_trx['PostingDate'] += " " + word_list.pop(0)
            
            l_debit, l_credit, l_balance, l_sisa_kata = process_line_logic(word_list)
            
            # Logic cek saldo di baris lanjutan
            if current_trx['Balance'] == 0 and l_balance != 0:
                current_trx['Balance'] = l_balance
                current_trx['Credit'] = l_credit
                current_trx['Debit'] = l_debit
                text_content = " ".join(l_sisa_kata)
            else:
                text_content = " ".join(l_sisa_kata)
            
            if text_content:
                current_trx['RemarkLines'].append(text_content)

    if current_trx: structured_data.append(current_trx)

    # Formatting ke Excel
    final_rows = []
    for trx in structured_data:
        full_remark = "\n".join(trx['RemarkLines'])
        final_rows.append({
            'PostingDate': trx['PostingDate'],
            'Remark': full_remark,
            'ReferenceNo': '-', 
            'Debit': trx['Debit'],
            'Credit': trx['Credit'],
            'Balance': trx['Balance']
        })
        
    return pd.DataFrame(final_rows)

# =====================================================
# 6. MAIN EXECUTION (OUTPUT JSON UNTUK LARAVEL)
# =====================================================
if __name__ == "__main__":
    try:
        # Jalankan Logic Parser
        df_hasil = process_mandiri_parser(INPUT_PDF)
        
        if not df_hasil.empty:
            # Simpan Excel
            df_hasil.to_excel(OUTPUT_XLSX, index=False)
            
            # Output JSON Success (Sesuai format OCBC lama yang diinginkan controller)
            print(json.dumps({
                "status": "success",
                "file": OUTPUT_XLSX,
                "rows": len(df_hasil)
            }))
        else:
            # JSON Error (Data Kosong)
            print(json.dumps({
                "status": "error",
                "message": "Data kosong atau PDF tidak terbaca"
            }))

    except Exception as e:
        # JSON Error (Crash)
        print(json.dumps({
            "status": "error",
            "message": str(e)
        }))
        sys.exit(1)