<?php

namespace App\Http\Controllers;

use App\Services\VisitorsAnalyticsGroupedUrlService;
use Illuminate\Http\Request;

class VisitorsAnalyticsGroupedUrlController extends Controller
{
    public function index(Request $request, VisitorsAnalyticsGroupedUrlService $service)
    {
        $sourceOptions = [
            'web' => 'Web table: visitorBehaviorAnalytics',
            'app' => 'Mobile table: visitorBehaviorAnalyticsApp',
        ];

        $source = strtolower(trim((string) $request->query('source', 'web')));
        if (!array_key_exists($source, $sourceOptions)) {
            $source = 'web';
        }

        $query = trim((string) $request->query('q', ''));
        $result = null;
        $error = null;

        if ($request->has('source') || $request->has('q')) {
            if ($query === '') {
                $error = 'Please type a value to search.';
            } else {
                try {
                    $result = $service->search($source, $query);
                } catch (\Throwable $e) {
                    $error = $e->getMessage();
                }
            }
        }

        return view('visitors-analytics-grouped-url', [
            'source' => $source,
            'sourceOptions' => $sourceOptions,
            'query' => $query,
            'result' => $result,
            'error' => $error,
        ]);
    }

    public function detail(Request $request, VisitorsAnalyticsGroupedUrlService $service)
    {
        $source = strtolower(trim((string) $request->query('source', 'web')));
        if (!in_array($source, ['web', 'app'], true)) {
            $source = 'web';
        }

        $value = trim((string) $request->query('value', ''));
        if ($value === '') {
            return redirect()
                ->route('visitors-analytics-grouped-url')
                ->with('error', 'Missing grouped value.');
        }

        $perPageOptions = [50, 100, 250, 500];
        $perPage = (int) $request->query('per_page', 100);
        if (!in_array($perPage, $perPageOptions, true)) {
            $perPage = 100;
        }

        $error = null;
        $detail = null;

        try {
            $detail = $service->detail($source, $value, $perPage);
            $detail['rows']->appends([
                'source' => $source,
                'value' => $value,
                'per_page' => $perPage,
            ]);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('visitors-analytics-grouped-url-detail', [
            'source' => $source,
            'value' => $value,
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
            'detail' => $detail,
            'error' => $error,
        ]);
    }
}
