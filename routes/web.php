<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\HrAnalyticsController;
use App\Http\Controllers\UploadedDocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/', [HrAnalyticsController::class, 'index'])->name('dashboard');
Route::get('/api/hr-analytics', [HrAnalyticsController::class, 'analytics'])->name('api.hr-analytics');
Route::post('/documents', [UploadedDocumentController::class, 'store'])->name('documents.store');
Route::get('/documents/{uploadedDocument}/download', [UploadedDocumentController::class, 'download'])->name('documents.download');
Route::delete('/documents/{uploadedDocument}', [UploadedDocumentController::class, 'destroy'])->name('documents.destroy');
