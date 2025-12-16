<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:user,username',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nama' => $request->username, // Set nama same as username for now
            'nik' => '0000000000000000', // Default NIK
            'no_hp' => '000000000000', // Default phone
            'status' => 'active',
        ]);

        // Jangan auto-login, langsung redirect ke login
        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        // Debug: Log attempt
        Log::info('Login attempt', [
            'username' => $request->username,
            'credentials' => $credentials,
            'session_id' => session()->getId()
        ]);

        // Cek status user sebelum login
        $user = \App\Models\User::where('username', $request->username)->first();
        if ($user && $user->status !== 'active') {
            Log::warning('Login attempt blocked - user inactive', [
                'username' => $request->username,
                'status' => $user->status,
                'session_id' => session()->getId()
            ]);
            
            return back()->withErrors([
                'username' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
            ])->withInput($request->only('username'));
        }

        // Verifikasi manual kredensial. Jika benar, lanjut ke OTP (jangan login dulu)
        if (
            $user && Hash::check($request->password, $user->password)
        ) {
            // Login langsung untuk SUPERADMIN
            if (strtoupper($user->username) === 'SUPERADMIN') {
                Auth::login($user, $request->filled('remember'));
                \App\Models\User::where('id', $user->id)->update(['online' => true]);
                $request->session()->regenerate();
                return redirect()->route('dashboard.index')->with('success', 'Login berhasil!');
            }

            // // BYPASS OTP: Login langsung untuk non-SUPERADMIN
            // Auth::login($user, $request->filled('remember'));
            // \App\Models\User::where('id', $user->id)->update(['online' => true]);
            // $request->session()->regenerate();
            // return redirect()->route('dashboard.index')->with('success', 'Login berhasil!');

            $otpCode = (string) random_int(100000, 999999);
            $expiresAt = now()->addMinutes(2);
            session([
                'otp_user_id' => $user->id,
                'otp_code' => $otpCode,
                'otp_expires_at' => $expiresAt,
                'otp_remember' => $request->filled('remember'),
            ]);
            try {
                $this->sendOtpToUser($user, $otpCode);
            } catch (\Throwable $e) {
                Log::error('Gagal mengirim OTP saat login', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'error' => $e->getMessage(),
                ]);
            }
            return redirect()->route('otp.form')->with('success', 'Kode OTP telah dikirim. Berlaku 2 menit.');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput($request->only('username'));
    }

    public function showOtpForm(Request $request)
    {
        Log::info('Show OTP form called', [
            'session_id' => session()->getId(),
            'otp_user_id' => session('otp_user_id'),
            'otp_expires_at' => session('otp_expires_at')
        ]);

        if (!session('otp_user_id')) {
            Log::warning('No OTP user ID in session, redirecting to login');
            return redirect()->route('login');
        }
        
        $user = User::find(session('otp_user_id'));
        if (!$user) {
            Log::error('User not found for OTP session', [
                'user_id' => session('otp_user_id')
            ]);
            return redirect()->route('login');
        }

        $expiresAt = session('otp_expires_at');
        // Fallback: jika tidak ada expiry di session, set default 2 menit ke depan
        if (!$expiresAt) {
            $expiresAt = now()->addMinutes(2);
            session()->put('otp_expires_at', $expiresAt);
            session()->save();
        }
        Log::info('OTP form data', [
            'user_id' => $user->id,
            'username' => $user->username,
            'masked_phone' => $this->maskPhone($user->no_hp),
            'expires_at' => $expiresAt ? $expiresAt->toIso8601String() : null
        ]);

        return view('otp', [
            'masked_phone' => $this->maskPhone($user->no_hp),
            'expires_at' => $expiresAt ? $expiresAt->toIso8601String() : null,
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp_code' => 'required|string']);
        $userId = session('otp_user_id');
        $code = session('otp_code');
        $expires = session('otp_expires_at');

        if (!$userId || !$code || !$expires) {
            return redirect()->route('login')->withErrors(['username' => 'Sesi OTP tidak ditemukan. Silakan login kembali.']);
        }

        if (now()->greaterThan($expires)) {
            return back()->withErrors(['otp_code' => 'Kode OTP sudah kadaluarsa. Silakan kirim ulang.']);
        }

        if (trim($request->otp_code) !== (string) $code) {
            return back()->withErrors(['otp_code' => 'Kode OTP tidak sesuai.']);
        }

        // Login user sekarang
        $user = User::findOrFail($userId);
        Auth::login($user, (bool) session('otp_remember'));
        \App\Models\User::where('id', $user->id)->update(['online' => true]);
        $request->session()->regenerate();

        // Bersihkan sesi OTP
        session()->forget(['otp_user_id','otp_code','otp_expires_at','otp_remember']);

        return redirect()->route('dashboard.index')->with('success', 'Login berhasil!');
    }

    public function resendOtp(Request $request)
    {
        try {
            Log::info('Resend OTP attempt', [
                'session_id' => session()->getId(),
                'otp_user_id' => session('otp_user_id'),
                'request_data' => $request->all()
            ]);

            $userId = session('otp_user_id');
            if (!$userId) {
                Log::warning('Resend OTP failed - no user ID in session', [
                    'session_id' => session()->getId()
                ]);
                return redirect()->route('login')->withErrors(['username' => 'Sesi OTP tidak ditemukan. Silakan login kembali.']);
            }

            $user = User::find($userId);
            if (!$user) {
                Log::error('Resend OTP failed - user not found', [
                    'user_id' => $userId,
                    'session_id' => session()->getId()
                ]);
                return redirect()->route('login')->withErrors(['username' => 'User tidak ditemukan. Silakan login kembali.']);
            }

            $otpCode = (string) random_int(100000, 999999);
            $expiresAt = now()->addMinutes(2);
            
            // Update session dengan OTP baru - pastikan session tetap konsisten
            session()->put('otp_code', $otpCode);
            session()->put('otp_expires_at', $expiresAt);
            session()->save(); // Force save session

            Log::info('Generating new OTP', [
                'user_id' => $userId,
                'username' => $user->username,
                'otp_code' => $otpCode,
                'expires_at' => $expiresAt->toIso8601String()
            ]);

            // Kirim OTP ke user
            try {
                $this->sendOtpToUser($user, $otpCode);
                
                Log::info('Resend OTP successful', [
                    'user_id' => $userId,
                    'username' => $user->username
                ]);

                return redirect()->route('otp.form')->with('success', 'OTP baru telah dikirim.');
                
            } catch (\Exception $sendException) {
                Log::error('Failed to send OTP during resend', [
                    'user_id' => $userId,
                    'username' => $user->username,
                    'error' => $sendException->getMessage()
                ]);
                
                return redirect()->route('otp.form')->withErrors(['otp_code' => 'Gagal mengirim OTP. Silakan coba lagi atau hubungi administrator.']);
            }
            
        } catch (\Exception $e) {
            Log::error('Resend OTP failed with exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'session_id' => session()->getId()
            ]);
            
            return redirect()->route('otp.form')->withErrors(['otp_code' => 'Terjadi kesalahan saat mengirim ulang OTP. Silakan coba lagi.']);
        }
    }

    private function sendOtpToUser(User $user, string $otpCode): void
    {
        try {
            $to = $this->normalizePhone($user->no_hp);
            $message = 'Kode OTP Anda: ' . $otpCode . ' (berlaku 2 menit).';

            Log::info('Attempting to send OTP', [
                'user_id' => $user->id,
                'username' => $user->username,
                'to' => $to,
                'message_length' => strlen($message)
            ]);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://owa.gusaha.id/api/create-message',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => [
                    'appkey' => '60b2a927-61a1-4866-8294-9404256a8b4e',
                    'authkey' => 'TYpLzhxZV1SOn3Fko3PXgvGK0W6eu947Us71Z1srBRmOnZscZV',
                    'to' => $to,
                    'message' => $message,
                    'sandbox' => 'false',
                ],
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
            
            $resp = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                Log::error('CURL error when sending OTP', [
                    'error' => $curlError,
                    'user_id' => $user->id,
                    'to' => $to
                ]);
                throw new \Exception('CURL Error: ' . $curlError);
            }

            if ($httpCode !== 200) {
                Log::error('HTTP error when sending OTP', [
                    'http_code' => $httpCode,
                    'response' => $resp,
                    'user_id' => $user->id,
                    'to' => $to
                ]);
                throw new \Exception('HTTP Error: ' . $httpCode);
            }

            Log::info('OTP sent successfully', [
                'to' => $to, 
                'response' => $resp,
                'http_code' => $httpCode,
                'user_id' => $user->id
            ]);
            
        } catch (\Throwable $e) {
            Log::error('Failed sending OTP', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'username' => $user->username,
                'to' => $to ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw untuk ditangani oleh caller
        }
    }

    private function normalizePhone(?string $hp): string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $hp);
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        } elseif (!str_starts_with($digits, '62')) {
            $digits = '62' . $digits;
        }
        return $digits;
    }

    private function maskPhone(?string $hp): string
    {
        $digits = $this->normalizePhone($hp);
        if (strlen($digits) <= 6) return $digits;
        return substr($digits, 0, 4) . str_repeat('*', max(0, strlen($digits) - 6)) . substr($digits, -2);
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            \App\Models\User::where('id', $user->id)->update(['online' => false]);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }
}