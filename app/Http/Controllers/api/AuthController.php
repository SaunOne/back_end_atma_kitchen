<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationAccount;
use App\Mail\VerifikasiEmail;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{   
    
    public function register(Request $request){
        $data = $request->all();
        
        $data['id_role'] = 4;

        $validate = Validator::make($data, [
            'username' => 'required',
            'password' => 'required|min:8',
            'id_role' => 'required',
            'nama_lengkap' =>  'required',
            'no_telp' => 'required',
            'email' => 'required|email:rfc,dns|unique:users',
            'gender' => 'required',
            'tanggal_lahir' => 'required',
        ]);

        if($validate->fails()){
            return response(
                ["Message" => $validate->errors()->first(),400]
            );
        }

        $data['password'] = bcrypt($request->password);

        $str = Str::random(100);
        $data['verify_key'] = $str;
        $data['active'] = false;
        $user = User::create($data);

        $details = [
            'username' => $request->username,
            'website' => 'Atma Kitchen',
            'datetime' => now(),
            'url' => request()->getHttpHost()  . '/api/verify/' . $str,
        ];  

        Mail::to($request->email)->send(new VerificationAccount($details));

        return response([
            'message' => 'Register Success',
            'data' => $user,
            'random' => $data['verify_key'],
            'url' => request()->getHttpHost() . '/api/verify/' . $str,
        ], 200);
        
    }

    public function verify($verify_key)
    {
        $keyCheck = User::select('verify_key')
            ->where('verify_key', $verify_key)
            ->exists();

        if ($keyCheck) {
            $user = User::where('verify_key', $verify_key)
                ->update([
                    'active' => 1,
                    'email_verified_at' => date('Y-m-d H:i:s'),
                ]);
            return ([
                'Message' => "Verifikasi berhasil. Akun anda sudah aktif.",
            ]);
        } else {
            return ([
                'message' => "Keys tidak valid.",
                'verify' => $verify_key,
                'data' => $keyCheck
            ]);
        }
    }

    public function login(Request $request){
        $loginData = $request->all();

        $validate = Validator::make($loginData, [
            'email' => 'required|email:rfc,dns',
            'password' => 'required|min:8',
        ]);
        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        if (!Auth::attempt($loginData)) {
            return response(['message' => 'Invalid email & password match'], 401);
        }
        $user = Auth::user();
        $token = $user->createToken('Authentication Token')->accessToken;

        return response([
            'message' => 'Authenticated',
            'data' => $user,
            'token_type' => 'Bearer',
            'access_token' => $token
        ]);
    }

}
