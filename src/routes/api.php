<?php

use App\Http\Controllers\OcrResultController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::post('/verifications', [VerificationController::class, 'store']);
Route::post('/ocr-results', [OcrResultController::class, 'store']);