<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Esta es la ruta oficial de Sanctum para CSRF (ya viene incluida)
// No necesitas redefinirla, pero la dejamos para claridad
