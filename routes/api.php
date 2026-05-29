<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeployController;

// 自動デプロイ
Route::post('/deploy',[DeployController::class,'deploy'])->name('deploy');
