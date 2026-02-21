<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AllLessonsController extends Controller
{
    public function content()
    {
        try {
            $rows = DB::connection('tenant')
                ->table('category_course as cc')
                ->join('course as c', 'cc.var_idtest_1_1', '=', 'c.category_url')
                ->select(
                    'cc.var_idtest_1',
                    'cc.var_idtest_1_1',
                    'cc.bgApp',
                    'cc.bgShadowApp',
                    'c.category_url',
                    'c.title',
                    'c.url',
                    DB::raw('c.id as courseId')
                )
                ->orderBy('cc.id')
                ->orderBy('c.id')
                ->get();

            $authUser = Auth::user();
            $authUserId = (int) ($authUser->user_id ?? $authUser->id ?? 0);
            $finishedCourseMap = [];

            if ($authUserId > 0) {
                $courseIds = $rows->pluck('courseId')->filter()->unique()->values();
                if ($courseIds->isNotEmpty()) {
                    $finishedCourseMap = DB::connection('tenant')
                        ->table('course_history')
                        ->where('user_id', $authUserId)
                        ->where('end_time', '>', 0)
                        ->whereIn('course_id', $courseIds)
                        ->distinct()
                        ->pluck('course_id')
                        ->map(function ($id) {
                            return (int) $id;
                        })
                        ->flip()
                        ->all();
                }
            }

            $courses = [];
            foreach ($rows as $row) {
                $categoryName = trim((string) ($row->var_idtest_1 ?? ''));
                if ($categoryName === '') {
                    $categoryName = 'Unknown';
                }

                if (!isset($courses[$categoryName])) {
                    $courses[$categoryName] = [
                        'var_idtest_1_1' => (string) ($row->var_idtest_1_1 ?? ''),
                        'bgApp' => (string) ($row->bgApp ?? ''),
                        'bgShadowApp' => (string) ($row->bgShadowApp ?? ''),
                        'titles' => [],
                    ];
                }

                $courseId = (int) ($row->courseId ?? 0);
                $courses[$categoryName]['titles'][] = [
                    'title' => (string) ($row->title ?? ''),
                    'url' => (string) ($row->url ?? ''),
                    'isFinished' => isset($finishedCourseMap[$courseId]),
                ];
            }

            return response()->json([
                'success' => true,
                'courses' => (object) $courses,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'courses' => new \stdClass(),
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
