<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PegawaiController extends Controller
{
    public function showAll()
    {
        $pegawais = Pegawai::join('users', 'users.id_user', '=', 'pegawai.id_user')
        ->select('pegawai.*', 'users.*')
        ->get();

        return response([
            'message' => 'All Pegawai Retrieved',
            'data' => $pegawais
        ], 200);
    }

    public function showById($id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response(['message' => 'Pegawai not found'], 404);
        }

        return response([
            'message' => 'Show Pegawai Successfully',
            'data' => $pegawai
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validate = Validator::make($data, [
            'jabatan' => 'required',
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }
        if($data['jabatan'] == 'Owner'){
            $data['id_role'] = 1;
        } else if($data['jabatan'] == 'Manajer Oprasional'){
            $data['id_role'] = 2;
        } else if($data['jabatan'] == 'Admin'){
            $data['id_role'] = 3;
        } else {
            $data['id_role'] = 4;
        }
        

        if ($request->hasFile('foto_profile')) {
            $uploadFolder = 'images';
            $image = $request->file('foto_profile');

            
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
        // return response([
        //     'data' => $data
        // ]);

        $data['password'] = bcrypt($request->password);
        $user = User::create($data);
        $pegawai = new Pegawai;
        $pegawai->gaji = 0;
        $pegawai->bonus_gaji = 0;
        $pegawai->jabatan = $data['jabatan'];
        $pegawai->id_user = $user->id_user;

        $pegawai->save();


        return response([
            'message' => 'Pegawai created successfully',
            'data' => $pegawai,
            'data' => $user     
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response(['message' => 'Pegawai not found'], 404);
        }
        $user = User::find($id);
        
        $data = $request->all();

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
            $data['foto_profile'] = $uploadedImageResponse;

            return response([
                'message' => storage_path('users/' . $uploadedImageResponse),
            ]);
        }
        if($data['jabatan'] == 'Owner'){
            $data['id_role'] = 1;
        } else if($data['jabatan'] == 'Manajer Oprasional'){
            $data['id_role'] = 2;
        } else if($data['jabatan'] == 'Admin'){
            $data['id_role'] = 3;
        } else {
            $data['id_role'] = 4;
        }

        $user->update($data);
        $pegawai->update($data);

        return response([
            'message' => 'Pegawai updated successfully',
            'data' => $pegawai
        ], 200);
    }

    public function updateGajiBonus(Request $request,$id){
        $pegawai = Pegawai::find($id)->first();
      
        if (!$pegawai) {    
            return response(['message' => 'Pegawai not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'gaji' => 'required',
            'bonus_gaji' => 'required',
        ]);

        $pegawai =  $pegawai->update($data);

        return response([
            'message' => 'Pegawai updated successfully',
            'data' => $pegawai
        ], 200);
        
    }

    public function destroy($id)
    {
        $pegawai = Pegawai::find($id);

        if (!$pegawai) {
            return response(['message' => 'Pegawai not found'], 404);
        }

        $pegawai->delete();

        return response(['message' => 'Pegawai deleted successfully'], 200);
    }


}
