<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PhotoboothController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [PhotoboothController::class, 'index'])->name('photobooth.index');
Route::post('/capture', [PhotoboothController::class, 'capture'])->name('photobooth.capture');
Route::post('/create-collage', [PhotoboothController::class, 'createCollage'])->name('photobooth.collage');
Route::get('/download/{filename}', [PhotoboothController::class, 'download'])->name('photobooth.download');

