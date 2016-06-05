<?php

namespace App\Http\Controllers\Page;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class IMController extends Controller
{
    public function main()
    {
        return view('im');
    }
}
