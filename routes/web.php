<?php

use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhook', function (Nutgram $bot) {
    $bot->run();
    return response('ok');
});
