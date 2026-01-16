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

    Route::get('/users', function (Request $request) {
        $error = null;
        $userStats = null;
        $recentUsers = collect();
        $levelDistribution = collect();
        $newToday = 0;
        $newWeek = 0;
        $newMonth = 0;
        $newYear = 0;
        $debug = null;
        $range = $request->query('range', 'today');
        $search = trim((string) $request->query('q', ''));
        $rangeLabels = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'week' => 'This week',
            'month' => 'This month',
        ];

        try {
            $userStats = \DB::table('users')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN verified > 0 THEN 1 ELSE 0 END) as verified')
                ->selectRaw('SUM(CASE WHEN byGoogle > 0 THEN 1 ELSE 0 END) as google')
                ->selectRaw('SUM(CASE WHEN appleUser > 0 THEN 1 ELSE 0 END) as apple')
                ->first();

            $newToday = \DB::table('users')
                ->whereRaw('DATE(FROM_UNIXTIME(`time`)) = CURDATE()')
                ->count();

            $newWeek = \DB::table('users')
                ->whereRaw('YEARWEEK(FROM_UNIXTIME(`time`), 1) = YEARWEEK(CURDATE(), 1)')
                ->count();

            $newMonth = \DB::table('users')
                ->whereRaw('YEAR(FROM_UNIXTIME(`time`)) = YEAR(CURDATE())')
                ->whereRaw('MONTH(FROM_UNIXTIME(`time`)) = MONTH(CURDATE())')
                ->count();

            $newYear = \DB::table('users')
                ->whereRaw('YEAR(FROM_UNIXTIME(`time`)) = YEAR(CURDATE())')
                ->count();

            $debugDb = \DB::selectOne('SELECT NOW() as db_now, CURDATE() as db_curdate, @@session.time_zone as db_session_tz, @@system_time_zone as db_system_tz');
            $timeStats = \DB::selectOne('SELECT MAX(`time`) as max_time, MIN(`time`) as min_time, FROM_UNIXTIME(MAX(`time`)) as max_time_dt, FROM_UNIXTIME(MIN(`time`)) as min_time_dt FROM users');
            $todayCheck = \DB::selectOne('SELECT COUNT(*) as count FROM users WHERE DATE(FROM_UNIXTIME(`time`)) = CURDATE()');

            $debug = [
                'php_now' => now()->toDateTimeString(),
                'php_tz' => now()->timezoneName,
                'php_utc_now' => now('UTC')->toDateTimeString(),
                'db_now' => $debugDb->db_now ?? null,
                'db_curdate' => $debugDb->db_curdate ?? null,
                'db_session_tz' => $debugDb->db_session_tz ?? null,
                'db_system_tz' => $debugDb->db_system_tz ?? null,
                'max_time' => $timeStats->max_time ?? null,
                'max_time_dt' => $timeStats->max_time_dt ?? null,
                'min_time' => $timeStats->min_time ?? null,
                'min_time_dt' => $timeStats->min_time_dt ?? null,
                'db_today_count' => $todayCheck->count ?? null,
            ];

            if (!array_key_exists($range, $rangeLabels)) {
                $range = 'today';
            }

            $recentUsersQuery = \DB::table('users')
                ->select('id', 'name', 'username', 'email', 'level', 'verified', 'time', 'image')
                ->selectRaw('FROM_UNIXTIME(`time`) as time_label');

            if ($range === 'today') {
                $recentUsersQuery->whereRaw('DATE(FROM_UNIXTIME(`time`)) = CURDATE()');
            } elseif ($range === 'yesterday') {
                $recentUsersQuery->whereRaw('DATE(FROM_UNIXTIME(`time`)) = CURDATE() - INTERVAL 1 DAY');
            } elseif ($range === 'week') {
                $recentUsersQuery->whereRaw('YEARWEEK(FROM_UNIXTIME(`time`), 1) = YEARWEEK(CURDATE(), 1)');
            } elseif ($range === 'month') {
                $recentUsersQuery->whereRaw('YEAR(FROM_UNIXTIME(`time`)) = YEAR(CURDATE())')
                    ->whereRaw('MONTH(FROM_UNIXTIME(`time`)) = MONTH(CURDATE())');
            }

            if ($search !== '') {
                $recentUsersQuery->where(function ($query) use ($search) {
                    $like = '%' . $search . '%';
                    $query->where('name', 'like', $like)
                        ->orWhere('username', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            }

            $recentUsers = $recentUsersQuery
                ->orderByDesc('time')
                ->limit(1000)
                ->get();

            $levelDistribution = \DB::table('users')
                ->select('level', \DB::raw('COUNT(*) as count'))
                ->groupBy('level')
                ->orderBy('level')
                ->limit(3)
                ->get();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('users', [
            'userStats' => $userStats,
            'recentUsers' => $recentUsers,
            'levelDistribution' => $levelDistribution,
            'newToday' => $newToday,
            'newWeek' => $newWeek,
            'newMonth' => $newMonth,
            'newYear' => $newYear,
            'range' => $range,
            'rangeLabel' => $rangeLabels[$range] ?? 'Today',
            'search' => $search,
            'debug' => $debug,
            'error' => $error,
        ]);
    });

    Route::get('/subscription', function () {
        $error = null;
        $stats = [
            'active' => 0,
            'expiring_7d' => 0,
            'expired' => 0,
            'plan_basic' => 0,
            'plan_pro' => 0,
            'plan_unknown' => 0,
            'success_count' => 0,
            'error_count' => 0,
            'cancel_count' => 0,
            'pending_count' => 0,
            'revenue_total' => 0.0,
            'revenue_web' => 0.0,
            'revenue_app' => 0.0,
            'revenue_month' => 0.0,
            'active_mrr' => 0.0,
        ];
        $activeSubscriptions = collect();
        $activeWebCount = 0;
        $activeAppCount = 0;

        try {
            $now = time();
            $in7d = $now + (7 * 86400);

            $activeQuery = \DB::connection('mysql')
                ->table('subscriptionManagement')
                ->where('subscribe_expire', '>=', $now);

            $stats['active'] = (clone $activeQuery)->count();

            $stats['expiring_7d'] = (clone $activeQuery)
                ->where('subscribe_expire', '<=', $in7d)
                ->count();

            $stats['expired'] = \DB::connection('mysql')
                ->table('subscriptionManagement')
                ->where('subscribe_expire', '>', 0)
                ->where('subscribe_expire', '<', $now)
                ->count();

            $stats['plan_basic'] = (clone $activeQuery)
                ->where('subscribe', 1)
                ->count();

            $stats['plan_pro'] = (clone $activeQuery)
                ->where('subscribe', 2)
                ->count();

            $stats['plan_unknown'] = (clone $activeQuery)
                ->whereNotIn('subscribe', [1, 2])
                ->count();

            $stats['active_mrr'] = ($stats['plan_basic'] * 1.0) + ($stats['plan_pro'] * 2.04);

            $stats['revenue_web'] = (float) \DB::connection('mysql')
                ->table('subscriptionHistory')
                ->where('subscribe_status', 1)
                ->sum('subscribe_price');

            $stats['revenue_app'] = (float) \DB::connection('mysql')
                ->table('subscriptionHistoryApp')
                ->where('subscribe_status', 1)
                ->sum('subscribe_price');

            $stats['revenue_total'] = $stats['revenue_web'] + $stats['revenue_app'];

            $stats['revenue_month'] = (float) \DB::connection('mysql')
                ->table('subscriptionHistory')
                ->where('subscribe_status', 1)
                ->whereRaw('YEAR(FROM_UNIXTIME(`time`)) = YEAR(CURDATE())')
                ->whereRaw('MONTH(FROM_UNIXTIME(`time`)) = MONTH(CURDATE())')
                ->sum('subscribe_price');

            $stats['revenue_month'] += (float) \DB::connection('mysql')
                ->table('subscriptionHistoryApp')
                ->where('subscribe_status', 1)
                ->whereRaw('YEAR(FROM_UNIXTIME(`time`)) = YEAR(CURDATE())')
                ->whereRaw('MONTH(FROM_UNIXTIME(`time`)) = MONTH(CURDATE())')
                ->sum('subscribe_price');

            $webSuccess = \DB::connection('mysql')
                ->table('subscriptionHistory')
                ->where('subscribe_status', 1)
                ->count();

            $appSuccess = \DB::connection('mysql')
                ->table('subscriptionHistoryApp')
                ->where('subscribe_status', 1)
                ->count();

            $stats['success_count'] = $webSuccess + $appSuccess;

            $webError = \DB::connection('mysql')
                ->table('subscriptionHistory')
                ->where('subscribe_status', 0)
                ->count();

            $appError = \DB::connection('mysql')
                ->table('subscriptionHistoryApp')
                ->where('subscribe_status', 0)
                ->count();

            $stats['error_count'] = $webError + $appError;

            $webCancel = \DB::connection('mysql')
                ->table('subscriptionHistory')
                ->where('subscribe_status', -1)
                ->count();

            $appCancel = \DB::connection('mysql')
                ->table('subscriptionHistoryApp')
                ->where('subscribe_status', -1)
                ->count();

            $stats['cancel_count'] = $webCancel + $appCancel;

            $webPending = \DB::connection('mysql')
                ->table('subscriptionHistory')
                ->where('subscribe_status', 2)
                ->count();

            $appPending = \DB::connection('mysql')
                ->table('subscriptionHistoryApp')
                ->where('subscribe_status', 2)
                ->count();

            $stats['pending_count'] = $webPending + $appPending;

            $activeWeb = \DB::connection('mysql')
                ->table('subscriptionHistory as sh')
                ->leftJoin('users as u', 'u.id', '=', 'sh.user_id')
                ->select(
                    'sh.id',
                    'sh.user_id',
                    'sh.subscribe',
                    'sh.subscribe_price',
                    'sh.subscribe_start',
                    'sh.subscribe_expire',
                    'u.username',
                    'u.name',
                    'u.image'
                )
                ->where('sh.subscribe_status', 1)
                ->where('sh.subscribe_expire', '>', $now)
                ->orderBy('sh.subscribe_expire')
                ->get()
                ->map(function ($row) {
                    $row->source = 'Web';
                    return $row;
                });

            $activeApp = \DB::connection('mysql')
                ->table('subscriptionHistoryApp as sh')
                ->leftJoin('users as u', 'u.id', '=', 'sh.user_id')
                ->select(
                    'sh.id',
                    'sh.user_id',
                    'sh.subscribe',
                    'sh.subscribe_price',
                    'sh.subscribe_start',
                    'sh.subscribe_expire',
                    'u.username',
                    'u.name',
                    'u.image'
                )
                ->where('sh.subscribe_status', 1)
                ->where('sh.subscribe_expire', '>', $now)
                ->orderBy('sh.subscribe_expire')
                ->get()
                ->map(function ($row) {
                    $row->source = 'App';
                    return $row;
                });

            $activeWebCount = $activeWeb->count();
            $activeAppCount = $activeApp->count();
            $activeSubscriptions = $activeWeb
                ->concat($activeApp)
                ->sortBy('subscribe_expire')
                ->values();

        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('subscription', [
            'stats' => $stats,
            'activeSubscriptions' => $activeSubscriptions,
            'activeWebCount' => $activeWebCount,
            'activeAppCount' => $activeAppCount,
            'error' => $error,
        ]);
    });

    Route::get('/visitors-analytics', function () {
        $error = null;
        $stats = [
            'web_total' => 0,
            'app_total' => 0,
            'total' => 0,
            'web_unique' => 0,
            'app_unique' => 0,
            'web_last24' => 0,
            'app_last24' => 0,
        ];
        $topPages = collect();
        $topScreens = collect();
        $topCountriesWeb = collect();
        $topCountriesApp = collect();

        try {
            $webBase = \DB::connection('tenant')->table('visitorBehaviorAnalytics');
            $appBase = \DB::connection('tenant')->table('visitorBehaviorAnalyticsApp');
            $now = time();
            $last24 = $now - 86400;

            $stats['web_total'] = (clone $webBase)->count();
            $stats['app_total'] = (clone $appBase)->count();
            $stats['total'] = $stats['web_total'] + $stats['app_total'];

            $stats['web_unique'] = (clone $webBase)
                ->whereNotNull('hash')
                ->where('hash', '!=', '')
                ->distinct()
                ->count('hash');
            $stats['app_unique'] = (clone $appBase)
                ->whereNotNull('hash')
                ->where('hash', '!=', '')
                ->distinct()
                ->count('hash');

            $stats['web_last24'] = (clone $webBase)
                ->where('time', '>=', $last24)
                ->count();
            $stats['app_last24'] = (clone $appBase)
                ->where('time', '>=', $last24)
                ->count();

            $topPages = (clone $webBase)
                ->select('recoveredPage', \DB::raw('COUNT(*) as count'))
                ->whereNotNull('recoveredPage')
                ->where('recoveredPage', '!=', '')
                ->groupBy('recoveredPage')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $topScreens = (clone $appBase)
                ->select('screen', \DB::raw('COUNT(*) as count'))
                ->whereNotNull('screen')
                ->where('screen', '!=', '')
                ->groupBy('screen')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $topCountriesWeb = (clone $webBase)
                ->select('country', \DB::raw('COUNT(*) as count'))
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $topCountriesApp = (clone $appBase)
                ->select('country', \DB::raw('COUNT(*) as count'))
                ->whereNotNull('country')
                ->where('country', '!=', '')
                ->groupBy('country')
                ->orderByDesc('count')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('visitors-analytics', [
            'stats' => $stats,
            'topPages' => $topPages,
            'topScreens' => $topScreens,
            'topCountriesWeb' => $topCountriesWeb,
            'topCountriesApp' => $topCountriesApp,
            'error' => $error,
        ]);
    });

    Route::get('/visitors-analytics-web', function () {
        $error = null;
        $rows = collect();
        $users = collect();

        try {
            $rows = \DB::connection('tenant')
                ->table('visitorBehaviorAnalytics')
                ->select(
                    'id',
                    'hash',
                    'ipAddress',
                    'user_id',
                    'recoveredPage',
                    'country',
                    'region',
                    'city',
                    'timezone',
                    'browserVersion',
                    'deviceName',
                    'operatingSystem',
                    'browserWindowWidth',
                    'browserLanguage',
                    'lengthStayOnPage',
                    'historyToPage',
                    'date',
                    'time'
                )
                ->orderByDesc('id')
                ->get();

            $userIds = $rows->pluck('user_id')->filter()->unique()->values();
            if ($userIds->isNotEmpty()) {
                $users = \DB::connection('mysql')
                    ->table('users')
                    ->select('id', 'username', 'name')
                    ->whereIn('id', $userIds)
                    ->get()
                    ->keyBy('id');
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('visitors-analytics-web', [
            'rows' => $rows,
            'users' => $users,
            'error' => $error,
        ]);
    });

    Route::get('/visitorBehaviorAnalytics_debut', function () {
        $error = null;
        $rows = collect();
        $users = collect();
        $limit = 10;

        try {
            $rows = \DB::connection('tenant')
                ->table('visitorBehaviorAnalytics')
                ->select(
                    'id',
                    'hash',
                    'ipAddress',
                    'user_id',
                    'recoveredPage',
                    'country',
                    'region',
                    'city',
                    'timezone',
                    'browserVersion',
                    'deviceName',
                    'operatingSystem',
                    'browserWindowWidth',
                    'browserLanguage',
                    'lengthStayOnPage',
                    'historyToPage',
                    'date',
                    'time'
                )
                ->orderByDesc('id')
                ->limit($limit)
                ->get();

            $userIds = $rows->pluck('user_id')->filter()->unique()->values();
            if ($userIds->isNotEmpty()) {
                $users = \DB::connection('mysql')
                    ->table('users')
                    ->select('id', 'username', 'name')
                    ->whereIn('id', $userIds)
                    ->get()
                    ->keyBy('id');
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('visitor-behavior-analytics-debut', [
            'rows' => $rows,
            'users' => $users,
            'limit' => $limit,
            'error' => $error,
        ]);
    });

    Route::get('/visitors-analytics-app', function () {
        $error = null;
        $rows = collect();
        $users = collect();

        try {
            $rows = \DB::connection('tenant')
                ->table('visitorBehaviorAnalyticsApp')
                ->select(
                    'id',
                    'hash',
                    'ipAddress',
                    'user_id',
                    'screen',
                    'country',
                    'region',
                    'city',
                    'timezone',
                    'osVersion',
                    'deviceName',
                    'operatingSystem',
                    'windowWidth',
                    'language',
                    'lengthStayOnScreen',
                    'lastScreen',
                    'version',
                    'date',
                    'time'
                )
                ->orderByDesc('id')
                ->get();

            $userIds = $rows->pluck('user_id')->filter()->unique()->values();
            if ($userIds->isNotEmpty()) {
                $users = \DB::connection('mysql')
                    ->table('users')
                    ->select('id', 'username', 'name')
                    ->whereIn('id', $userIds)
                    ->get()
                    ->keyBy('id');
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('visitors-analytics-app', [
            'rows' => $rows,
            'users' => $users,
            'error' => $error,
        ]);
    });

    Route::get('/visitors-analytics-clicks', function () {
        $error = null;
        $rows = collect();
        $users = collect();
        $totalClicks = 0;
        $uniqueUsers = 0;
        $uniqueIps = 0;

        try {
            $rows = \DB::connection('tenant')
                ->table('clickOfApp')
                ->select('id', 'ip', 'userId', 'timedate')
                ->orderByDesc('id')
                ->get();

            $totalClicks = $rows->count();
            $uniqueUsers = $rows->pluck('userId')->filter()->unique()->count();
            $uniqueIps = $rows->pluck('ip')->filter()->unique()->count();

            $userIds = $rows->pluck('userId')->filter()->unique()->values();
            if ($userIds->isNotEmpty()) {
                $users = \DB::connection('mysql')
                    ->table('users')
                    ->select('id', 'username', 'name')
                    ->whereIn('id', $userIds)
                    ->get()
                    ->keyBy('id');
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('visitors-analytics-clicks', [
            'rows' => $rows,
            'users' => $users,
            'totalClicks' => $totalClicks,
            'uniqueUsers' => $uniqueUsers,
            'uniqueIps' => $uniqueIps,
            'error' => $error,
        ]);
    });

    Route::get('/subscription-history', function () {
        $error = null;
        $rows = collect();
        $users = collect();

        try {
            $rows = \DB::connection('mysql')
                ->table('subscriptionHistory')
                ->select('id', 'user_id', 'subscribe', 'subscribe_price', 'subscribe_status', 'subscribe_start', 'subscribe_expire', 'time')
                ->orderByDesc('id')
                ->get();

            $userIds = $rows->pluck('user_id')->filter()->unique()->values();
            if ($userIds->isNotEmpty()) {
                $users = \DB::connection('mysql')
                    ->table('users')
                    ->select('id', 'username', 'name')
                    ->whereIn('id', $userIds)
                    ->get()
                    ->keyBy('id');
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('subscription-history', [
            'rows' => $rows,
            'users' => $users,
            'error' => $error,
        ]);
    });

    Route::get('/subscription-history-app', function () {
        $error = null;
        $rows = collect();
        $users = collect();

        try {
            $rows = \DB::connection('mysql')
                ->table('subscriptionHistoryApp')
                ->select('id', 'user_id', 'subscribe', 'subscribe_price', 'subscribe_status', 'subscribe_start', 'subscribe_expire', 'time')
                ->orderByDesc('id')
                ->get();

            $userIds = $rows->pluck('user_id')->filter()->unique()->values();
            if ($userIds->isNotEmpty()) {
                $users = \DB::connection('mysql')
                    ->table('users')
                    ->select('id', 'username', 'name')
                    ->whereIn('id', $userIds)
                    ->get()
                    ->keyBy('id');
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('subscription-history-app', [
            'rows' => $rows,
            'users' => $users,
            'error' => $error,
        ]);
    });

    Route::get('/subscription-management', function () {
        $error = null;
        $rows = collect();
        $users = collect();

        try {
            $rows = \DB::connection('mysql')
                ->table('subscriptionManagement')
                ->select('id', 'user_id', 'subscribe', 'subscribe_start', 'subscribe_expire')
                ->orderByDesc('id')
                ->get();

            $userIds = $rows->pluck('user_id')->filter()->unique()->values();
            if ($userIds->isNotEmpty()) {
                $users = \DB::connection('mysql')
                    ->table('users')
                    ->select('id', 'username', 'name')
                    ->whereIn('id', $userIds)
                    ->get()
                    ->keyBy('id');
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('subscription-management', [
            'rows' => $rows,
            'users' => $users,
            'error' => $error,
        ]);
    });

    Route::get('/check-users-image', function () {
        $error = null;
        $users = collect();

        try {
            $users = \DB::table('users')
                ->select('id', 'name', 'username', 'image', 'time')
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->where('image', '!=', 'default.png')
                ->orderByDesc('time')
                ->get();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('check-users-image', [
            'users' => $users,
            'error' => $error,
        ]);
    });

    Route::get('/users/{id}', function (int $id) {
        $error = null;
        $user = null;

        try {
            $user = \DB::table('users')
                ->select('id', 'name', 'surname', 'username', 'email', 'level', 'image', 'bio', 'verified', 'byGoogle', 'appleUser', 'profileAccess', 'time')
                ->selectRaw('FROM_UNIXTIME(`time`) as time_label')
                ->where('id', $id)
                ->first();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        if (!$user && !$error) {
            abort(404);
        }

        return view('user-detail', [
            'user' => $user,
            'error' => $error,
        ]);
    })->where('id', '[0-9]+');

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
        $summary = [
            'categories' => 0,
            'lessons' => 0,
            'carousel' => 0,
            'tests' => 0,
        ];

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

            $summary['categories'] = $categories->count();
            $summary['lessons'] = $lessonsByCategory->sum(function ($lessons) {
                return $lessons->count();
            });
            $summary['carousel'] = (int) $carouselCountsByLesson->sum();
            $summary['tests'] = (int) $testCountsByLesson->sum();
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
            'summary' => $summary,
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
        $error = null;
        $summary = [
            'books' => 0,
            'categories' => 0,
            'withImages' => 0,
            'withAudio' => 0,
            'withSubtitles' => 0,
            'bookmarksTotal' => 0,
            'readsTotal' => 0,
            'readerUsers' => 0,
            'bookmarkUsers' => 0,
        ];
        $categoryStats = collect();
        $topBookmarked = collect();
        $topRead = collect();

        try {
            $categories = \DB::connection('tenant')
                ->table('read_books_categories')
                ->select('id', 'title', 'code', 'time')
                ->orderBy('id')
                ->get();

            $summary['categories'] = $categories->count();
            $summary['books'] = \DB::connection('tenant')->table('read_books')->count();
            $summary['authors'] = \DB::connection('tenant')
                ->table('read_books')
                ->whereNotNull('author')
                ->where('author', '!=', '')
                ->distinct()
                ->count('author');
            $summary['withAudio'] = \DB::connection('tenant')
                ->table('read_books')
                ->whereNotNull('audio_file')
                ->where('audio_file', '!=', '')
                ->count();
            $summary['withSubtitles'] = \DB::connection('tenant')
                ->table('read_books')
                ->whereNotNull('subtitles_json')
                ->where('subtitles_json', '!=', '')
                ->count();
            $summary['bookmarksTotal'] = \DB::connection('tenant')->table('read_books_user_bookmarks')->count();
            $summary['readsTotal'] = \DB::connection('tenant')->table('read_books_user_mark')->count();
            $summary['bookmarkUsers'] = \DB::connection('tenant')->table('read_books_user_bookmarks')->distinct()->count('user_id');
            $summary['readerUsers'] = \DB::connection('tenant')->table('read_books_user_mark')->distinct()->count('user_id');

            $booksByCategory = \DB::connection('tenant')
                ->table('read_books')
                ->select('id', 'category')
                ->get()
                ->groupBy('category');

            $bookmarkCountsByBook = \DB::connection('tenant')
                ->table('read_books_user_bookmarks')
                ->select('bookmark_id', \DB::raw('COUNT(*) as count'))
                ->groupBy('bookmark_id')
                ->pluck('count', 'bookmark_id');

            $readCountsByBook = \DB::connection('tenant')
                ->table('read_books_user_mark')
                ->select('mark_id', \DB::raw('COUNT(*) as count'))
                ->groupBy('mark_id')
                ->pluck('count', 'mark_id');

            $categoryStats = $categories->map(function ($category) use ($booksByCategory, $bookmarkCountsByBook, $readCountsByBook) {
                $bookIds = $booksByCategory->get($category->code, collect())->pluck('id');
                $bookCount = $bookIds->count();
                $bookmarkCount = $bookIds->sum(function ($id) use ($bookmarkCountsByBook) {
                    return (int) $bookmarkCountsByBook->get($id, 0);
                });
                $readCount = $bookIds->sum(function ($id) use ($readCountsByBook) {
                    return (int) $readCountsByBook->get($id, 0);
                });

                return (object) [
                    'title' => $category->title,
                    'code' => $category->code,
                    'bookCount' => $bookCount,
                    'bookmarkCount' => $bookmarkCount,
                    'readCount' => $readCount,
                    'time' => $category->time,
                ];
            });

            $topBookmarked = \DB::connection('tenant')
                ->table('read_books_user_bookmarks as rbub')
                ->join('read_books as rb', 'rb.id', '=', 'rbub.bookmark_id')
                ->select('rb.id', 'rb.title', 'rb.url', \DB::raw('COUNT(rbub.id) as count'))
                ->groupBy('rb.id', 'rb.title', 'rb.url')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $topRead = \DB::connection('tenant')
                ->table('read_books_user_mark as rbum')
                ->join('read_books as rb', 'rb.id', '=', 'rbum.mark_id')
                ->select('rb.id', 'rb.title', 'rb.url', \DB::raw('COUNT(rbum.id) as count'))
                ->groupBy('rb.id', 'rb.title', 'rb.url')
                ->orderByDesc('count')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('books', [
            'summary' => $summary,
            'categoryStats' => $categoryStats,
            'topBookmarked' => $topBookmarked,
            'topRead' => $topRead,
            'error' => $error,
        ]);
    });

    Route::get('/poetry', function () {
        $error = null;
        $summary = [
            'books' => 0,
            'categories' => 0,
            'authors' => 0,
            'withAudio' => 0,
            'withSubtitles' => 0,
            'bookmarksTotal' => 0,
            'readsTotal' => 0,
            'readerUsers' => 0,
            'bookmarkUsers' => 0,
        ];
        $categoryStats = collect();
        $topBookmarked = collect();
        $topRead = collect();

        try {
            $categories = \DB::connection('tenant')
                ->table('read_poetry_categories')
                ->select('id', 'title', 'code', 'time')
                ->orderBy('id')
                ->get();

            $summary['categories'] = $categories->count();
            $summary['books'] = \DB::connection('tenant')->table('read_poetry')->count();
            $summary['authors'] = \DB::connection('tenant')
                ->table('read_poetry')
                ->whereNotNull('author')
                ->where('author', '!=', '')
                ->distinct()
                ->count('author');
            $summary['withAudio'] = \DB::connection('tenant')
                ->table('read_poetry')
                ->whereNotNull('audio_file')
                ->where('audio_file', '!=', '')
                ->count();
            $summary['withSubtitles'] = \DB::connection('tenant')
                ->table('read_poetry')
                ->whereNotNull('subtitles_json')
                ->where('subtitles_json', '!=', '')
                ->count();
            $summary['bookmarksTotal'] = \DB::connection('tenant')->table('read_poetry_user_bookmarks')->count();
            $summary['readsTotal'] = \DB::connection('tenant')->table('read_poetry_user_mark')->count();
            $summary['bookmarkUsers'] = \DB::connection('tenant')->table('read_poetry_user_bookmarks')->distinct()->count('user_id');
            $summary['readerUsers'] = \DB::connection('tenant')->table('read_poetry_user_mark')->distinct()->count('user_id');

            $booksByCategory = \DB::connection('tenant')
                ->table('read_poetry')
                ->select('id', 'category')
                ->get()
                ->groupBy('category');

            $bookmarkCountsByBook = \DB::connection('tenant')
                ->table('read_poetry_user_bookmarks')
                ->select('bookmark_id', \DB::raw('COUNT(*) as count'))
                ->groupBy('bookmark_id')
                ->pluck('count', 'bookmark_id');

            $readCountsByBook = \DB::connection('tenant')
                ->table('read_poetry_user_mark')
                ->select('mark_id', \DB::raw('COUNT(*) as count'))
                ->groupBy('mark_id')
                ->pluck('count', 'mark_id');

            $categoryStats = $categories->map(function ($category) use ($booksByCategory, $bookmarkCountsByBook, $readCountsByBook) {
                $bookIds = $booksByCategory->get($category->code, collect())->pluck('id');
                $bookCount = $bookIds->count();
                $bookmarkCount = $bookIds->sum(function ($id) use ($bookmarkCountsByBook) {
                    return (int) $bookmarkCountsByBook->get($id, 0);
                });
                $readCount = $bookIds->sum(function ($id) use ($readCountsByBook) {
                    return (int) $readCountsByBook->get($id, 0);
                });

                return (object) [
                    'title' => $category->title,
                    'code' => $category->code,
                    'bookCount' => $bookCount,
                    'bookmarkCount' => $bookmarkCount,
                    'readCount' => $readCount,
                    'time' => $category->time,
                ];
            });

            $topBookmarked = \DB::connection('tenant')
                ->table('read_poetry_user_bookmarks as rpub')
                ->join('read_poetry as rp', 'rp.id', '=', 'rpub.bookmark_id')
                ->select('rp.id', 'rp.title', 'rp.url', \DB::raw('COUNT(rpub.id) as count'))
                ->groupBy('rp.id', 'rp.title', 'rp.url')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $topRead = \DB::connection('tenant')
                ->table('read_poetry_user_mark as rpum')
                ->join('read_poetry as rp', 'rp.id', '=', 'rpum.mark_id')
                ->select('rp.id', 'rp.title', 'rp.url', \DB::raw('COUNT(rpum.id) as count'))
                ->groupBy('rp.id', 'rp.title', 'rp.url')
                ->orderByDesc('count')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('poetry', [
            'summary' => $summary,
            'categoryStats' => $categoryStats,
            'topBookmarked' => $topBookmarked,
            'topRead' => $topRead,
            'error' => $error,
        ]);
    });

    Route::get('/dialog', function () {
        $error = null;
        $summary = [
            'books' => 0,
            'categories' => 0,
            'authors' => 0,
            'withAudio' => 0,
            'withSubtitles' => 0,
            'bookmarksTotal' => 0,
            'readsTotal' => 0,
            'readerUsers' => 0,
            'bookmarkUsers' => 0,
        ];
        $categoryStats = collect();
        $topBookmarked = collect();
        $topRead = collect();

        try {
            $categories = \DB::connection('tenant')
                ->table('read_dialog_categories')
                ->select('id', 'title', 'code', 'time')
                ->orderBy('id')
                ->get();

            $summary['categories'] = $categories->count();
            $summary['books'] = \DB::connection('tenant')->table('read_dialog')->count();
            $summary['withImages'] = \DB::connection('tenant')
                ->table('read_dialog')
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->count();
            $summary['withAudio'] = \DB::connection('tenant')
                ->table('read_dialog')
                ->whereNotNull('audio_file')
                ->where('audio_file', '!=', '')
                ->count();
            $summary['withSubtitles'] = \DB::connection('tenant')
                ->table('read_dialog')
                ->whereNotNull('subtitles_json')
                ->where('subtitles_json', '!=', '')
                ->count();
            $summary['bookmarksTotal'] = \DB::connection('tenant')->table('read_dialog_user_bookmarks')->count();
            $summary['readsTotal'] = \DB::connection('tenant')->table('read_dialog_user_mark')->count();
            $summary['bookmarkUsers'] = \DB::connection('tenant')->table('read_dialog_user_bookmarks')->distinct()->count('user_id');
            $summary['readerUsers'] = \DB::connection('tenant')->table('read_dialog_user_mark')->distinct()->count('user_id');

            $booksByCategory = \DB::connection('tenant')
                ->table('read_dialog')
                ->select('id', 'category')
                ->get()
                ->groupBy('category');

            $bookmarkCountsByBook = \DB::connection('tenant')
                ->table('read_dialog_user_bookmarks')
                ->select('bookmark_id', \DB::raw('COUNT(*) as count'))
                ->groupBy('bookmark_id')
                ->pluck('count', 'bookmark_id');

            $readCountsByBook = \DB::connection('tenant')
                ->table('read_dialog_user_mark')
                ->select('mark_id', \DB::raw('COUNT(*) as count'))
                ->groupBy('mark_id')
                ->pluck('count', 'mark_id');

            $categoryStats = $categories->map(function ($category) use ($booksByCategory, $bookmarkCountsByBook, $readCountsByBook) {
                $bookIds = $booksByCategory->get($category->code, collect())->pluck('id');
                $bookCount = $bookIds->count();
                $bookmarkCount = $bookIds->sum(function ($id) use ($bookmarkCountsByBook) {
                    return (int) $bookmarkCountsByBook->get($id, 0);
                });
                $readCount = $bookIds->sum(function ($id) use ($readCountsByBook) {
                    return (int) $readCountsByBook->get($id, 0);
                });

                return (object) [
                    'title' => $category->title,
                    'code' => $category->code,
                    'bookCount' => $bookCount,
                    'bookmarkCount' => $bookmarkCount,
                    'readCount' => $readCount,
                    'time' => $category->time,
                ];
            });

            $topBookmarked = \DB::connection('tenant')
                ->table('read_dialog_user_bookmarks as rdub')
                ->join('read_dialog as rd', 'rd.id', '=', 'rdub.bookmark_id')
                ->select('rd.id', 'rd.title', 'rd.url', \DB::raw('COUNT(rdub.id) as count'))
                ->groupBy('rd.id', 'rd.title', 'rd.url')
                ->orderByDesc('count')
                ->limit(10)
                ->get();

            $topRead = \DB::connection('tenant')
                ->table('read_dialog_user_mark as rdum')
                ->join('read_dialog as rd', 'rd.id', '=', 'rdum.mark_id')
                ->select('rd.id', 'rd.title', 'rd.url', \DB::raw('COUNT(rdum.id) as count'))
                ->groupBy('rd.id', 'rd.title', 'rd.url')
                ->orderByDesc('count')
                ->limit(10)
                ->get();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('dialog', [
            'summary' => $summary,
            'categoryStats' => $categoryStats,
            'topBookmarked' => $topBookmarked,
            'topRead' => $topRead,
            'error' => $error,
        ]);
    });

    Route::get('/games', function () {
        return view('games');
    });

    Route::get('/games-table-name', function () {
        return view('games-table-name');
    });

    Route::get('/games-rules-display', function () {
        return view('games-rules-display');
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
