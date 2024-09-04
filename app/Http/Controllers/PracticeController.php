<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PracticeController extends Controller
{
    public function storeCache(Request $request) {
    	$user = new User();
    	$user->name = $request->name;
    	$user->mobile = $request->mobile;
    	$user->email = $request->email;
    	$user->password = Hash::make('123456');
    	$user->save();
    	$unique_key = "user_".$user->id;
    	$values = [];
    	$values['id'] = $user->id;
    	$values['name'] = $user->name;
    	$values['email'] = $user->email;
    	$values['mobile'] = $user->mobile;
    	
    	Cache::put($unique_key,$values);
    	Cache::put($unique_key.'_id',$user->id);
    	Cache::put($unique_key.'_mobile',$user->mobile,);
    	Cache::put($unique_key.'_email',$user->email);
    	return Cache::get($unique_key."_id");
    }

    public function getChache($id) {
    	$unique_key = "user_".$id;
    	if (Cache::has($unique_key)) {
    		return Cache::get($unique_key);
		} else {
			return null;
		}
    }
}
