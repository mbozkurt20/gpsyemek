<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordRestCode;
use App\Models\Verification;
use App\Services\NetGsmService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class PasswordResetController extends Controller
{
    public function smsForgot(Request $request)
    {
        $request->validate([
            'phone' => 'required'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'Telefon numaranıza ait hesap bulunamadı!'], 404);
        }

        $code = rand(100000, 999999);

        if (PasswordRestCode::where('phone', $request->phone)->exists()) {
            $code = rand(100000, 999999);
        }

        PasswordRestCode::updateOrCreate(
            ['phone' => $request->phone],
            [
                'code' => Hash::make($code),
                'expires_at' => Carbon::now()->addMinutes(5)
            ]
        );

        $netgsm = new NetGsmService();
        $message = "GpsYemek hesabiniza ait sifre sifirlama kodu: {$code}. 5 dakika gecerlidir. Guvenliginiz icin paylasmayiniz.";

        $netgsm->sendSms($request->phone, $message);
        Log::info("SMS OTP gönderildi: {$code} - {$request->phone}");

        return response()->json([
            'message' => 'Şifre sıfırlama kodu SMS olarak gönderildi'
        ]);
    }

    public function smsReset(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'code' => 'required',
            'password' => 'required|min:6|confirmed'
        ]);

        $reset = PasswordRestCode::where('phone', $request->phone)->first();

        if (!$reset) {
            return response()->json(['message' => 'Sıfırlama bağlantınız bulunamadı, lütfen kontrol edip tekrar deneyiniz!'], 404);
        }

        if ($reset->expires_at < now()) {
            return response()->json(['message' => 'Kodun süresi dolmuş'], 400);
        }

        if (!Hash::check($request->code, $reset->code)) {
            return response()->json(['message' => 'Kod Geçersiz'], 400);
        }

        $user = User::where('phone', $request->phone)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        $reset->delete();

        return response()->json(['status' => 200, 'message' => 'Şifreniz Başarıyla Güncellendi']);
    }


    // Şifre sıfırlama linki gönderme
    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)], 200)
            : response()->json(['message' => __($status)], 400);
    }

    // Şifre sıfırlama
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed'
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        return $status == Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)], 200)
            : response()->json(['message' => __($status)], 400);
    }
}
