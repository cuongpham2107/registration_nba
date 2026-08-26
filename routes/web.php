<?php

use App\Http\Controllers\InvoiceFee;
use App\Http\Controllers\RegistrationController;
use App\Models\Registration;
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

Route::group([], function () {
    Route::resource('registration', RegistrationController::class)->names('registration');
});

Route::get('/approve/{id}', [RegistrationController::class, 'approve'])->name('approve');
Route::get('/reject/{id}', [RegistrationController::class, 'reject'])->name('reject');

Route::get('/approve-vehicle/{id}', [RegistrationController::class, 'approveVehicle'])->name('approve-vehicle');
Route::get('/reject-vehicle/{id}', [RegistrationController::class, 'rejectVehicle'])->name('reject-vehicle');

Route::get('test', function () {
    $registration = Registration::with('customers')->where('id', 25)->first();
    dd($registration->customers->first());
});

// Route mới với Filament Livewire
Route::get('/dang-ky-xe-khai-thac', \App\Livewire\RegistrationVehicleForm::class)->name('registration-vehicle.index');

Route::post('/registration-vehicle', [RegistrationController::class, 'storeVehicle'])->name('registration-vehicle.store');

// Success page route
Route::get('/registration-vehicle/success', function () {
    return view('registration_vehicle.success');
})->name('registration-vehicle.success');

// Route đăng ký khách
Route::get('/dang-ky-khach', \App\Livewire\RegistrationGuestForm::class)->name('registration-guest.index');
Route::get('/dang-ky-khach/thanh-cong', function () {
    return view('registration_guest.success');
})->name('registration-guest.success');

Route::get('/invoice/download/{registerDirectly}', [InvoiceFee::class, 'download'])->name('invoice.download');
