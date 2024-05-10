<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Alamat;
use App\Models\Pegawai;
use App\Models\DetailTransaksi;
use App\Models\User;
use App\Models\Transaksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function showAll()
    {
        $users = User::where('id_role', 4)->get();

        $user = User::where('id_role',4)->get();

        return response()->json($users);
    }

    public function getProfile()
    {
        $id = Auth::user()->id_user;

        $user = User::select('users.*', 'point.*', 'wallet.*')
            ->join('point', 'users.id_user', '=', 'point.id_user')
            ->join('wallet', 'users.id_user', '=', 'wallet.id_user')
            ->where('users.id_user', '=', 57)
            ->first();

        if (!$user) {
            return response(['message' => 'Users not found'], 404);
        }

        $user->alamat = Alamat::select()->where('id_user', '=', 57)->get();

        $user->pesanan = Transaksi::select()->where('id_user', '=', $user['id_user'])->get();

        foreach ($user->pesanan as $transaksi) {
            $transaksi->produk = DetailTransaksi::select('detail_transaksi.*', 'produk.*')
                ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
                ->where('id_transaksi', '=', $transaksi['id_transaksi'])
                ->get();
        }



        return response([
            'message' => 'success get data user',
            'data' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {

        $data = $request->all();
        $user = User::find(auth()->id());

        if ($user == null) {
            return response([
                'message' => 'User Not Found',
            ], 400);
        }

        $validate = Validator::make($data, [
            'email' => 'nullable|email:rfc,dns|unique:users,email',
        ]);


        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }


        if ($request->hasFile('foto_profile')) {
            $uploadFolder = 'images';
            $image = $request->file('foto_profile');
            
            if ($user->foto_profile) {
                Storage::disk('public')->delete($user->foto_profile);
            }
            // Generate nama file acak dengan 12 karakter
            $randomFileName = Str::random(12);
            // Dapatkan ekstensi file asli
            $extension = $image->getClientOriginalExtension();
            // Gabungkan nama file acak dengan ekstensi
            $fileNameToStore = $randomFileName . '.' . $extension;
            // Simpan gambar
            $image_uploaded_path = $image->storeAs($uploadFolder, $fileNameToStore, 'public');
            // Mendapatkan nama file yang diunggah
            $uploadedImageResponse = basename($image_uploaded_path);
            // Set data foto profile baru
            $data['foto_profile'] = 'images/' . $uploadedImageResponse;

         
        }

        $data2 = json_encode($request->all());

        $user->update($data);

        return response([
            'message' => 'Update Profile Success',
            'data' => $user,
            'data2' => $data2
        ], 200);
    }

    public function findByIdUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response(['message' => 'Users not found'], 404);
        }

        return response([
            'message' => 'success get data user',
            'data' => $user
        ]);
    }

    public function test()
    {
        $user = DB::table('password_reset_tokens')->select('token')->where('email', 'tinartinar720@gmail.com')->value('column');

        return response([
            $user
        ]);
    }
}
