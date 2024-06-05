<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class PushNotificationService {
    protected $messaging;

    public function __construct() {
        $factory = (new Factory)->withServiceAccount(config('firebase.credentials.file'));
        $this->messaging = $factory->createMessaging();
    }

    public function sendNotification($deviceToken, $title, $body) {
        $message = CloudMessage::withTarget('token', $deviceToken)
            ->withNotification(Notification::create($title, $body));

        $this->messaging->send($message);
    }
}
