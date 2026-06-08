<?php

use App\Http\Controllers\DownloadInvoiceController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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

Route::get('qrcode', function () {
    // return QrCode::size(300)->generate(route('registration.success'));
});

Route::group([], function () {
    Route::resource('registration', RegistrationController::class)->names('registration');
});

Route::get('/approve/{id}', [RegistrationController::class, 'approve'])->name('approve');
Route::get('/reject/{id}', [RegistrationController::class, 'reject'])->name('reject');

Route::get('/approve-vehicle/{id}', [RegistrationController::class, 'approveVehicle'])->name('approve-vehicle');
Route::get('/reject-vehicle/{id}', [RegistrationController::class, 'rejectVehicle'])->name('reject-vehicle');

// Success page route
// Route::get('/registration/success', function () {
//     return view('registration.success');
// })->name('registration.success');
Route::livewire('/{id}/invoice-with-company', 'invoice-with-company')->name('invoice.with-company');
// Invoice download route
Route::get('/invoice/download/{registrationEntry}', [DownloadInvoiceController::class, 'download'])
    ->middleware('signed')
    ->name('invoice.download');
