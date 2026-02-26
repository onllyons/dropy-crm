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
}
