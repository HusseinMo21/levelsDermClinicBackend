<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Swagger UI Route
Route::get('/api-docs', function () {
    return view('swagger-ui');
});

// Swagger JSON Route
Route::get('/swagger.json', function () {
    return response()->file(public_path('swagger.json'));
});
