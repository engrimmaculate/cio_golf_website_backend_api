<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify-email', function (\Illuminate\Http\Request $request) {
    $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
    return redirect($frontendUrl . '/verify-email?' . http_build_query($request->only(['token', 'email'])));
});
