<?php

namespace App\Http\Controllers;

use App\Services\DropyAppService;
use Illuminate\Http\Request;

class DropyAppController extends Controller
{
    public function index(DropyAppService $service)
    {
        $clickStats = [
            'today_date' => now()->toDateString(),
            'today_rows' => 0,
            'total_rows' => 0,
        ];
        $clickStatsError = null;
        $emailActiveClickGroups = collect();
        $emailActiveClickGroupsError = null;
        $prMapSchema = [
            'table_exists' => false,
            'columns' => [],
        ];
        $prMapRows = collect();
        $prMapRowsError = null;
        $emailActiveClickPrGroups = collect();
        $emailActiveClickPrGroupsError = null;

        try {
            $clickStats = $service->getDownloadClickStats();
        } catch (\Throwable $e) {
            $clickStatsError = $e->getMessage();
        }

        try {
            $emailActiveClickGroups = $service->getEmailActiveClickGroupedUrls();
        } catch (\Throwable $e) {
            $emailActiveClickGroupsError = $e->getMessage();
        }

        try {
            $prMapSchema = $service->getBloggerPrMapSchema();
        } catch (\Throwable $e) {
            $prMapRowsError = $e->getMessage();
        }

        try {
            $prMapRows = $service->getBloggerPrMapRows();
        } catch (\Throwable $e) {
            $prMapRowsError = $e->getMessage();
        }

        try {
            $emailActiveClickPrGroups = $service->getEmailActiveClickGroupedByPrCode();
        } catch (\Throwable $e) {
            $emailActiveClickPrGroupsError = $e->getMessage();
        }

        return view('app.dropy-app', [
            'clickStats' => $clickStats,
            'clickStatsError' => $clickStatsError,
            'emailActiveClickGroups' => $emailActiveClickGroups,
            'emailActiveClickGroupsError' => $emailActiveClickGroupsError,
            'prMapSchema' => $prMapSchema,
            'prMapRows' => $prMapRows,
            'prMapRowsError' => $prMapRowsError,
            'emailActiveClickPrGroups' => $emailActiveClickPrGroups,
            'emailActiveClickPrGroupsError' => $emailActiveClickPrGroupsError,
        ]);
    }

    public function storePrMap(Request $request, DropyAppService $service)
    {
        $payload = $this->validatedPrMapPayload($request);

        try {
            $service->createBloggerPrMap($payload);
            return back()->with('status', 'PR mapping added.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function updatePrMap(Request $request, int $id, DropyAppService $service)
    {
        $payload = $this->validatedPrMapPayload($request);

        try {
            $service->updateBloggerPrMap($id, $payload);
            return back()->with('status', 'PR mapping updated.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroyPrMap(int $id, DropyAppService $service)
    {
        try {
            $service->deleteBloggerPrMap($id);
            return back()->with('status', 'PR mapping deleted.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function validatedPrMapPayload(Request $request): array
    {
        return $request->validate(
            [
                'pr_code' => ['required', 'regex:/^[1-9][0-9]{0,5}$/'],
                'blogger_name' => ['required', 'string', 'max:191'],
                'campaign_name' => ['nullable', 'string', 'max:191'],
                'platform' => ['nullable', 'string', 'max:50'],
                'profile_url' => ['nullable', 'string', 'max:500'],
                'links_json' => ['nullable', 'string'],
                'notes' => ['nullable', 'string'],
                'is_active' => ['nullable', 'boolean'],
            ],
            [
                'pr_code.required' => 'PR code is required.',
                'pr_code.regex' => 'PR code must be numeric, from 1 to 999999, without leading zero.',
            ]
        );
    }
}
