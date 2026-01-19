<?php
namespace App\Helpers;
use App\Models\UserNotification;
use App\Helpers\FCMHelper; // earlier provided
class Notify {
  public static function to($userId, $title, $message, $data = []) {
    UserNotification::create([
      'user_id'=>$userId, 'title'=>$title, 'message'=>$message,
      'type'=>$data['type'] ?? null, 'pass_id'=>$data['pass_id'] ?? null
    ]);
    // send FCM if token exists
    $user = \App\Models\User::find($userId);
    if ($user && $user->fcm_token) {
      FCMHelper::send($user->fcm_token, $title, $message, $data);
    }
  }
}
