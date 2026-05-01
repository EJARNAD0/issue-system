<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/issues', [IssueController::class, 'index']);
Route::post('/issues', [IssueController::class, 'store']);
Route::get('/issues/{id}', [IssueController::class, 'show']);
Route::put('/issues/{id}', [IssueController::class, 'update']);
