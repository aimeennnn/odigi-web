<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SlikController;
use App\Http\Controllers\KomiteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ImageUploadController;


Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard.index');
    }
    return redirect()->route('login');
});

Route::get('index', function() {
    return view('index');
})->name('index')->middleware('web');
Route::get('project_dashboard', function() {
    return view('project_dashboard');
})->name('project_dashboard')->middleware('web');

Route::get('crypto_dashboard', function() {
    return view('crypto_dashboard');
})->name('crypto_dashboard')->middleware('web');

Route::get('education_dashboard', function() {
    return view('education_dashboard');
})->name('education_dashboard')->middleware('web');

Route::view('api', 'api')->name('api');

Route::view('profile', 'profile')->name('profile');

Route::view('select', 'select')->name('select');
Route::view('setting', 'setting')->name('setting');

// Route login yang benar (menggantikan sign_in)
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard.index');
    }
    return view('login');
})->name('login')->middleware('web');

// Hapus endpoint pendaftaran publik

// Route SLIK yang memerlukan login dan akses menu (dilindungi middleware auth + menu authorization)
Route::middleware(['auth', \App\Http\Middleware\CheckMenuAccess::class . ':menu_slik'])->group(function () {
    // SLIK simple edit path using encrypted id via query: /slik/edit?id=<enc>
    Route::get('/slik/edit', [App\Http\Controllers\SlikController::class, 'editSimple'])->name('slik.edit.simple');
    Route::resource('slik', SlikController::class);
    Route::post('/slik/upload/{id}', [App\Http\Controllers\SlikController::class, 'uploadHasil'])->name('slik.upload');
    // Bulk delete SLIK (gracefully handle GET/POST to avoid 404 if user navigates)
    Route::match(['GET','POST','DELETE'], '/slik/bulk-destroy', [App\Http\Controllers\SlikController::class, 'bulkDestroy'])->name('slik.bulkDestroy');
});

// Route bank yang memerlukan login dan akses menu (dilindungi middleware auth + menu authorization)
Route::middleware(['auth', \App\Http\Middleware\CheckMenuAccess::class . ':menu_bank'])->group(function () {
    // Bank simple edit path using encrypted id via query: /bank/edit?id=<enc>
    Route::get('/bank/edit', [App\Http\Controllers\BankController::class, 'editSimple'])->name('bank.edit.simple');
    Route::resource('bank', App\Http\Controllers\BankController::class);
    Route::post('/bank/upload/{id}', [App\Http\Controllers\BankController::class, 'uploadHasil'])->name('bank.upload');
    Route::post('/bank/upload-temp/{id}', [App\Http\Controllers\BankController::class, 'uploadTempHasil'])->name('bank.upload.temp');
    Route::get('/bank/check-status/{id}', [App\Http\Controllers\BankController::class, 'checkStatus'])->name('bank.check_status');
});
// Route data yang memerlukan login dan akses menu (dilindungi middleware auth + menu authorization)
Route::middleware(['auth', \App\Http\Middleware\CheckMenuAccess::class . ':menu_data'])->group(function () {
    // Simple edit path using encrypted id via query: /data/edit?id=<enc>
    Route::get('/data/edit', [App\Http\Controllers\DataController::class, 'editSimple'])
        ->name('data.edit.simple');
    Route::resource('data', App\Http\Controllers\DataController::class);
    // Serve data files without relying on storage:link (DISABLED - using encrypted URLs now)
    // Route::get('/data/file/{id}/{index?}', [App\Http\Controllers\DataController::class, 'viewFile'])->name('data.file');
    // Pre-upload for create (progress bar, multiple)
    Route::post('/data/upload-temp-new', [App\Http\Controllers\DataController::class, 'uploadTempNew'])->name('data.upload.temp.new');
});
// Register simple edit path using encrypted id via query: /register/edit?id=<enc>
Route::get('/register/edit', [App\Http\Controllers\RegisterController::class, 'editSimple'])
    ->name('register.edit.simple')
    ->middleware(['auth', \App\Http\Middleware\CheckMenuAccess::class . ':menu_register']);
