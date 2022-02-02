<?php

use App\Http\Controllers\TodoController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|------------------------------------------
| API Routes
|------------------------------------------
*/

Route::middleware('guest:api_guard')
->group(function(){
    Route::post('/user/login', [UserController::class, 'login'])->name('user.login');
    Route::post('/user/register', [UserController::class, 'register'])->name('user.register');
});

Route::middleware('auth:api_guard')
->prefix('user')
->name('user.')
->group(function(){
    Route::controller(UserController::class)
    ->group(function(){
        Route::get('/profile', 'show')->name('profile');
        Route::put('/update', 'update')->name('update');
        Route::post('/logout', 'logout')->name('logout');
    });

    Route::controller(TodoController::class)
    ->prefix('todo')
    ->name('todo.')
    ->group(function(){
        Route::get('/all', 'index')->name('all');
        Route::get('/{id}', 'show')->name('single');
        Route::post('/create', 'store')->name('create');
        Route::put('/update/{id}', 'update')->name('update');
        Route::put('/mark-as-done/{id}', 'markAsDone')->name('done');
        Route::delete('/delete/{id}', 'destroy')->name('delete');
    });
});

