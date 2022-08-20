<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class WebPushController extends Controller
{
	public function pushSubscription(Request $request){
        $this->validate($request,[
			'endpoint'    => 'required',
			'keys.auth'   => 'required',
			'keys.p256dh' => 'required'
		]);
		$endpoint = $request->endpoint;
		$token = $request->keys['auth'];
		$key = $request->keys['p256dh'];
		$user = User::find(1);
		$user->updatePushSubscription($endpoint, $key, $token);
		
		return response()->json(['success' => true], 200);
    }
}