// Route komite yang memerlukan login dan akses menu (dilindungi middleware auth + menu authorization)
Route::middleware(['auth', \App\Http\Middleware\CheckMenuAccess::class . ':menu_komite'])->group(function () {
    // Komite edit sederhana: /komite/edit?id=<enc>
    Route::get('/komite/edit', [KomiteController::class, 'editSimple'])->name('komite.edit.simple');
    Route::post('/komite/update', [KomiteController::class, 'updateSimple'])->name('komite.update.simple');
    Route::resource('komite', KomiteController::class);
});



// Route register publik untuk pendaftaran umum (tanpa login)
Route::get('/register/public', [RegisterController::class, 'publicIndex'])->name('register.public');
Route::post('/register/public', [RegisterController::class, 'publicStore'])->name('register.public.store');

// Route register yang memerlukan login dan akses menu (dilindungi middleware auth + menu authorization)
Route::middleware(['auth', \App\Http\Middleware\CheckMenuAccess::class . ':menu_register'])->group(function () {
    Route::get('/register', [RegisterController::class, 'index'])->name('register.index');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/register/create', [RegisterController::class, 'create'])->name('register.create');
    Route::get('/register/{encrypted_id}', [RegisterController::class, 'show'])->name('register.show');
    Route::get('/register/{encrypted_id}/edit', [RegisterController::class, 'edit'])->name('register.edit');
    Route::put('/register/{encrypted_id}', [RegisterController::class, 'update'])->name('register.update');
    Route::delete('/register/{encrypted_id}', [RegisterController::class, 'destroy'])->name('register.destroy');
    Route::post('/register/{encrypted_id}/aksi-upload', [RegisterController::class, 'aksiUpload'])->name('register.aksi-upload');
});

// Ubah nama rute POST login agar tidak bentrok dengan GET /login
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt')->middleware('web');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('web');
Route::post('/register-user', [AuthController::class, 'register'])->name('register.user');

// OTP routes
Route::get('/otp', [AuthController::class, 'showOtpForm'])->name('otp.form')->middleware('web');
Route::post('/otp/verify', [AuthController::class, 'verifyOtp'])->name('otp.verify')->middleware('web');
Route::post('/otp/resend', [AuthController::class, 'resendOtp'])->name('otp.resend')->middleware('web');

// Debug route untuk testing session (hapus setelah testing)
Route::get('/debug-session', function() {
    return response()->json([
        'session_id' => session()->getId(),
        'otp_user_id' => session('otp_user_id'),
        'otp_code' => session('otp_code'),
        'otp_expires_at' => session('otp_expires_at'),
        'all_session' => session()->all()
    ]);
})->name('debug.session');

// Manajemen Pengguna - Semua route dilindungi middleware auth + menu authorization
Route::middleware(['auth', \App\Http\Middleware\CheckMenuAccess::class . ':menu_manajemen'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('protect.superadmin');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('protect.superadmin');
    Route::post('/users/{user}/settings', [UserController::class, 'updateSettings'])->name('users.settings');
});

// Route SLIK yang memerlukan login (dilindungi middleware auth)
Route::middleware(['auth'])->group(function () {
    Route::post('/slik/upload-temp/{id}', [App\Http\Controllers\SlikController::class, 'uploadTemp'])->name('slik.upload.temp');
    Route::get('/slik/get-files/{id}', [App\Http\Controllers\SlikController::class, 'getFiles'])->name('slik.get.files');
    // Serve hasil file directly without requiring storage:link (DISABLED - using encrypted URLs now)
    // Route::get('/slik/file/{id}', [App\Http\Controllers\SlikController::class, 'viewFile'])->name('slik.file');
});

// API route untuk mengambil data register
Route::get('/api/register/{id}', function($id) {
    $register = App\Models\Register::find($id);
    if ($register) {
        return response()->json([
            'success' => true,
            'register' => [
                'id_reg' => $register->id_reg,
                'nomor' => $register->nomor,
                'nama' => $register->nama,
                'no_identitas' => $register->no_identitas,
                'alamat' => $register->alamat,
                'no_hp' => $register->no_hp,
                'email' => $register->email,
                'pekerjaan' => $register->pekerjaan,
                'penghasilan' => $register->penghasilan,
                'status' => $register->status
            ]
        ]);
    }
    return response()->json(['error' => 'Register not found'], 404);
});

