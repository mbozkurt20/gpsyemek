<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Mail\VerifyCodeMail;
use App\Models\User;
use App\Models\Verification;
use App\Services\NetGsmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VerificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['showForm','sendOtp','verifyOtp']]);
    }

    public function showForm() {
        return view('auth.verify-section');
    }

    // 1️⃣ OTP gönderme
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:email,phone',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = $request->type == 'email' ? User::where('email',$request->value)->first()
            : User::where('phone',$request->value)->first();

        if ($user){
            session([
                'verified_type' => $request->type,
                'verified_value' => $request->value,
            ]);

            return redirect()->route('login')->with([
                'type' => $request->type,
                'value' => $request->value
            ]);
        }

        $otp = rand(100000, 999999);
        if (Verification::where('otp', $otp)->exists()) {
            $otp = rand(100000, 999999);
        }

        Verification::create([
            'type' => $request->type,
            'value' => $request->value,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        if ($request->type === 'email') {
            Mail::raw("Doğrulama kodun: {$otp}", function () use ($request,$otp) {
                Mail::to($request->value)->send(new VerifyCodeMail($otp));
            });
        } else {
            $netgsm = new NetGsmService();
            $message =
                 "GpsYemek hesabınız için doğrulama kodunuz: {$otp}. "
                . "Bu kod 5 dakika boyunca geçerlidir. Güvenliğiniz için lütfen kodu kimseyle paylaşmayınız.\n\n"
                . "İyi günler dileriz,\n"
                . "GpsYemek";
            $netgsm->sendSms($request->value, $message);
            Log::info("SMS OTP gönderildi: {$otp} - {$request->value}");
        }


        return view('auth.verify-section',
        [
            'type' => $request->type,
            'value' => $request->value
        ]);
    }

    // 2️⃣ OTP doğrulama
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'type' => 'required|in:email,phone',
            'value' => 'required',
            'otp' => 'required',
        ]);

        $verification = Verification::where('type', $request->type)
            ->where('value', $request->value)
            ->latest()
            ->first();

        if (!$verification) {
            return response()->json(['error' => 'Doğrulama isteği bulunamadı.'], 404);
        }

        if ($verification->expires_at->isPast()) {
            return response()->json(['error' => 'Üzgünüz,Doğrulama kodunuzun süresi dolmuş.'], 401);
        }

        if ($verification->otp !== $request->otp) {
            return response()->json(['error' => 'Doğrulama kodunuz hatalı, kontrol edip tekrar deneyiniz.'], 401);
        }

        $verification->update(['verified' => true]);

        // Kullanıcının kayıt sayfasına geçebilmesi için session tut
        session([
            'verified_type' => $request->type,
            'verified_value' => $request->value,
        ]);

        return redirect()->route('register')->with('message', 'Başarıyla Doğrulandı.');
    }

    public function sendOtpApi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:email,phone',
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $otp = rand(100000, 999999);
        if (Verification::where('otp', $otp)->exists()) {
            $otp = rand(100000, 999999);
        }

        Verification::create([
            'type' => $request->type,
            'value' => $request->value,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(5),
        ]);

        if ($request->type === 'email') {
            Mail::raw("Doğrulama kodun: {$otp}", function () use ($request,$otp) {
                Mail::to($request->value)->send(new VerifyCodeMail($otp));
            });
        } else {
            $netgsm = new NetGsmService();
            $message =
                "GpsYemek hesabınız için doğrulama kodunuz: {$otp}. "
                . "Bu kod 5 dakika boyunca geçerlidir. Güvenliğiniz için lütfen kodu kimseyle paylaşmayınız.\n\n"
                . "İyi günler dileriz,\n"
                . "GpsYemek";
            $netgsm->sendSms($request->value, $message);
            Log::info("SMS OTP gönderildi: {$otp} - {$request->value}");
        }


        return response()->json(['success' => 'Doğrulama kodu başarıyla gönderildi.', 'data' => [$request->type => $request->value]], 201);
    }
    public function verifyOtpApi(Request $request)
    {
        $request->validate([
            'type' => 'required|in:email,phone',
            'value' => 'required',
            'otp' => 'required',
        ]);

        $verification = Verification::where('type', $request->type)
            ->where('value', $request->value)
            ->latest()
            ->first();

        if (!$verification) {
            return response()->json(['status' => 404,'message' => 'Doğrulama isteği bulunamadı.'], 404);
        }

        if ($verification->expires_at->isPast()) {
            return response()->json(['status' => 401,'message' => 'Üzgünüz,Doğrulama kodunuzun süresi dolmuş.'], 401);
        }

        if ($verification->otp !== $request->otp) {
            return response()->json(['status' => 401,'message' => 'Doğrulama kodunuz hatalı, kontrol edip tekrar deneyiniz.'], 401);
        }

        $verification->update(['verified' => true]);

        $authUser = auth('api')->user();
        $authUser->update(['phone_verify' => true]);

        return response()->json(['success' => 'Doğrulama kodunuz başarıyla doğrulandı.'], 201);
    }
}
