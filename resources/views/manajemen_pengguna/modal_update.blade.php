<div class="modal fade" id="modalEditUser_{{ $user->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(90deg, #1dd1a1 0%, #49bbca 100%);">
                <h5 class="modal-title text-white">Update User Admin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('users.update', $user->id) }}" method="POST" id="formEditUser_{{ $user->id }}">
                @csrf
                @method('PUT')
                <div class="modal-body" style="padding: 1.25rem;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Username <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="username" id="usernameInput_{{ $user->id }}" class="form-control bg-light" value="{{ $user->username }}" readonly required style="text-transform: uppercase;">
                                <span class="input-group-text text-success bg-white border-start-0 required-warning" id="usernameIcon_{{ $user->id }}"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem; color: #6c757d;">Username tidak dapat diubah</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Nama Lengkap <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="nama" id="namaInput_{{ $user->id }}" class="form-control" value="{{ $user->nama }}" required style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase(); updateFieldIcon_{{ $user->id }}(this);">
                                <span class="input-group-text text-success bg-white border-start-0 required-warning" id="namaIcon_{{ $user->id }}"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">NIK <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="nik" id="nikInput_{{ $user->id }}" class="form-control" value="{{ $user->nik }}" maxlength="16" required oninput="validateNIK_{{ $user->id }}(this);">
                                <span class="input-group-text text-success bg-white border-start-0 required-warning" id="nikIcon_{{ $user->id }}"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem; color: #6c757d;">Masukkan 16 digit angka</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="email" name="email" id="emailInput_{{ $user->id }}" class="form-control" value="{{ $user->email }}" required oninput="validateEmail_{{ $user->id }}(this);">
                                <span class="input-group-text text-success bg-white border-start-0 required-warning" id="emailIcon_{{ $user->id }}"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem; color: #6c757d;">Masukkan alamat email yang valid</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">No. HP <span class="text-danger">*</span></label>
                            <div class="input-group">
                                @php
                                    $hpVal = preg_replace('/[^0-9]/','', (string)($user->no_hp ?? ''));
                                    if ($hpVal !== '') {
                                        if (str_starts_with($hpVal, '0')) { $hpVal = '62' . substr($hpVal, 1); }
                                        elseif (str_starts_with($hpVal, '8')) { $hpVal = '62' . $hpVal; }
                                        elseif (!str_starts_with($hpVal, '62')) { $hpVal = '62' . $hpVal; }
                                        $hpVal = '+'.$hpVal;
                                    } else { $hpVal = '+62'; }
                                @endphp
                                <input type="text" name="no_hp" id="noHpInput_{{ $user->id }}" class="form-control" value="{{ $hpVal }}" maxlength="15" required pattern="^\+62[0-9]{10,12}$"
                                    oninput="
                                        if(this.value.length < 3) this.value = '+62';
                                        if(!this.value.startsWith('+62')) this.value = '+62'+this.value.replace(/^\+?62?/, '');
                                        let digits = this.value.slice(3).replace(/\D/g,'');
                                        if(digits.length > 12) digits = digits.slice(0,12);
                                        this.value = '+62' + digits;
                                        // toggle icon
                                        try {
                                            const ok = /^\+62[0-9]{10,12}$/.test(this.value);
                                            const iconWrap = document.getElementById('noHpIcon_{{ $user->id }}');
                                            if (iconWrap) {
                                                iconWrap.classList.toggle('text-success', ok);
                                                iconWrap.classList.toggle('text-danger', !ok);
                                                iconWrap.innerHTML = ok ? '<i class=\'bi bi-check-circle-fill\'></i>' : '<i class=\'bi bi-exclamation-circle\'></i>';
                                            }
                                        } catch(e) {}
                                    "
                                >
                                <span class="input-group-text text-success bg-white border-start-0 required-warning" id="noHpIcon_{{ $user->id }}"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem; color: #6c757d;">Format wajib: +62 diikuti 10-12 digit angka</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Status <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="status" id="statusInput_{{ $user->id }}" class="form-select" required onchange="updateFieldIcon_{{ $user->id }}(this);">
                                    <option value="">Pilih Status...</option>
                                    @foreach(\App\Models\User::statusList() as $key => $label)
                                        <option value="{{ $key }}" {{ $user->status == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning" id="statusIcon_{{ $user->id }}"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Jabatan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="jabatan" id="jabatanInput_{{ $user->id }}" class="form-control" value="{{ $user->jabatan }}" required style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase(); updateFieldIcon_{{ $user->id }}(this);">
                                <span class="input-group-text text-success bg-white border-start-0 required-warning" id="jabatanIcon_{{ $user->id }}"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Level <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="level" id="levelInput_{{ $user->id }}" class="form-select" required onchange="updateFieldIcon_{{ $user->id }}(this);">
                                    <option value="">Pilih Level...</option>
                                    @foreach(\App\Models\User::levelList() as $key => $label)
                                        <option value="{{ $key }}" {{ $user->level == $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <span class="input-group-text text-success bg-white border-start-0 required-warning" id="levelIcon_{{ $user->id }}"><i class="bi bi-check-circle-fill"></i></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Password Baru <span class="text-muted">(Opsional)</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordInput_{{ $user->id }}" class="form-control" minlength="6" placeholder="Isi jika ingin ganti password" onchange="validatePassword_{{ $user->id }}()">
                                <span class="input-group-text text-muted bg-white border-start-0"><i class="bi bi-lock"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem; color: #6c757d;">Minimal 6 karakter</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Konfirmasi Password <span class="text-muted">(Opsional)</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="passwordConfirmationInput_{{ $user->id }}" class="form-control" minlength="6" placeholder="Ulangi password baru" onchange="validatePassword_{{ $user->id }}()">
                                <span class="input-group-text text-muted bg-white border-start-0"><i class="bi bi-lock"></i></span>
                            </div>
                            <div class="form-text" style="font-size: 0.75rem; color: #6c757d;">Harus sama dengan password baru</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end gap-2">
                    <button type="submit" class="btn" style="background:#1dd1a1; color:white; font-weight:600;"><i class="bi bi-save me-1"></i>Simpan</button>
                    <button type="button" class="btn btn-danger px-4" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i>Batal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript telah dipindahkan ke public/assets/js/manajemen_pengguna-modal_update.js -->
<!-- Script akan dimuat di halaman parent untuk optimasi caching -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const modal = document.getElementById('modalEditUser_{{ $user->id }}');
    const normalize = function(){
        const inp = document.getElementById('noHpInput_{{ $user->id }}');
        const icon = document.getElementById('noHpIcon_{{ $user->id }}');
        if(!inp) return;
        // Normalisasi ke +62 + 11-12 digit
        let v = inp.value || '';
        v = v.replace(/[^0-9+]/g,'');
        if (v.startsWith('+')) v = v; else if (v.startsWith('62')) v = '+'+v; else if (v.startsWith('0')) v = '+62'+v.substring(1); else if (v.startsWith('8')) v = '+62'+v; else v = '+62';
        const digits = v.slice(3).replace(/\D/g,'').slice(0,12);
        inp.value = '+62' + digits;
        const ok = /^\+62[0-9]{10,12}$/.test(inp.value);
        if (icon){
            icon.classList.toggle('text-success', ok);
            icon.classList.toggle('text-danger', !ok);
            icon.innerHTML = ok ? '<i class="bi bi-check-circle-fill"></i>' : '<i class="bi bi-exclamation-circle"></i>';
        }
    };
    // Run once on load (if modal is already open)
    normalize();
    if (modal){ modal.addEventListener('shown.bs.modal', normalize); }
});
</script>
@endpush
