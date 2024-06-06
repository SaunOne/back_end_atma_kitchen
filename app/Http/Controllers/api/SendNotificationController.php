<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DateTime;
use onesignal\client\api\DefaultApi;
use onesignal\client\Configuration;
use onesignal\client\model\GetNotificationRequestBody;
use onesignal\client\model\Notification;
use onesignal\client\model\StringMap;
use onesignal\client\model\Player;
use onesignal\client\model\UpdatePlayerTagsRequestBody;
use onesignal\client\model\ExportPlayersRequestBody;
use onesignal\client\model\Segment;
use onesignal\client\model\FilterExpressions;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp;

class SendNotificationController extends Controller
{
    public function sendNotifKonfirmasiProses($id_user,$id_transaksi)
    {
      
        $config = Configuration::getDefaultConfiguration()
            ->setAppKeyToken(env('ONESIGNAL_APP_KEY_TOKEN'));

        $apiInstance = new DefaultApi(
            new GuzzleHttp\Client(),
            $config
        );
            $content = new StringMap();
            $header  = new StringMap();
            $content->setEn("Pesanan Dengan Id ". $id_transaksi ." Sedang Diproses");
            $header->setEn("-- Atma Kitchen --");
        
            $notification = new Notification();
            $notification->setAppId(env('ONESIGNAL_APP_ID'));
            $notification->setContents($content);
            $notification->setHeadings($header);
            $notification->setIncludeExternalUserIds([$id_user]);

            $result = $apiInstance->createNotification($notification);

            // return response([
            //     "message" => "success send message",
            //     $result
            // ]);
        
    }
}
