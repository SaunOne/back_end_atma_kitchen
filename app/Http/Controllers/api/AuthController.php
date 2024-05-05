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
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;



class AuthController extends Controller
{

    public function register(Request $request)
    {
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

        if ($validate->fails()) {
            return response(
                ["Message" => $validate->errors()->first(), 400]
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

    public function cekVerify(){

    }

    public function login(Request $request)
    {
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
        /** @var \App\Models\User $user  **/
        $user = Auth::user();
        $result = $user->createToken('Authentication Token')->accessToken;


        return response([
            'message' => 'Authenticated',
            'data' => $user,
            'token_type' => 'Bearer',
            'access_token' => $result
        ]);
    }


    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if($status === Password::RESET_LINK_SENT){
            return response([
                "message" => "Email Link successfully sent",
                "status" => 200,
                "data" => $request,
            ]);
        } else {
            return response([
                "message" => "Email Link Failed to send",
                "status" => 400,
                "data" => $request,
            ]);
        }

        // return $status === Password::RESET_LINK_SENT
        //     ? back()->with(['status' => __($status)])
        //     : back()->withErrors(['email' => __($status)]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data,[
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),

            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if($status == Password::RESET_LINK_SENT){
            return response([
                "message" => "Reset Password Successful",
                "status" => $status,
                "data" => $request
            ]);
        } else {
            return response([
                "message" => "Reset Password Failed",
                "status" => $status,
            ]);
        }

        // return $status === Password::PASSWORD_RESET
        //     ? redirect()->route('login')->with('status', __($status))
        //     : back()->withErrors(['email' => [__($status)]]);
    }

    public function updatePassword($newPassword, $id)
    {
        $user = User::find($id);
        if($user['id_role'] === 4){
            return response([
                "message" => "Customer can't update password with this mthode",
                "status" => 405
            ]);
        }
        
        $user->password = Hash::make($newPassword);
        $user->save();

        return response([
            "message" => "Update Password Successfully",
            "status" => 202
        ]);
    }

    public function cekActive($id){

        $user = User::find($id);
        return response([
            "message" => "Cek Active Successfully",
            "Status" => $user['active']
        ]);
    }
}
