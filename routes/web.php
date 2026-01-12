<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

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
        $tenantDb = request()->session()->get('tenant_db', config('dropy.tenants.default'));
        $tenantLabels = config('dropy.tenants.allowed', []);

        return view('welcome', [
            'userCount' => $userCount,
            'tenantDb' => $tenantDb,
            'tenantLabels' => $tenantLabels,
        ]);
    });

    Route::get('/models', function () {
        return view('models');
    });

    Route::get('/datatables', function () {
        return view('datatables');
    });

    Route::get('/users', function () {
        $error = null;
        $userStats = null;
        $recentUsers = collect();
        $levelDistribution = collect();

        try {
            $userStats = \DB::table('users')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN verified > 0 THEN 1 ELSE 0 END) as verified')
                ->selectRaw('SUM(CASE WHEN byGoogle > 0 THEN 1 ELSE 0 END) as google')
                ->selectRaw('SUM(CASE WHEN appleUser > 0 THEN 1 ELSE 0 END) as apple')
                ->selectRaw('SUM(CASE WHEN profileAccess > 0 THEN 1 ELSE 0 END) as profile_access')
                ->selectRaw("SUM(CASE WHEN email IS NULL OR email = '' THEN 1 ELSE 0 END) as missing_email")
                ->selectRaw("SUM(CASE WHEN bio IS NULL OR bio = '' THEN 1 ELSE 0 END) as missing_bio")
                ->selectRaw("SUM(CASE WHEN image IS NULL OR image = '' THEN 1 ELSE 0 END) as missing_image")
                ->first();

            $recentUsers = \DB::table('users')
                ->select('id', 'username', 'email', 'level', 'verified', 'time')
                ->orderByDesc('id')
                ->limit(10)
                ->get();

            $levelDistribution = \DB::table('users')
                ->select('level', \DB::raw('COUNT(*) as count'))
                ->groupBy('level')
                ->orderByDesc('count')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('users', [
            'userStats' => $userStats,
            'recentUsers' => $recentUsers,
            'levelDistribution' => $levelDistribution,
            'error' => $error,
        ]);
    });

    Route::get('/course', function () {
        $error = null;
        $categories = collect();
        $lessonsByCategory = collect();
        $carouselCounts = collect();
        $testCounts = collect();
        $carouselCountsByLesson = collect();
        $testCountsByLesson = collect();
        $carouselSeriesByLesson = collect();
        $testSeriesByLesson = collect();

        try {
            $categories = \DB::connection('tenant')
                ->table('category_course')
                ->select('id', 'var_idtest_1', 'var_idtest_1_1', 'var_idtest_3')
                ->orderBy('var_idtest_3')
                ->orderBy('var_idtest_1')
                ->get();

            $lessonsByCategory = \DB::connection('tenant')
                ->table('course')
                ->select('id', 'category_url', 'url', 'title')
                ->orderBy('category_url')
                ->orderBy('title')
                ->get()
                ->groupBy('category_url');

            $carouselCounts = \DB::connection('tenant')
                ->table('course_carousel as cc')
                ->join('course as c', 'c.url', '=', 'cc.course_url')
                ->select('c.category_url', \DB::raw('COUNT(cc.id) as count'))
                ->groupBy('c.category_url')
                ->pluck('count', 'c.category_url');

            $testCounts = \DB::connection('tenant')
                ->table('course_test as ct')
                ->join('course as c', 'c.url', '=', 'ct.course_url')
                ->select('c.category_url', \DB::raw('COUNT(ct.id) as count'))
                ->groupBy('c.category_url')
                ->pluck('count', 'c.category_url');

            $carouselCountsByLesson = \DB::connection('tenant')
                ->table('course_carousel')
                ->select('course_url', \DB::raw('COUNT(*) as count'))
                ->groupBy('course_url')
                ->pluck('count', 'course_url');

            $testCountsByLesson = \DB::connection('tenant')
                ->table('course_test')
                ->select('course_url', \DB::raw('COUNT(*) as count'))
                ->groupBy('course_url')
                ->pluck('count', 'course_url');

            $carouselSeriesByLesson = \DB::connection('tenant')
                ->table('course_carousel')
                ->select('course_url', 'series', \DB::raw('COUNT(*) as count'))
                ->groupBy('course_url', 'series')
                ->orderBy('series')
                ->get()
                ->groupBy('course_url');

            $testSeriesByLesson = \DB::connection('tenant')
                ->table('course_test')
                ->select('course_url', 'series', \DB::raw('COUNT(*) as count'))
                ->groupBy('course_url', 'series')
                ->orderBy('series')
                ->get()
                ->groupBy('course_url');
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('course', [
            'categories' => $categories,
            'lessonsByCategory' => $lessonsByCategory,
            'carouselCounts' => $carouselCounts,
            'testCounts' => $testCounts,
            'carouselCountsByLesson' => $carouselCountsByLesson,
            'testCountsByLesson' => $testCountsByLesson,
            'carouselSeriesByLesson' => $carouselSeriesByLesson,
            'testSeriesByLesson' => $testSeriesByLesson,
            'error' => $error,
        ]);
    });

    Route::get('/course-history', function () {
        $error = null;
        $history = collect();
        $courses = collect();
        $users = collect();
        $completedCount = 0;
        $neverStartedCount = 0;
        $startedCount = 0;
        $inProgressCount = 0;
        $activeUsers7d = 0;
        $activeUsers30d = 0;
        $totalTimeSeconds = 0;
        $topLessons = collect();
        $inactiveCategories = collect();

        try {
            $startedCount = \DB::connection('tenant')
                ->table('course_history')
                ->where('start_time', '>', 0)
                ->count();

            $completedCount = \DB::connection('tenant')
                ->table('course_history')
                ->where('start_time', '>', 0)
                ->where('end_time', '>', 0)
                ->where('slides_study', '>', 0)
                ->where('quizzes_study', '>', 0)
                ->where('time_study', '>', 0)
                ->count();

            $inProgressCount = max($startedCount - $completedCount, 0);

            $neverStartedCount = \DB::connection('tenant')
                ->table('course as c')
                ->leftJoin('course_history as ch', 'ch.course_id', '=', 'c.id')
                ->whereNull('ch.course_id')
                ->count();

            $totalTimeSeconds = (int) \DB::connection('tenant')
                ->table('course_history')
                ->where('time_study', '>', 0)
                ->sum('time_study');

            $activeUsers7d = \DB::connection('tenant')
                ->table('course_history')
                ->where('user_id', '>', 0)
                ->where(function ($query) {
                    $threshold = time() - (7 * 86400);
                    $query->where('end_time', '>=', $threshold)
                        ->orWhere('start_time', '>=', $threshold);
                })
                ->distinct()
                ->count('user_id');

            $activeUsers30d = \DB::connection('tenant')
                ->table('course_history')
                ->where('user_id', '>', 0)
                ->where(function ($query) {
                    $threshold = time() - (30 * 86400);
                    $query->where('end_time', '>=', $threshold)
                        ->orWhere('start_time', '>=', $threshold);
                })
                ->distinct()
                ->count('user_id');

            $topLessons = \DB::connection('tenant')
                ->table('course_history as ch')
                ->select('ch.course_id', \DB::raw('COUNT(*) as count'))
                ->groupBy('ch.course_id')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $inactiveCategories = \DB::connection('tenant')
                ->table('category_course as cat')
                ->leftJoin('course as c', 'c.category_url', '=', 'cat.var_idtest_1_1')
                ->leftJoin('course_history as ch', 'ch.course_id', '=', 'c.id')
                ->select('cat.var_idtest_1 as title', 'cat.var_idtest_1_1 as code', \DB::raw('COUNT(ch.id) as activity_count'))
                ->groupBy('cat.var_idtest_1', 'cat.var_idtest_1_1')
                ->havingRaw('COUNT(ch.id) = 0')
                ->orderBy('cat.var_idtest_1')
                ->limit(10)
                ->get();

            $history = \DB::connection('tenant')
                ->table('course_history')
                ->select('id', 'user_id', 'course_id', 'slides_study', 'quizzes_study', 'time_study', 'start_time', 'end_time')
                ->orderByDesc('id')
                ->limit(200)
                ->get();

            $courseIds = $history->pluck('course_id')->filter()->unique()->values();
            $userIds = $history->pluck('user_id')->filter()->unique()->values();

            if ($courseIds->isNotEmpty()) {
                $courses = \DB::connection('tenant')
                    ->table('course')
                    ->select('id', 'category_url', 'url', 'title')
                    ->whereIn('id', $courseIds)
                    ->get()
                    ->keyBy('id');
            }

            if ($userIds->isNotEmpty()) {
            $users = \DB::table('users')
                ->whereIn('id', $userIds)
                ->get()
                ->keyBy('id');
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('course-history', [
            'history' => $history,
            'courses' => $courses,
            'users' => $users,
            'completedCount' => $completedCount,
            'neverStartedCount' => $neverStartedCount,
            'startedCount' => $startedCount,
            'inProgressCount' => $inProgressCount,
            'activeUsers7d' => $activeUsers7d,
            'activeUsers30d' => $activeUsers30d,
            'totalTimeSeconds' => $totalTimeSeconds,
            'topLessons' => $topLessons,
            'inactiveCategories' => $inactiveCategories,
            'error' => $error,
        ]);
    });

    Route::get('/books', function () {
        return view('books');
    });

    Route::get('/games', function () {
        return view('games');
    });

    Route::get('/flash-cards', function () {
        return view('flash-cards');
    });

    Route::post('/tenant/switch', function (Request $request) {
        $allowed = array_keys(config('dropy.tenants.allowed', []));
        $data = $request->validate([
            'db' => ['required', Rule::in($allowed)],
        ]);

        $request->session()->put('tenant_db', $data['db']);

        return back();
    })->name('tenant.switch');
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
