<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApplicationController;

Route::fallback([ApplicationController::class, '__invoke']);