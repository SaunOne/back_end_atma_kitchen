<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DetailHampers;
use App\Models\Produk;
use App\Models\ProdukUtama;
use App\Models\ReadyStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\api\ProdukTitipanController;
use App\Http\Controllers\api\ProdukUtamaController;
use App\Http\Controllers\api\HampersController;
use App\Http\Controllers\api\ReadyStokController;
use App\Http\Controllers\api\DetailHampersController;
use App\Models\Hampers;
use App\Models\ProdukTitipan;
use PhpParser\Node\Expr\CallLike;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function showAll()
    {
        $produks = Produk::join('ready_stok', 'ready_stok.id_stok_produk', '=', 'produk.id_stok_produk')->select('produk.*', 'ready_stok.*')->get();


        
        foreach ($produks as $produk) {
            if ($produk->jenis_produk == "Titipan") {
            } else if ($produk->jenis_produk == "Hampers") {
                $products = DB::table('detail_hampers as dh')
                    ->join('hampers as h', 'dh.id_hampers', '=', 'h.id_produk')
                    ->join('produk_utama as pu', 'pu.id_produk', '=', 'dh.id_produk')
                    ->join('produk as p', 'p.id_produk', '=', 'pu.id_produk')
                    ->join('ready_stok as rs', 'rs.id_stok_produk', '=', 'p.id_stok_produk')
                    ->select('p.*', 'rs.*')
                    ->where('h.id_produk', '=', $produk->id_produk)
                    ->get();
                $produk->produk = $products;
            } else if ($produk->jenis_produk == "Utama") {
            }
        }

        return response([
            'message' => 'All Produk Retrieved',
            'data' => $produks
        ], 200);
    }



    public function showById($id)
    {
        
        $produk = Produk::find($id)->first();

        if (!$produk) {
            return response(['message' => 'Produk not found'], 404);
        }

        return response([
            'message' => 'Show Produk Successfully',
            'data' => $produk
        ], 200);
    }

    // public function showByIdAll($id)
    // {
        
    //     $produk = Produk::find($id)->first();

    //     if (!$produk) {
    //         return response(['message' => 'Produk not found'], 404);
    //     }

    //     return response([
    //         'message' => 'Show Produk Successfully',
    //         'data' => $produk
    //     ], 200);
    // }

    public function searchProduk($search)
    {

        $result = Produk::where('nama_produk', 'like', '%' . $search . '%')
            ->orWhere('nama_produk', 'like', '%' . $search . '%')
            ->orWhere('jenis_produk', 'like', '%' . $search . '%')
            ->orWhere('harga', 'like', '%' . $search . '%')
            ->orWhere('deskripsi', 'like', '%' . $search . '%')
            ->get();

        if ($result === []) {
            return response([
                "message" => "Produk not found",
                "result" => $search,
            ]);
        }

        return response([
            "message" => "Show Produk Successfully",
            "data" => $result
        ]);
    }

    public function store(Request $request)
    {   
        

        $data = $request->all();

        $data['limit_harian'] = 5;
        
        $validate = Validator::make($data, [
            'id_packaging' => 'required',
            'jenis_produk' => 'required',

        ]);
        
        if($data['jenis_produk'] == 'Hampers'){
            
            $data['jumlah_stok'] = 0;
            $readyStok = ReadyStok::create($data);
            $data['id_stok_produk'] = $readyStok->id_stok_produk;
            //create produk
            $produk = Produk::create($data);
            $data['id_produk'] = $produk['id_produk'];

            $hamper = new Hampers;
            $hamper->id_packaging = $data['id_packaging'];
            $hamper->id_produk = $data['id_produk'];
            $hamper->limit_harian = 5;
            $hamper->save();

            foreach($data['detail_hampers'] as $dh){
                $dh['id_hampers'] = $data['id_produk'];
                DetailHampers::create($dh);
            }

        }
        
        if(!isset($data['id_penitip']) && ($data['jenis_produk'] == 'Titipan') ){
            
            $produk = Produk::select('id_stok_produk')->find($data['id_produk'])->first();
            $data['id_stok_produk'] = $produk->id_stok_produk;
        } 

        if(isset($data['id_penitip']) && ($data['jenis_produk'] == 'Titipan') ){
            $data['nama_stok_produk'] = $data['nama_produk'];
        } 
        
        
        

        //ketika membuat produk dengan stok baru
        if (!isset($data['id_stok_produk'])) {

            $validate = Validator::make($data, [
                // 'id_stok_produk' => 'required',
                'satuan' => 'required',
                'nama_produk_stok' => 'required',
            ]);

            if ($validate->fails()) {
                return response(
                    ["Message" => $validate->errors()->first(), 400]
                );
            }
            //jumlah stoknya kita 0 dulu
            $data['jumlah_stok'] = 0;
            $readyStok = ReadyStok::create($data);

            $data['id_stok_produk'] = $readyStok->id_stok_produk;
            
        }

        if ($request->hasFile('image_produk')) {
            $uploadFolder = 'images';
            $image = $request->file('image_produk');

            if ($data['image_produk']) {
                Storage::disk('public')->delete($data['image_produk']);
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
            $data['image_produk'] = 'images/' . $uploadedImageResponse;
        }

        //kalo engga ada
        if (!isset($data['id_produk']) && isset($data['jenis_produk']) == 'Utama') {
            //ini create produk baru
            
            $produk = Produk::create($data);
            $data['id_produk'] = $produk['id_produk'];
            $data['id_ready_stok'] = $produk->id_stok_produk;
        } else {
            //kalo id produknya ada
            // return (["message" => "success update", "data" => $data]);
            
            $produk = Produk::find($data['id_produk'])->first();
            $produk->update($data);
            $data['id_ready_stok'] = $produk->id_stok_produk;
        }

        //create produk dan ketika produk titipan
        switch ($data['jenis_produk']) {
            case 'Utama':
                $validate = Validator::make($data, [
                    'id_stok_produk' => 'required',
                    // 'satuan' => 'required',
                    // 'nama_produk_stok' => 'required',
                ]);
                ProdukUtama::create($data);
                break;
            case 'Titipan':
                $validate = Validator::make($data, [
                    'id_penitip' => 'required',
                    'nama_produk' => 'required',
                    'jumlah_produk_dititip' => 'required',
                    'harga' => 'required',
                    'image_produk' => 'required'
                ]);

                //ketika produk lama

                $readyStok = ReadyStok::where('id_stok_produk', $data['id_stok_produk'])
                    ->update(['jumlah_stok' => DB::raw('jumlah_stok + ' . $data['jumlah_produk_dititip'])]);
                
                $data['tanggal'] = now();

                $produkTitipan = ProdukTitipan::create($data);
                return (["message" => "success create titipan", "data" => $readyStok]);
                break;
            case 'Hampers':
                
                break;
        }


        return response([
            "message" => "Succes Add product Utama",
            "data" => $data
        ]);
    }



    // public function store(Request $request)
    // {
    //     $data = $request->all();

    //     if (!isset($data['id_produk'])) {
    //         if ($data['jenis_produk'] == 'produk utama') {
    //             $validate = Validator::make($data, [
    //                 'id_packaging' => 'required',
    //                 'katagorie_produk' => 'required',
    //             ]);
    //             if ($validate->fails()) {
    //                 return response(['message' => $validate->errors()->first()], 400);
    //             }
    //         } else if ($data['jenis_produk'] == 'produk titipan') {
    //             $validate = Validator::make($data, [
    //                 'id_penitip' => 'required',
    //                 'jumlah_produk_dititip' => 'required',
    //             ]);
    //             if ($validate->fails()) {
    //                 return response(['message' => $validate->errors()->first()], 400);
    //             }
    //         } else if ($data['jenis_produk'] == 'hampers') {
    //             $validate = Validator::make($data, [
    //                 'id_packaging' => 'required',
    //                 'limit_harian' => 'required',
    //                 'detail_hampers' => 'required'
    //             ]);
    //             if ($validate->fails()) {
    //                 return response(['message' => $validate->errors()->first()], 400);
    //             }
    //         }
    //     }
    //     $validate = Validator::make($data, [
    //         'nama_produk' => 'required',
    //         'harga' => 'required',
    //         'quantity' => 'required',
    //         'deskripsi' => 'required',
    //         'jenis_produk' => 'required',
    //     ]);

    //     if ($validate->fails()) {
    //         return response(['message' => $validate->errors()->first()], 400);
    //     }

    //     if ($data['jenis_produk'] === 'produk titipan' && isset($data['id_produk'])) {
    //         $data['id_stok_produk'] = Produk::where('id_produk', $data['id_produk'])->value('id_stok_produk');
    //         $data['jumlah_stok'] = $data['jumlah_produk_dititip'];
    //     }

    //     $readyStok = ReadyStok::updateOrCreate(
    //         ['id_stok_produk' => $data['id_stok_produk'] ?? null],
    //         ['jumlah_stok' => DB::raw('jumlah_stok + ' . ($data['jumlah_stok'] ?? 0))]
    //     );

    //     if (!isset($data['id_stok_produk'])) {
    //         // $readyStok['satuan'] = $data['satuan'];  
    //         $readyStok['jumlah_stok'] = $data['jumlah_stok'];
    //         $readyStok->save();
    //         $data['id_stok_produk'] = $readyStok['id_stok_produk'];
    //     }

    //     $produk = Produk::updateOrCreate(
    //         ['id_produk' => $data['id_produk'] ?? null],
    //         $data
    //     );

    //     $data['id_produk'] = $produk['id_produk'];

    //     switch ($data['jenis_produk']) {
    //         case 'Utama':
    //             app(ProdukUtamaController::class)->store(new Request($data));
    //             break;
    //         case 'Hampers':

    //             $data['DetailHampers']['id_produk'] = $produk['id_produk'];

    //             $data = Hampers::create($data['DetailHampers']);
    //             return response(['data' =>  $data['DetailHampers']]);
    //             $this->handleDetailHampers($data);
    //             break;
    //         case 'Titipan':
    //             app(ProdukTitipanController::class)->store(new Request($data));
    //             break;
    //     }

    //     return response(['message' => 'Produk created successfully'], 200);
    // }

    // protected function handleDetailHampers($data)
    // {
    //     foreach ($data['detail_hampers'] as $dH) {
    //         app(DetailHampersController::class)->store(new Request(array_merge($dH, ["id_hampers" => $data["id_produk"]])));
    //     }
    // }

    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response(['message' => 'Produk not found'], 404);
        }

        $data = $request->all();

        $validate = Validator::make($data, [
            'nama_produk' => 'required',
            'harga' => 'required',
            'quantity' => 'required',
            'deskripsi' => 'required',
            'jenis_produk' => 'required'
        ]);

        if ($validate->fails()) {
            return response(['message' => $validate->errors()->first()], 400);
        }

        $produk->update($data);

        return response([
            'message' => 'Produk updated successfully',
            'data' => $produk
        ], 200);
    }

    public function destroy($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response(['message' => 'Produk not found'], 404);
        }

        $produk->delete();

        return response(['message' => 'Produk deleted successfully'], 200);
    }
}
