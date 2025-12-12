<div class="modal fade" id="modalTambahKomite" tabindex="-1" aria-labelledby="modalTambahKomiteLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" id="modalDialog" style="max-width: 95vw; width: 95vw; transition: all 0.3s ease;">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%);">
        <h5 class="modal-title text-white" id="modalTambahKomiteLabel">Tambah Data Komite</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('komite.store') }}" method="POST" id="komiteForm">
        @csrf
        @if(request('register_id'))
            <input type="hidden" name="register_id" value="{{ request('register_id') }}">
        @endif
        <input type="hidden" name="tipe_memorandum" id="tipe_memorandum" value="rekomendasi">
        <div class="modal-body" style="padding: 1.5rem 1.25rem; min-height: 65vh; display: flex; flex-direction: column; justify-content: flex-start;">
          @php
              // Gunakan session current_register_id jika ada, atau fallback ke request parameter
              $idReg = session('current_register_id') ?? request('id_reg') ?? request('register_id');
              // Force fresh data by clearing any potential cache
              $reg = $idReg ? App\Models\Register::find($idReg) : null;
              // Ensure we get fresh data by refreshing the model
              if($reg) {
                  $reg->refresh();
              }
          @endphp
          @if($reg)
                <div class="d-flex justify-content-start mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleDetailSidebar()" title="Detail Data">
                        <i class="bi bi-eye me-1"></i>Detail Data
                    </button>
                </div>
            @else
                <div class="d-flex justify-content-start mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Detail Data">
                        <i class="bi bi-eye me-1"></i>Detail Data
                    </button>
                </div>
          @endif
          <div class="row g-3">
            <!-- Baris 1: Nama Nasabah (kiri) dan Keputusan (kanan) -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="id_reg" class="form-label fw-bold text-primary mb-2">Nama Nasabah</label>
                <div class="input-group">
                  @if($reg)
                      <input type="text" class="form-control bg-light" value="{{ $reg->nomor . ' - ' . $reg->nama }}" readonly>
                      <input type="hidden" name="id_reg" value="{{ $reg->id_reg }}">
                  @else
                      <input type="text" class="form-control bg-light" value="Registrasi belum dipilih" readonly>
                      <input type="hidden" name="id_reg" value="">
                  @endif
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
              </div>
            </div>
            @if((\App\Helpers\RoleHelper::getKomiteRole() === 'direktur_utama') || \App\Helpers\RoleHelper::isSuperAdmin())
            <div class="col-md-6" id="keputusan_group" style="display:none;">
              <div class="mb-3">
                <label for="keputusan" class="form-label fw-bold text-primary">Keputusan</label>
                <div class="input-group">
                  <select class="form-select" id="keputusan" name="keputusan">
                      <option value="">-- Pilih Keputusan --</option>
                      @foreach(\App\Models\Komite::keputusanList() as $key => $label)
                          <option value="{{ $key }}">{{ $label }}</option>
                      @endforeach
                  </select>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
              </div>
            </div>
            @else
            <input type="hidden" name="keputusan" value="">
            @endif
            
            <!-- Baris 2: Tanggal (kiri) -->
            <div class="col-md-6">
              <div class="mb-3">
                <label for="tgl" class="form-label fw-bold text-primary">Tanggal</label>
                <div class="input-group">
                  <input type="date" class="form-control" name="tgl" id="tgl" value="{{ date('Y-m-d') }}" required>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <!-- Kolom kosong untuk alignment -->
            </div>
            
            <!-- Baris 3: Keterangan (full width) -->
            <div class="col-12">
              <div class="mb-0">
                <label for="keterangan" class="form-label fw-bold text-primary">Keterangan</label>
                <div class="input-group">
                  <div class="summernote-container">
                    <textarea class="form-control summernote @error('keterangan') is-invalid @enderror" id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan lengkap mengenai keputusan komite..." required></textarea>
                    <!-- Proxy input untuk memunculkan tooltip native HTML5 ketika Summernote kosong -->
                    <input type="text" id="keterangan_required_proxy" required aria-hidden="true" tabindex="-1" style="position:absolute; right:12px; bottom:12px; width:1px; height:1px; opacity:0; pointer-events:none; background:transparent; border:0;" />
                  </div>
                  <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                </div>
                <div id="keterangan_error" class="text-danger small mt-1" style="display:none;">Mohon isi keterangan.</div>
                @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer d-flex justify-content-end gap-2" style="padding: 1rem 1.25rem 1.5rem 1.25rem;">
          <button type="submit" class="btn" style="background:#1dd1a1; color:white; font-weight:600;"><i class="bi bi-save me-1"></i>Simpan Data</button>
          <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Detail Sidebar -->
