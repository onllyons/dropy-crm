<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
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
            $categories = DB::connection('tenant')
                ->table('category_course')
                ->select('id', 'var_idtest_1', 'var_idtest_1_1', 'var_idtest_3')
                ->orderBy('var_idtest_3')
                ->orderBy('var_idtest_1')
                ->get();

            $lessonsByCategory = DB::connection('tenant')
                ->table('course')
                ->select('id', 'category_url', 'url', 'title')
                ->orderBy('category_url')
                ->orderBy('title')
                ->get()
                ->groupBy('category_url');

            $carouselCounts = DB::connection('tenant')
                ->table('course_carousel as cc')
                ->join('course as c', 'c.url', '=', 'cc.course_url')
                ->select('c.category_url', DB::raw('COUNT(cc.id) as count'))
                ->groupBy('c.category_url')
                ->pluck('count', 'c.category_url');

            $testCounts = DB::connection('tenant')
                ->table('course_test as ct')
                ->join('course as c', 'c.url', '=', 'ct.course_url')
                ->select('c.category_url', DB::raw('COUNT(ct.id) as count'))
                ->groupBy('c.category_url')
                ->pluck('count', 'c.category_url');

            $carouselCountsByLesson = DB::connection('tenant')
                ->table('course_carousel')
                ->select('course_url', DB::raw('COUNT(*) as count'))
                ->groupBy('course_url')
                ->pluck('count', 'course_url');

            $testCountsByLesson = DB::connection('tenant')
                ->table('course_test')
                ->select('course_url', DB::raw('COUNT(*) as count'))
                ->groupBy('course_url')
                ->pluck('count', 'course_url');

            $carouselSeriesByLesson = DB::connection('tenant')
                ->table('course_carousel')
                ->select('course_url', 'series', DB::raw('COUNT(*) as count'))
                ->groupBy('course_url', 'series')
                ->orderBy('series')
                ->get()
                ->groupBy('course_url');

            $testSeriesByLesson = DB::connection('tenant')
                ->table('course_test')
                ->select('course_url', 'series', DB::raw('COUNT(*) as count'))
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
    }

    public function updateCourse(Request $request, int $id): RedirectResponse
    {
        try {
            $exists = DB::connection('tenant')
                ->table('course')
                ->where('id', $id)
                ->exists();

            if (!$exists) {
                return back()->with('error', "Course row with id {$id} was not found.");
            }

            $payload = $this->buildUpdatePayload($request, 'tenant', 'course', ['id']);
            if ($payload === []) {
                return back()->with('error', 'No editable course fields were provided.');
            }

            DB::connection('tenant')
                ->table('course')
                ->where('id', $id)
                ->update($payload);

            return back()->with('status', "Course #{$id} updated.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function updateCourseTest(Request $request, int $id): RedirectResponse
    {
        try {
            $exists = DB::connection('tenant')
                ->table('course_test')
                ->where('id', $id)
                ->exists();

            if (!$exists) {
                return back()->with('error', "course_test row with id {$id} was not found.");
            }

            $payload = $this->buildUpdatePayload($request, 'tenant', 'course_test', ['id']);
            if ($payload === []) {
                return back()->with('error', 'No editable course_test fields were provided.');
            }

            DB::connection('tenant')
                ->table('course_test')
                ->where('id', $id)
                ->update($payload);

            return back()->with('status', "course_test #{$id} updated.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function buildUpdatePayload(
        Request $request,
        string $connection,
        string $table,
        array $blockedColumns = []
    ): array {
        $columns = Schema::connection($connection)->getColumnListing($table);
        $allowedColumns = array_values(array_diff($columns, $blockedColumns));

        $payload = [];
        foreach ($allowedColumns as $column) {
            if (!$request->exists($column)) {
                continue;
            }

            $value = $request->input($column);
            if (is_array($value) || is_object($value)) {
                $value = json_encode($value);
            }

            $payload[$column] = $value;
        }

        return $payload;
    }
}
