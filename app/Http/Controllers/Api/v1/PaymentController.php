<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\PayTrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function payTrToken(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'basket' => 'required|array',
            'totalAmount'  => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $basket = $request->input('basket');
        $amount = $request->input('totalAmount');
        $userEmail = $request->input('email');

        $payTrService = new PaytrService();
        $token = $payTrService->getApiToken($userEmail,$amount,$basket);

        return response()->json(['token' => $token]);
    }
}
