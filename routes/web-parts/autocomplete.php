<?php

use App\Http\Controllers\AutocompleteController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::prefix('autocomplete')
    ->controller(AutocompleteController::class)
    ->group(function () {
        $models = collect(['roles', 'permissions', 'doctors', 'medical-clinics', 'locations', 'users']);

        $models->each(
            fn ($m) => Route::get($m, Str::camel($m))->name('autocomplete.'.Str::snake(str_replace('-', ' ', $m)))
        );
    });
