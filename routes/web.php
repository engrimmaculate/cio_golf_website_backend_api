<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify-email', function (\Illuminate\Http\Request $request) {
    $frontendUrl = rtrim(env('FRONTEND_URL', 'http://localhost:3000'), '/');
    return redirect($frontendUrl . '/verify-email?' . http_build_query($request->only(['token', 'email'])));
});
