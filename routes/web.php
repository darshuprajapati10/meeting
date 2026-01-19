<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\AdminController;

// Marketing Pages
Route::get('/', fn() => Inertia::render('Home'))->name('home');
Route::get('/features', fn() => Inertia::render('Features'))->name('features');
Route::get('/pricing', fn() => Inertia::render('Pricing'))->name('pricing');
Route::get('/about', fn() => Inertia::render('About'))->name('about');
Route::get('/contact', fn() => Inertia::render('Contact'))->name('contact');
Route::get('/get-started', fn() => Inertia::render('GetStarted'))->name('get-started');
Route::get('/blog', fn() => Inertia::render('Blog'))->name('blog');
Route::get('/blog/{slug}', fn($slug) => Inertia::render('BlogPost', ['slug' => $slug]))->name('blog.post');

// Legal & Policy Pages
Route::get('/cancellation-refunds', fn() => Inertia::render('CancellationRefunds'))->name('cancellation-refunds');
Route::get('/terms-and-conditions', fn() => Inertia::render('TermsAndConditions'))->name('terms-and-conditions');
Route::get('/shipping', fn() => Inertia::render('Shipping'))->name('shipping');
Route::get('/privacy', fn() => Inertia::render('Privacy'))->name('privacy');

// Authentication Routes (Web)
Route::get('/login', [WebLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebLoginController::class, 'login']);
Route::get('/register', fn() => Inertia::render('Auth/Signup'))->name('register');
Route::post('/logout', [WebLoginController::class, 'logout'])->name('logout');

// Admin Routes (Protected - Platform Admin Only)
Route::middleware(['auth', 'platform.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/organizations', [AdminController::class, 'organizations'])->name('organizations');
    Route::get('/contacts', [AdminController::class, 'contacts'])->name('contacts');
    Route::get('/meetings', [AdminController::class, 'meetings'])->name('meetings');
    Route::get('/surveys', [AdminController::class, 'surveys'])->name('surveys');
});
