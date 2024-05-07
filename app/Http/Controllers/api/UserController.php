<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function showAll(){

        $user = User::all();

        return response()->json($user);
    }

    public function getProfile(){
        $user = Auth::user();

        if (!$user) {
            return response(['message' => 'Users not found'], 404);
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
            $user->save();
            return response(['message' => $validate->errors()->first()], 400);
        }


        if($request->hasFile('foto_profile')){
            // kalau kalian membaca ini, ketahuilah bahwa gambar tidak akan bisa diupdate karena menggunakan method PUT ;)
            // kalian bisa mengubahnya menjadi POST atau PATCH untuk mengupdate gambar
            $uploadFolder = 'users';
            $image = $request->file('foto_profile');
            $image_uploaded_path = $image->store($uploadFolder, 'public');
            $uploadedImageResponse = basename($image_uploaded_path);

            // hapus data thumbnail yang lama dari storage
            // Storage::disk('public')->delete('users/'.$user->image_profile);

            // set thumbnail yang baru
            
            $data['foto_profile'] = $uploadedImageResponse;
            return response([
                'message' => storage_path('users/' . $uploadedImageResponse),
            ]);
        }

        $data2 = json_encode($request->all());

        $user->update($data); 

        return response([
            'message' => 'Update Profile Success',
            'data' => $user,
            'data2' => $data2
        ], 200);
    }

    public function findByIdUser($id){
        $user = User::find($id);

        if (!$user) {
            return response(['message' => 'Users not found'], 404);
        }

        return response([
            'message' => 'success get data user',
            'data' => $user
        ]);
    }

    public function test(){
        $user = DB::table('password_reset_tokens')->select('token')->where('email','tinartinar720@gmail.com')->value('column');
        
        return response([
            $user
        ]);
    }
}
