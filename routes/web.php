<?php

use App\Http\Controllers\ParseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SendMessage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',[ParseController::class, 'index']);

Route::get('/dashboard', [ParseController::class, 'dashboard'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/getdata', [ParseController::class, 'showData'])->name('getAlldata');
    Route::post('/getdata', [ParseController::class, 'getData']);
    Route::post('/sendTG', [ParseController::class, 'dataForTelegramBot']);
});

// send message
Route::get('/sendMessage', [SendMessage::class, 'send']);

require __DIR__.'/auth.php';
