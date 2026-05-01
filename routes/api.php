<?php

use App\Http\Controllers\IssueController;
use Illuminate\Support\Facades\Route;

Route::resource('issues', IssueController::class)
    ->only(['index', 'store', 'show', 'update']);
