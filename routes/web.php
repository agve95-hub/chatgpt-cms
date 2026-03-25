<?php

use App\Http\Controllers\GithubWebhookController;
use App\Http\Controllers\SiteController;
use App\Livewire\Sites\Index as SitesIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', SitesIndex::class)->name('sites.index');
Route::post('/sites', [SiteController::class, 'store'])->name('sites.store');
Route::post('/webhooks/github', GithubWebhookController::class)->name('webhooks.github');
