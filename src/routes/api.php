<?php
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::post('/verifications', [VerificationController::class, 'store']);