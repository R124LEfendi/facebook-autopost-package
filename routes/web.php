<?php

use Illuminate\Support\Facades\Route;
use Tokalink\FacebookAutopost\Http\Controllers\FacebookController;

Route::middleware(['web'])->prefix('facebook')->name('facebook.')->group(function () {
    Route::get('/', [FacebookController::class, 'index'])->name('dashboard');
    Route::post('/connect-user-token', [FacebookController::class, 'connectUserToken'])->name('connect.user-token');
    Route::post('/connect-single-page', [FacebookController::class, 'connectSinglePage'])->name('connect.single-page');
    Route::post('/toggle-page/{page}', [FacebookController::class, 'togglePage'])->name('page.toggle');
    Route::delete('/account/{account}', [FacebookController::class, 'deleteAccount'])->name('account.delete');
    Route::post('/post', [FacebookController::class, 'post'])->name('post');
});