// API route untuk mengambil data SLIK
Route::get('/api/slik/{id}', function($id) {
    $slik = App\Models\Slik::find($id);
    if ($slik) {
        return response()->json([
            'success' => true,
            'slik' => [
                'id_slik' => $slik->id_slik,
                'id_reg' => $slik->id_reg,
                'nomor' => $slik->nomor,
                'tgl' => $slik->tgl,
                'nama' => $slik->nama,
                'no_identitas' => $slik->no_identitas,
                'keterkaitan' => $slik->keterkaitan,
                'hasil' => $slik->hasil,
                'status' => $slik->status,
                'updated_at' => $slik->updated_at
            ]
        ]);
    }
    return response()->json(['error' => 'SLIK not found'], 404);
});


// Route bank yang memerlukan login (dilindungi middleware auth)
Route::middleware(['auth'])->group(function () {
    // Pre-upload for create (progress bar, multiple)
    Route::post('/bank/upload-temp-new', [App\Http\Controllers\BankController::class, 'uploadTempNew'])->name('bank.upload.temp.new');
    // Serve bank files directly without relying on storage:link (DISABLED - using encrypted URLs now)
    // Route::get('/bank/file/{id}/{index?}', [App\Http\Controllers\BankController::class, 'viewFile'])->name('bank.file');
    // Route::get('/bank/hasil-file/{id}', [App\Http\Controllers\BankController::class, 'viewHasil'])->name('bank.hasil.file');
    
    // Encrypted file URLs
    Route::get('/file/{encrypted}', [App\Http\Controllers\FileController::class, 'viewEncryptedFile'])->name('file.encrypted');
});


// Duplikat POST /login dihapus agar tidak menimpa nama rute 'login.attempt'

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index')->middleware(['web','auth']);

// Image upload route for Summernote
Route::post('/upload-image', [ImageUploadController::class, 'upload'])->name('upload.image')->middleware('auth');
Route::get('/uploads/images/{filename}', [ImageUploadController::class, 'serveImage'])->name('image.uploaded')->where('filename', '[a-zA-Z0-9\-\.]+');

Route::middleware(['auth'])->group(function () {
    Route::get('data_tambahdata', function() {
        $regId = session('current_register_id');
        if (!$regId) return redirect()->route('register.index')->with('error','Register belum dipilih.');
        $enc = \Illuminate\Support\Facades\Crypt::encryptString($regId);
        session(['current_register_encrypted_id' => $enc]);
        return redirect()->route('data.index');
    });
});
Route::middleware(['auth'])->group(function () {
    Route::get('bank_tambahdata', function() {
        $regId = session('current_register_id');
        if (!$regId) return redirect()->route('register.index')->with('error','Register belum dipilih.');
        $enc = \Illuminate\Support\Facades\Crypt::encryptString($regId);
        session(['current_register_encrypted_id' => $enc]);
        return redirect()->route('bank.index');
    });
});
Route::middleware(['auth'])->group(function () {
    Route::get('slik_tambahdata', function() {
        $regId = session('current_register_id');
        if (!$regId) return redirect()->route('register.index')->with('error','Register belum dipilih.');
        $enc = \Illuminate\Support\Facades\Crypt::encryptString($regId);
        session(['current_register_encrypted_id' => $enc]);
        return redirect()->route('slik.index');
    });
});
Route::middleware(['auth'])->group(function () {
    Route::get('komite_tambahdata', function() {
        $regId = session('current_register_id');
        if (!$regId) return redirect()->route('register.index')->with('error','Register belum dipilih.');
        $enc = \Illuminate\Support\Facades\Crypt::encryptString($regId);
        session(['current_register_encrypted_id' => $enc]);
        return redirect()->route('komite.index');
    });
});

// Route Pintu Belakang untuk n8n
Route::post('/webhook/bank-result', [BankController::class, 'handleWebhook'])->name('webhook.bank');