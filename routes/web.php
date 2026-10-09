<?php

use App\Http\Controllers\Frontend\MainController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\WelcomeController;


require 'admin.php';
Route::get('/clear', function () {
    $exitCode = Artisan::call('optimize:clear');
    return '<h1>Optimize Cleared Now</h1>';
});

Route::controller(MainController::class)->group(function () {
    Route::get('/', 'index')->name('index');
});


// Route::get('/optimize-clear', function () {
//     Artisan::call('optimize:clear');

//     return response()->json([
//         'success' => true,
//         'message' => 'Application cache cleared successfully.',
//     ]);
// });

Route::get('/storage-link', function () {
    Artisan::call('storage:link');

    return response()->json([
        'success' => true,
        'message' => 'Storage link created successfully.',
    ]);
});
