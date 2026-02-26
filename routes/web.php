<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

/*
|--------------------------------------------------------------------------
| Public Route
|--------------------------------------------------------------------------
*/
Route::get('/', function (): View {
    return view('welcome');
});


/*
|--------------------------------------------------------------------------
| Guest Routes (Not Logged In)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'login_form'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'register_form'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

});


/*
|--------------------------------------------------------------------------
| Authenticated Users
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Categories (all authenticated users)
    Route::resource('categories', CategoryController::class);

    // Posts (authenticated users)
    Route::resource('posts', PostController::class);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


/*
|--------------------------------------------------------------------------
| Admin Only
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'checkUserRole:admin'])->group(function () {

    // User Management
    Route::resource('users', AuthController::class)->except(['create', 'store']);

    // Route::get('/register', [AuthController::class, 'register_form'])->name('register');
    // Route::post('/register', [AuthController::class, 'register']);

});
/*
|--------------------------------------------------------------------------
| POSTS PERMISSIONS
|--------------------------------------------------------------------------
*/

// Everyone logged in can see posts
Route::middleware(['auth','checkUserRole:admin,editor,contributor,viewer'])
    ->resource('posts', PostController::class)
    ->only(['index','show']);


// Contributor+
Route::middleware(['auth','checkUserRole:admin,editor,contributor'])
    ->resource('posts', PostController::class)
    ->only(['create','store']);


// Editor+
Route::middleware(['auth','checkUserRole:admin,editor'])
    ->resource('posts', PostController::class)
    ->only(['edit','update']);


// Admin only
Route::middleware(['auth','checkUserRole:admin'])
    ->resource('posts', PostController::class)
    ->only(['destroy']);

/*
|--------------------------------------------------------------------------
| Editor Permissions (LIMIT DELETE)
|--------------------------------------------------------------------------
*/
// Route::middleware(['auth', 'checkUserRole:editor'])->group(function () {

//     // Editor can NOT delete posts
//     Route::resource('posts', PostController::class)
//         ->only(['index', 'show', 'create', 'store', 'edit', 'update']);
// });