<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        $userCount = \DB::table('a_users')->count();
        return view('welcome', ['userCount' => $userCount]);
    });

    Route::get('/models', function () {
        return view('models');
    });

    Route::get('/datatables', function () {
        return view('datatables');
    });
});

Route::middleware('guest')->get('/login', function () {
    return view('login');
})->name('login');

Route::middleware('guest')->post('/login', function (Request $request) {
    $credentials = $request->validate([
        'username' => ['required', 'string'],
        'password' => ['required', 'string'],
    ]);

    $user = \App\User::where('username', $credentials['username'])->first();
    if ($user && hash_equals((string) $user->password, (string) $credentials['password'])) {
        Auth::login($user, false);
        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    return back()
        ->withErrors(['username' => 'Invalid credentials.'])
        ->onlyInput('username');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->middleware('auth')->name('logout');
