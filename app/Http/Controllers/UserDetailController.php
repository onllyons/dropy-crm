<?php

namespace App\Http\Controllers;

use App\Services\UserDetailService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserDetailController extends Controller
{
    public function show(Request $request, int $id, UserDetailService $service): View
    {
        $cooldownFilter = strtolower(trim((string) $request->query('cooldown_filter', 'all')));
        $data = $service->getPageData($id, $cooldownFilter);

        if (!$data['user'] && !$data['error']) {
            abort(404);
        }

        return view('user-detail', $data);
    }
}