<div id="detailSidebar" class="detail-sidebar">
  <div class="detail-sidebar-content">
    <div class="detail-sidebar-header">
      <h5 class="detail-sidebar-title">
        <i class="bi bi-eye me-2"></i>Detail Data
      </h5>
      <button type="button" class="btn-close btn-close-white" onclick="toggleDetailSidebar()" aria-label="Close"></button>
    </div>
    
    <!-- Navigation Tabs -->
    <div class="detail-nav-tabs">
      <button class="nav-tab active" data-tab="detail" onclick="switchDetailTab('detail')">
        <i class="bi bi-eye"></i>
        <span>Detail</span>
      </button>
      <button class="nav-tab" data-tab="data" onclick="switchDetailTab('data')">
        <i class="bi bi-folder"></i>
        <span>Data</span>
      </button>
      <button class="nav-tab" data-tab="bank" onclick="switchDetailTab('bank')">
        <i class="bi bi-building"></i>
        <span>Bank</span>
      </button>
      <button class="nav-tab" data-tab="slik" onclick="switchDetailTab('slik')">
        <i class="bi bi-file-text"></i>
        <span>SLIK</span>
      </button>
    </div>
    
    <div class="detail-sidebar-body">
        @php
            $idReg = session('current_register_id') ?? request('id_reg') ?? request('register_id');
            $reg = $idReg ? App\Models\Register::find($idReg) : null;
            // Force fresh data by refreshing the model
            if($reg) {
                $reg->refresh();
            }
            $dataList = $reg ? App\Models\Data::where('id_reg', $reg->id_reg)->get() : collect();
            $bankList = $reg ? App\Models\Bank::where('id_reg', $reg->id_reg)->get() : collect();
            $slikList = $reg ? App\Models\Slik::where('id_reg', $reg->id_reg)->get() : collect();
            
            // Debug info - uncomment to debug
            // dd([
            //     'idReg' => $idReg,
            //     'reg' => $reg,
            //     'data' => $data,
            //     'bank' => $bank,
            //     'slik' => $slik,
            //     'komite' => $komite
            // ]);
        @endphp
        
        <!-- Tab Content: Detail (Register) -->
        <div id="tab-detail" class="tab-content active">
        @if($reg)
          <!-- Header dengan Avatar -->
          <div class="detail-header-card">
            <div class="d-flex align-items-center">
              <div class="detail-avatar">
                {{ strtoupper(substr($reg->nama ?? 'U',0,1)) }}
              </div>
              <div class="detail-info">
                <h3 class="detail-name">{{ $reg->nama ?? 'Nama Tidak Ditemukan' }}</h3>
                <div class="detail-status">
                  <i class="bi bi-check-circle me-1"></i>{{ $reg->status_label ?? 'Dalam Proses' }}
                </div>
                <div class="detail-input-by">
                  <i class="bi bi-person-badge me-1"></i>Diinput oleh: {{ $reg->input_by ?? 'System' }}
                  <!-- Cache busting: {{ time() }} -->
                </div>
              </div>
            </div>
          </div>
          
          <!-- Informasi Dasar -->
          <div class="detail-section">
            <div class="detail-section-header">
              <i class="bi bi-file-earmark-text me-2"></i>Informasi Dasar
            </div>
            <div class="detail-section-body">
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-hash"></i>Nomor Registrasi
                </div>
                <div class="detail-field-value highlight">{{ $reg->nomor ?? '003/REG/2025' }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-calendar-event"></i>Tanggal Pengajuan
                </div>
                <div class="detail-field-value">{{ $reg->tgl_pengajuan ? \Carbon\Carbon::parse($reg->tgl_pengajuan)->translatedFormat('d F Y') : '13 September 2025' }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-file-earmark-text"></i>Jenis Pengajuan
                </div>
                <div class="detail-field-value">{{ $reg->jenis_pengajuan_label ?? 'UMUM' }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-tag"></i>Jenis Entitas
                </div>
                <div class="detail-field-value">
                  @if(($reg->jenis_entitas ?? 'perorangan') === 'perorangan')
                    <span class="detail-badge primary">Perorangan</span>
                  @else
                    <span class="detail-badge success">Badan Usaha</span>
                  @endif
                </div>
              </div>
            </div>
          </div>
          
          @if(($reg->jenis_entitas ?? 'perorangan') === 'perorangan')
          <!-- Data Pribadi -->
          <div class="detail-section">
            <div class="detail-section-header">
              <i class="bi bi-person me-2"></i>Data Pribadi
            </div>
            <div class="detail-section-body">
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-person"></i>Nama Lengkap
                </div>
                <div class="detail-field-value highlight">{{ $reg->nama ?? 'FATTAH RAFIF SYAUQI' }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-gender-ambiguous"></i>Jenis Kelamin
                </div>
                <div class="detail-field-value">{{ $reg->jns_kelamin ?? 'Laki-laki' }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-card-text"></i>No Identitas
                </div>
                <div class="detail-field-value">{{ $reg->no_identitas ?? '1234567890123456' }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-briefcase"></i>Pekerjaan
                </div>
                <div class="detail-field-value">{{ $reg->pekerjaan ?? 'Karyawan Swasta' }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-geo-alt"></i>Alamat
                </div>
                <div class="detail-field-value">{{ $reg->alamat ?? 'Jl. Contoh No. 123, Jakarta' }}</div>
              </div>
            </div>
          </div>
          @else
          <!-- Data Badan Usaha -->
          <div class="detail-section">
            <div class="detail-section-header">
              <i class="bi bi-building me-2"></i>Data Badan Usaha
            </div>
            <div class="detail-section-body">
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-building"></i>Nama Badan Usaha
                </div>
                <div class="detail-field-value highlight">{{ $reg->nama_badan_usaha }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-file-text"></i>Jenis Dokumen Usaha
                </div>
                <div class="detail-field-value">{{ $reg->jenis_dokumen_usaha }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-hash"></i>Nomor Legalitas Usaha
                </div>
                <div class="detail-field-value">{{ $reg->nomor_legalitas_usaha }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-briefcase"></i>Bidang Usaha
                </div>
                <div class="detail-field-value">{{ $reg->bidang_usaha }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-geo-alt"></i>Alamat Usaha
                </div>
                <div class="detail-field-value">{{ $reg->alamat_usaha }}</div>
              </div>
            </div>
          </div>
          @endif
          
          <!-- Pengajuan Kredit -->
          <div class="detail-section">
            <div class="detail-section-header">
              <i class="bi bi-currency-dollar me-2"></i>Pengajuan Kredit
            </div>
            <div class="detail-section-body">
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-currency-dollar"></i>Nominal Pengajuan
                </div>
                <div class="detail-field-value highlight">Rp {{ number_format($reg->nominal_pengajuan ?? 50000000,0,',','.') }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-calendar-range"></i>Jangka Waktu
                </div>
                <div class="detail-field-value highlight">{{ $reg->jw_pengajuan ?? '12 Bulan' }}</div>
              </div>
              <div class="detail-field">
                <div class="detail-field-label">
                  <i class="bi bi-shield-check"></i>Jaminan
                </div>
                <div class="detail-field-value">{{ $reg->jaminan ?? 'Sertifikat Tanah' }}</div>
              </div>
            </div>
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
            <h5 class="mt-3 text-muted">Data Register Tidak Ditemukan</h5>
            <p class="text-muted">Silakan pilih register terlebih dahulu</p>
          </div>
        @endif
        </div>
        
        <!-- Tab Content: Data -->
        <div id="tab-data" class="tab-content">
        @if($dataList->count() > 0)
          <div class="data-list-container">
            <div class="data-list-header">
              <div class="data-list-title">
                <i class="bi bi-folder2-open me-2"></i>
                <span>Data Pengajuan</span>
                <span class="data-count-badge">{{ $dataList->count() }}</span>
              </div>
            </div>
            
            <div class="data-list-body">
              @foreach($dataList as $index => $data)
                <div class="data-card" data-data-id="{{ $data->id_data }}">
                  <div class="data-card-header" onclick="toggleDataDetail({{ $data->id_data }})">
                    <div class="data-card-info">
                      <div class="data-card-title">
                        <i class="bi bi-file-earmark-text me-2"></i>
                        <span>{{ $data->jenis_data }}</span>
                      </div>
                      <div class="data-card-meta">
                        <div class="meta-item">
                          <i class="bi bi-calendar3 me-1"></i>
                          <span>{{ \Carbon\Carbon::parse($data->created_at)->translatedFormat('d M Y H:i') }}</span>
                        </div>
                        <div class="meta-separator">•</div>
                        <div class="meta-item">
                          <i class="bi bi-person-circle me-1"></i>
                          <span>{{ $data->input_by ?? 'System' }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="data-card-actions">
                      <div class="toggle-indicator">
                        <i class="bi bi-chevron-down"></i>
                      </div>
                    </div>
                  </div>
                  
                  <div class="data-card-content" id="data-detail-{{ $data->id_data }}" style="display: none;">
                    <div class="data-detail-grid">
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-hash"></i>
                          <span>ID Data</span>
                        </div>
                        <div class="detail-value highlight">{{ $data->id_data }}</div>
                      </div>
                      
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-tag"></i>
                          <span>Jenis Data</span>
                        </div>
                        <div class="detail-value">{{ $data->jenis_data }}</div>
                      </div>
                      
                      <div class="detail-item full-width">
                        <div class="detail-label">
                          <i class="bi bi-card-text"></i>
                          <span>Keterangan</span>
                        </div>
                        <div class="detail-value">{{ $data->keterangan ?? '-' }}</div>
                      </div>
                      
                      @if($data->file)
                        <div class="detail-item full-width">
                          <div class="detail-label">
                            <i class="bi bi-paperclip"></i>
                            <span>File Lampiran</span>
                          </div>
                          <div class="detail-value">
                            @php $files = $data->file ? (json_decode($data->file, true) ?: [$data->file]) : []; @endphp
                            @if(count($files))
                              <div class="file-attachments-compact">
                                @foreach($files as $fileIndex => $file)
                                  @php
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg','jpeg','png']);
                                    $encryptedUrl = \App\Helpers\UrlEncryptionHelper::encryptFileUrl($data->id_data, $fileIndex, 'data', $data->jenis_data);
                                    $url = route('file.encrypted', $encryptedUrl);
                                    $fileName = basename($file);
                                    // Truncate long filenames
                                    if (strlen($fileName) > 30) {
                                      $fileName = substr($fileName, 0, 27) . '...';
                                    }
                                  @endphp
                                  <div class="file-item-compact">
                                    <a href="{{ $url }}" target="_blank" class="file-link-compact">
                                      <div class="file-icon-compact">
                                        @if($isImage)
                                          <img src="{{ $url }}" alt="Preview" class="file-preview-compact">
                                        @else
                                          <i class="bi bi-file-earmark"></i>
                                        @endif
                                      </div>
                                      <div class="file-info-compact">
                                        <div class="file-name-compact">{{ $fileName }}</div>
                                        <div class="file-type-compact">{{ strtoupper($ext) }}</div>
                                      </div>
                                    </a>
                                  </div>
                                @endforeach
                              </div>
                            @else
                              <span class="no-files">Tidak ada file</span>
                            @endif
                          </div>
                        </div>
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @else
          <div class="empty-state">
            <div class="empty-state-icon">
              <i class="bi bi-folder-x"></i>
            </div>
            <div class="empty-state-content">
              <h5>Belum Ada Data Pengajuan</h5>
              <p>Data pengajuan belum diinput untuk registrasi ini</p>
            </div>
          </div>
        @endif
        </div>
        
        <!-- Tab Content: Bank -->
        <div id="tab-bank" class="tab-content">
        @if($bankList->count() > 0)
          <div class="data-list-container">
            <div class="data-list-header">
              <div class="data-list-title">
                <i class="bi bi-building me-2"></i>
                <span>Data Bank</span>
                <span class="data-count-badge">{{ $bankList->count() }}</span>
              </div>
            </div>
            
            <div class="data-list-body">
              @foreach($bankList as $index => $bank)
                <div class="data-card" data-bank-id="{{ $bank->id_bank }}">
                  <div class="data-card-header" onclick="toggleBankDetail({{ $bank->id_bank }})">
                    <div class="data-card-info">
                      <div class="data-card-title">
                        <i class="bi bi-bank me-2"></i>
                        <span>{{ $bank->nama_bank }}</span>
                      </div>
                      <div class="data-card-meta">
                        <div class="meta-item">
                          <i class="bi bi-calendar3 me-1"></i>
                          <span>{{ \Carbon\Carbon::parse($bank->created_at)->translatedFormat('d M Y H:i') }}</span>
                        </div>
                        <div class="meta-separator">•</div>
                        <div class="meta-item">
                          <i class="bi bi-person-circle me-1"></i>
                          <span>{{ $bank->input_by ?? 'System' }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="data-card-actions">
                      <div class="toggle-indicator">
                        <i class="bi bi-chevron-down"></i>
                      </div>
                    </div>
                  </div>
                  
                  <div class="data-card-content" id="bank-detail-{{ $bank->id_bank }}" style="display: none;">
                    <div class="data-detail-grid">
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-hash"></i>
                          <span>ID Bank</span>
                        </div>
                        <div class="detail-value highlight">{{ $bank->id_bank }}</div>
                      </div>
                      
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-building"></i>
                          <span>Nama Bank</span>
                        </div>
                        <div class="detail-value">{{ $bank->nama_bank }}</div>
                      </div>
                      
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-credit-card"></i>
                          <span>No Rekening</span>
                        </div>
                        <div class="detail-value">{{ $bank->no_rekening }}</div>
                      </div>
                      
                      @if($bank->file)
                        <div class="detail-item full-width">
                          <div class="detail-label">
                            <i class="bi bi-paperclip"></i>
                            <span>File Lampiran</span>
                          </div>
                          <div class="detail-value">
                            @php $files = $bank->file ? (json_decode($bank->file, true) ?: [$bank->file]) : []; @endphp
                            @if(count($files))
                              <div class="file-attachments-compact">
                                @foreach($files as $fileIndex => $file)
                                  @php
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    $isImage = in_array($ext, ['jpg','jpeg','png']);
                                    $encryptedUrl = \App\Helpers\UrlEncryptionHelper::encryptFileUrl($bank->id_bank, $fileIndex, 'bank', $bank->nama_bank);
                                    $url = route('file.encrypted', $encryptedUrl);
                                    $fileName = basename($file);
                                    if (strlen($fileName) > 30) {
                                      $fileName = substr($fileName, 0, 27) . '...';
                                    }
                                  @endphp
                                  <div class="file-item-compact">
                                    <a href="{{ $url }}" target="_blank" class="file-link-compact">
                                      <div class="file-icon-compact">
                                        @if($isImage)
                                          <img src="{{ $url }}" alt="Preview" class="file-preview-compact">
                                        @else
                                          <i class="bi bi-file-earmark"></i>
                                        @endif
                                      </div>
                                      <div class="file-info-compact">
                                        <div class="file-name-compact">{{ $fileName }}</div>
                                        <div class="file-type-compact">{{ strtoupper($ext) }}</div>
                                      </div>
                                    </a>
                                  </div>
                                @endforeach
                              </div>
                            @else
                              <span class="no-files">Tidak ada file</span>
                            @endif
                          </div>
                        </div>
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @else
          <div class="empty-state">
            <div class="empty-state-icon">
              <i class="bi bi-building-x"></i>
            </div>
            <div class="empty-state-content">
              <h5>Belum Ada Data Bank</h5>
              <p>Data bank belum diinput untuk registrasi ini</p>
            </div>
          </div>
        @endif
        </div>
        
        <!-- Tab Content: SLIK -->
        <div id="tab-slik" class="tab-content">
        @if($slikList->count() > 0)
          <div class="data-list-container">
            <div class="data-list-header">
              <div class="data-list-title">
                <i class="bi bi-file-text me-2"></i>
                <span>Data SLIK</span>
                <span class="data-count-badge">{{ $slikList->count() }}</span>
              </div>
            </div>
            
            <div class="data-list-body">
              @foreach($slikList as $index => $slik)
                <div class="data-card" data-slik-id="{{ $slik->id_slik }}">
                  <div class="data-card-header" onclick="toggleSlikDetail({{ $slik->id_slik }})">
                    <div class="data-card-info">
                      <div class="data-card-title">
                        <i class="bi bi-shield-check me-2"></i>
                        <span>{{ $slik->nama ?? 'SLIK' }}</span>
                      </div>
                      <div class="data-card-meta">
                        <div class="meta-item">
                          <i class="bi bi-calendar3 me-1"></i>
                          <span>{{ \Carbon\Carbon::parse($slik->created_at)->translatedFormat('d M Y H:i') }}</span>
                        </div>
                        <div class="meta-separator">•</div>
                        <div class="meta-item">
                          <i class="bi bi-person-circle me-1"></i>
                          <span>{{ $slik->input_by ?? 'System' }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="data-card-actions">
                      <div class="toggle-indicator">
                        <i class="bi bi-chevron-down"></i>
                      </div>
                    </div>
                  </div>
                  
                  <div class="data-card-content" id="slik-detail-{{ $slik->id_slik }}" style="display: none;">
                    <div class="data-detail-grid">
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-hash"></i>
                          <span>Nomor SLIK</span>
                        </div>
                        <div class="detail-value highlight">{{ $slik->nomor_slik ?? 'SLIK-001/2025' }}</div>
                      </div>
                      
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-calendar3"></i>
                          <span>Tanggal</span>
                        </div>
                        <div class="detail-value">{{ \Carbon\Carbon::parse($slik->tanggal ?? $slik->created_at)->format('d/m/Y') }}</div>
                      </div>
                      
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-card-text"></i>
                          <span>No Identitas</span>
                        </div>
                        <div class="detail-value">{{ $slik->no_identitas ?? '-' }}</div>
                      </div>
                      
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-shield-check"></i>
                          <span>Status</span>
                        </div>
                        <div class="detail-value">
                          <span class="badge bg-info">{{ $slik->status_slik ?? 'Dalam Proses' }}</span>
                        </div>
                      </div>
                      
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-journal-text"></i>
                          <span>Nomor Register</span>
                        </div>
                        <div class="detail-value">{{ $slik->nomor_register ?? '003/REG/2025' }}</div>
                      </div>
                      
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-person"></i>
                          <span>Nama Nasabah</span>
                        </div>
                        <div class="detail-value">{{ $slik->nama ?? '-' }}</div>
                      </div>
                      
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-link-45deg"></i>
                          <span>Keterkaitan</span>
                        </div>
                        <div class="detail-value">{{ $slik->keterkaitan ?? 'Pengurus' }}</div>
                      </div>
                      
                      <div class="detail-item">
                        <div class="detail-label">
                          <i class="bi bi-clock"></i>
                          <span>Dibuat</span>
                        </div>
                        <div class="detail-value">{{ \Carbon\Carbon::parse($slik->created_at)->format('d/m/Y H:i') }}</div>
                      </div>
                      
                      @if($slik->hasil)
                        <div class="detail-item full-width">
                          <div class="detail-label">
                            <i class="bi bi-paperclip"></i>
                            <span>File Hasil</span>
                          </div>
                          <div class="detail-value">
                            @php
                              $ext = strtolower(pathinfo($slik->hasil, PATHINFO_EXTENSION));
                              $isImage = in_array($ext, ['jpg','jpeg','png']);
                              $encryptedUrl = \App\Helpers\UrlEncryptionHelper::encryptFileUrl($slik->id_slik, 0, 'slik_hasil', $slik->nama);
                              $url = route('file.encrypted', $encryptedUrl);
                              $fileName = basename($slik->hasil);
                              if (strlen($fileName) > 30) {
                                $fileName = substr($fileName, 0, 27) . '...';
                              }
                            @endphp
                            <div class="file-attachments-compact">
                              <div class="file-item-compact">
                                <a href="{{ $url }}" target="_blank" class="file-link-compact">
                                  <div class="file-icon-compact">
                                    @if($isImage)
                                      <img src="{{ $url }}" alt="Preview" class="file-preview-compact">
                                    @else
                                      <i class="bi bi-file-earmark"></i>
                                    @endif
                                  </div>
                                  <div class="file-info-compact">
                                    <div class="file-name-compact">{{ $fileName }}</div>
                                    <div class="file-type-compact">{{ strtoupper($ext) }}</div>
                                  </div>
                                </a>
                              </div>
                            </div>
                          </div>
                        </div>
                      @endif
                    </div>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @else
          <div class="empty-state">
            <div class="empty-state-icon">
              <i class="bi bi-file-text-x"></i>
            </div>
            <div class="empty-state-content">
              <h5>Belum Ada Data SLIK</h5>
              <p>Data SLIK belum diinput untuk registrasi ini</p>
            </div>
          </div>
        @endif
        </div>
    </div>
  </div>
</div>

<!-- Overlay untuk sidebar (tidak menutup sidebar saat diklik) -->
<div id="sidebarOverlay" class="sidebar-overlay"></div>

<!-- CSS styles telah dipindahkan ke public/assets/css/komite_style.css -->

<!-- JavaScript sudah dipindahkan ke file eksternal komite-modal.js -->
@push('script')
<script src="{{ asset('assets/js/komite-modal-summernote.js') }}"></script>
<script src="{{ asset('assets/js/komite-summernote-fix.js') }}"></script>
<script src="{{ asset('assets/js/summernote-debug-simple.js') }}"></script>
<script src="{{ asset('assets/js/summernote-initializer.js') }}"></script>
@endpush
@push('scripts')
<script>
    // Pastikan semua tombol tambah pada card memorandum mengisi tipe_memorandum sebelum modal tampil
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-bs-target="#modalTambahKomite"]').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                let tipe = 'rekomendasi';
                if (btn.innerText.toLowerCase().includes('opini')) tipe = 'opini';
                else if (btn.innerText.toLowerCase().includes('keputusan')) tipe = 'keputusan';
                else if (btn.innerText.toLowerCase().includes('mengetahui')) tipe = 'mengetahui';
                document.getElementById('tipe_memorandum').value = tipe;
                
                // Set nilai keputusan otomatis untuk role yang tidak bisa memilih keputusan
                const keputusanSelect = document.getElementById('keputusan');
                const keputusanGroup = document.getElementById('keputusan_group');
                const keputusanHidden = document.querySelector('input[name="keputusan"][type="hidden"]');

                // Tampilkan field keputusan hanya jika tipe = keputusan (Direktur Utama)
                if (keputusanGroup) {
                    if (tipe === 'keputusan') {
                        keputusanGroup.style.display = '';
                        if (keputusanSelect) {
                            keputusanSelect.setAttribute('required', 'required');
                        }
                    } else {
                        keputusanGroup.style.display = 'none';
                        if (keputusanSelect) {
                            keputusanSelect.removeAttribute('required');
                            keputusanSelect.value = '';
                        }
                    }
                }

                if (!keputusanSelect && keputusanHidden) {
                    keputusanHidden.value = '';
                }
            });
        });
    });
</script>
@endpush