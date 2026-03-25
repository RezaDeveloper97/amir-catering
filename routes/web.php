<?php

use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhook', function (Nutgram $bot) {
    try {
        \Log::info('Webhook hit', ['update' => file_get_contents('php://input')]);
        $bot->run();
        \Log::info('Webhook processed successfully');
    } catch (\Throwable $e) {
        \Log::error('Webhook error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
    }
    return response('ok');
});
