<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $messaging = app('firebase.messaging');

        $message = [
            'token' => $request->input('token'), // Token perangkat tujuan
            'notification' => [
                'title' => $request->input('title'),
                'body' => $request->input('body'),
            ],
        ];
        // return response(["isi" => $messaging]);
        try {
            $messaging->send($message);
            return response()->json(['status' => 'Notification sent']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error sending notification', 'error' => $e->getMessage()]);
        }
    }
}
