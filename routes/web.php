<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/absensi',[AbsensiController::class,'index']);

Route::post('/absensi/store',[AbsensiController::class,'store']);