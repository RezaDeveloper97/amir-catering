<?php

use App\Telegram\Core\TelegramBot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhook', function (Request $request, TelegramBot $bot) {
    $bot->processUpdate($request->all());
    return response('ok');
});
