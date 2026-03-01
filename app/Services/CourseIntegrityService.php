<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CourseIntegrityService
{
    public function buildReport(): array
    {
        $mediaBase = DB::connection('tenant')
            ->table('course_carousel')
            ->whereIn('variant', ['v', 'a']);

        $videoBase = DB::connection('tenant')
            ->table('course_carousel')
            ->where('variant', 'v');

        $audioBase = DB::connection('tenant')
            ->table('course_carousel')
            ->where('variant', 'a');

        $videoMissingPathCount = (clone $videoBase)
            ->where(function ($query) {
                $query->whereNull('file_path')
                    ->orWhereRaw("TRIM(file_path) = ''");
            })
            ->count();

        $audioMissingPathCount = (clone $audioBase)
            ->where(function ($query) {
                $query->whereNull('file_path')
                    ->orWhereRaw("TRIM(file_path) = ''");
            })
            ->count();

        $videoInvalidPathCount = (clone $videoBase)
            ->whereRaw("TRIM(COALESCE(file_path, '')) <> ''")
            ->where('file_path', 'not like', '%/course/video-lessons/%')
            ->count();

        $audioInvalidPathCount = (clone $audioBase)
            ->whereRaw("TRIM(COALESCE(file_path, '')) <> ''")
            ->where('file_path', 'not like', '%/course/audio-lessons/%')
            ->count();

        $mediaPathProblems = (clone $mediaBase)
            ->select(
                'id',
                'course_url',
                'series',
                'variant',
                'file_path',
                DB::raw("
                    CASE
                        WHEN TRIM(COALESCE(file_path, '')) = '' THEN 'missing_file_path'
                        WHEN variant = 'v' AND file_path NOT LIKE '%/course/video-lessons/%' THEN 'invalid_video_path'
                        WHEN variant = 'a' AND file_path NOT LIKE '%/course/audio-lessons/%' THEN 'invalid_audio_path'
                        ELSE 'ok'
                    END AS issue
                ")
            )
            ->where(function ($query) {
                $query->whereRaw("TRIM(COALESCE(file_path, '')) = ''")
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('variant', 'v')
                            ->whereRaw("TRIM(COALESCE(file_path, '')) <> ''")
                            ->where('file_path', 'not like', '%/course/video-lessons/%');
                    })
                    ->orWhere(function ($subQuery) {
                        $subQuery->where('variant', 'a')
                            ->whereRaw("TRIM(COALESCE(file_path, '')) <> ''")
                            ->where('file_path', 'not like', '%/course/audio-lessons/%');
                    });
            })
            ->orderBy('variant')
            ->orderBy('course_url')
            ->orderBy('id')
            ->get();

        $lessonsBase = DB::connection('tenant')
            ->table('course as c')
            ->leftJoin('category_course as cc', 'cc.var_idtest_1_1', '=', 'c.category_url')
            ->where(function ($query) {
                $query->whereNull('c.category_url')
                    ->orWhere('c.category_url', '')
                    ->orWhereNull('cc.id');
            });

        $lessonsWithoutCategory = (clone $lessonsBase)
            ->select('c.id', 'c.category_url', 'c.url', 'c.title')
            ->orderBy('c.category_url')
            ->orderBy('c.id')
            ->get();

        $carouselBase = DB::connection('tenant')
            ->table('course_carousel as car')
            ->leftJoin('course as c', 'c.url', '=', 'car.course_url')
            ->where(function ($query) {
                $query->whereNull('car.course_url')
                    ->orWhere('car.course_url', '')
                    ->orWhereNull('c.id');
            });

        $carouselWithoutLesson = (clone $carouselBase)
            ->select('car.id', 'car.course_url', 'car.series', 'car.base_title')
            ->orderBy('car.course_url')
            ->orderBy('car.id')
            ->get();

        $testsBase = DB::connection('tenant')
            ->table('course_test as t')
            ->leftJoin('course as c', 'c.url', '=', 't.course_url')
            ->where(function ($query) {
                $query->whereNull('t.course_url')
                    ->orWhere('t.course_url', '')
                    ->orWhereNull('c.id');
            });

        $testsWithoutLesson = (clone $testsBase)
            ->select('t.id', 't.course_url', 't.series', 't.v1', 't.correct', 't.variant')
            ->orderBy('t.course_url')
            ->orderBy('t.id')
            ->get();

        $duplicateLessonUrls = DB::connection('tenant')
            ->table('course as c')
            ->select('c.url', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('c.url')
            ->where('c.url', '!=', '')
            ->groupBy('c.url')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('cnt')
            ->orderBy('c.url')
            ->get();

        return [
            'videoRowsCount' => (clone $videoBase)->count(),
            'audioRowsCount' => (clone $audioBase)->count(),
            'videoMissingPathCount' => (int) $videoMissingPathCount,
            'audioMissingPathCount' => (int) $audioMissingPathCount,
            'videoInvalidPathCount' => (int) $videoInvalidPathCount,
            'audioInvalidPathCount' => (int) $audioInvalidPathCount,
            'mediaPathProblems' => $mediaPathProblems,
            'mediaPathProblemsCount' => $mediaPathProblems->count(),
            'lessonsWithoutCategory' => $lessonsWithoutCategory,
            'lessonsWithoutCategoryCount' => $lessonsWithoutCategory->count(),
            'carouselWithoutLesson' => $carouselWithoutLesson,
            'carouselWithoutLessonCount' => $carouselWithoutLesson->count(),
            'testsWithoutLesson' => $testsWithoutLesson,
            'testsWithoutLessonCount' => $testsWithoutLesson->count(),
            'duplicateLessonUrls' => $duplicateLessonUrls,
            'duplicateLessonUrlsCount' => $duplicateLessonUrls->count(),
        ];
    }
}
