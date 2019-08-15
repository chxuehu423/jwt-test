<?php

namespace App\Http\Controllers;

use App\Utils\BLogger;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function index(){
        //dd($this->currentUser);
        BLogger::writeInfoLog('cardController:'.json_encode($this->currentUser));
        return $this->renderJsonWithSuccess(auth('api')->user());
    }
}
