<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\DeliveryBoyAccount;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    // -------------------------------
    // Validator
    // -------------------------------
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'roles'                   => ['required', 'numeric'],
            'g-recaptcha-response'    => ['required'],
            'is_membership_conditions'=> ['required'],
            'is_illumination_text'    => ['required'],
            'is_electronic_message'   => ['nullable'],
            'first_name'              => ['required', 'string', 'max:255'],
            'last_name'               => ['required', 'string', 'max:255'],
            'phone'                   => ['required'],
            'address'                 => ['required', 'string', 'max:255'],
            'register_email'          => ['required', 'string', 'email', 'max:100', Rule::unique('users', 'email')],
            'username'                => request('username')
                ? ['required', 'string', 'max:60', Rule::unique('users', 'username')]
                : ['nullable'],
            'password'                => ['required', 'string', 'min:6', 'confirmed'],
            'countrycode'             => ['required', 'numeric'],
            'countrycodename'         => ['required', 'string', 'max:255'],
        ], [
            'register_email.required'      => 'Email alanı boş geçilemez',
            'g-recaptcha-response.required'=> 'Lütfen Captcha Doldurunuz',
        ]);
    }

    // -------------------------------
    // reCAPTCHA Doğrulama
    // -------------------------------
    protected function verifyRecaptcha(Request $request)
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => setting('recaptcha_secret_key'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        return $response->json()['success'] ?? false;
    }

    // -------------------------------
    // Register metodu
    // -------------------------------
    public function register(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        if (!$this->verifyRecaptcha($request)) {
            return redirect()->back()
                ->withErrors(['g-recaptcha-response' => 'Captcha doğrulaması başarısız!'])
                ->withInput();
        }

        $user = $this->create($request->all());
        $this->guard()->login($user);

        return redirect($this->redirectPath());
    }

    // -------------------------------
    // Kullanıcı oluşturma
    // -------------------------------
    protected function create(array $data)
    {
        $user = User::create([
            'first_name'               => $data['first_name'],
            'last_name'                => $data['last_name'],
            'username'                 => $data['username'] ?? $this->username($data['register_email']),
            'phone'                    => $data['phone'],
            'address'                  => $data['address'],
            'email'                    => $data['register_email'],
            'password'                 => Hash::make($data['password']),
            'country_code'             => $data['countrycode'],
            'country_code_name'        => $data['countrycodename'],
            'is_illumination_text'     => $data['is_illumination_text'],
            'is_electronic_message'    => $data['is_electronic_message'] ?? 0,
            'is_membership_conditions' => $data['is_membership_conditions'],
        ]);

        $role = Role::find($data['roles']);
        if (!blank($user) && !blank($role)) {
            $user->assignRole($role->name);
        }

        if (!blank($role) && ($role->id == 4)) {
            $deliveryBoyAccount                  = new DeliveryBoyAccount();
            $deliveryBoyAccount->user_id         = $user->id;
            $deliveryBoyAccount->delivery_charge = 0;
            $deliveryBoyAccount->balance         = 0;
            $deliveryBoyAccount->save();
        }

        return $user;
    }

    private function username($email)
    {
        $emails = explode('@', $email);
        return $emails[0] . mt_rand();
    }
}



