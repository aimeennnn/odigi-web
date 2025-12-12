<div class="modal fade" id="modalTambahData" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%);">
                <h5 class="modal-title text-white">Tambah User Admin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('users.store') }}" method="POST" id="formTambahUser">
                @csrf
                <div class="modal-body" style="padding: 1.25rem;">
                    @if ($errors->any())
                        <div class="alert alert-danger mb-3" role="alert">
                            <strong>Gagal menyimpan data!</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}" required style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                                <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                            </div>
                            @error('username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                                <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                            </div>
                            @error('nama')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">NIK <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="nik" id="nikInput" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik') }}" maxlength="16" required oninput="validateNIK(this)">
                                <span class="input-group-text text-danger bg-white border-start-0 required-warning" id="nikIcon"><i class="bi bi-exclamation-circle"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem; color: #6c757d;">Masukkan 16 digit angka</div>
                            @error('nik')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="email" name="email" id="emailInput" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required oninput="validateEmail(this)">
                                <span class="input-group-text text-danger bg-white border-start-0 required-warning" id="emailIcon"><i class="bi bi-exclamation-circle"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem; color: #6c757d;">Masukkan alamat email yang valid</div>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">No. HP <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="no_hp" id="noHpInput" class="form-control @error('no_hp') is-invalid @enderror" value="{{ old('no_hp', '+62') }}" placeholder="+6281xxxxxxxxx" required maxlength="15"
    oninput="if(this.value.length < 3) this.value = '+62'; if(!this.value.startsWith('+62')) this.value = '+62'+this.value.replace(/^\+?62?/, ''); let digits = this.value.slice(3).replace(/\D/g,''); if(digits.length > 12) digits = digits.slice(0,12); this.value = '+62' + digits;"
/>
                                <span class="input-group-text text-danger bg-white border-start-0 required-warning" id="noHpIcon"><i class="bi bi-exclamation-circle"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem; color: #6c757d;">Format wajib: +62 diikuti 10-12 digit angka (0812xxx... → +62812xxxxxxxx). Maksimal 12 digit setelah +62.</div>
                            @error('no_hp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Status <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="">Pilih Status...</option>
                                    @foreach(\App\Models\User::statusList() as $key => $label)
                                        <option value="{{ $key }}" {{ old('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                            </div>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Jabatan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan') }}" required style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase()">
                                <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                            </div>
                            @error('jabatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Level <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="level" class="form-select @error('level') is-invalid @enderror" required>
                                    <option value="">Pilih Level...</option>
                                    @foreach(\App\Models\User::levelList() as $key => $label)
                                        <option value="{{ $key }}" {{ old('level') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                            </div>
                            @error('level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="6" required>
                                <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Konfirmasi Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" minlength="6" required>
                                <span class="input-group-text text-danger bg-white border-start-0 required-warning"><i class="bi bi-exclamation-circle"></i></span>
                            </div>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end gap-2">
                    <button type="submit" class="btn" style="background:#1dd1a1; color:white; font-weight:600;"><i class="bi bi-save me-1"></i>Simpan Data</button>
                    <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript telah dipindahkan ke public/assets/js/manajemen_pengguna-modal.js -->
<!-- Script akan dimuat di halaman parent untuk optimasi caching -->
