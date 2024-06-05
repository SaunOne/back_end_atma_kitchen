<?php
namespace App\Http\Controllers\api;

use App\Services\PushNotificationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaksi;

class OrderController extends Controller {
    public function store(Request $request) {
        // Validasi dan logika untuk menyimpan pesanan
        $order = Transaksi::create($request->all());
        // return response(["message" => $order]);
        // Dapatkan token perangkat pengguna
        $user = User::find($order->id_user);
        $deviceToken = $user->device_token;

        // Kirim notifikasi push
        $pushService = new PushNotificationService();
        $pushService->sendNotification($deviceToken, 'Pesanan Baru', 'Anda memiliki pesanan baru');

        return response()->json(['message' => 'Order created successfully']);
    }
}
