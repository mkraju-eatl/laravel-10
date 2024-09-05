<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function rememberOnCache()
    {
        $value = Cache::get('user_lists', function () {
            return User::all();
        });
        return $value;
    }

    public function fetchFromCache()
    {
        return User::getCachedRecords();
    }
}
