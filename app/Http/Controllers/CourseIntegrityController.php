<?php

namespace App\Http\Controllers;

use App\Services\CourseIntegrityService;
use Illuminate\View\View;

class CourseIntegrityController extends Controller
{
    public function index(CourseIntegrityService $service): View
    {
        $error = null;
        $videoRowsCount = 0;
        $audioRowsCount = 0;
        $videoMissingPathCount = 0;
        $audioMissingPathCount = 0;
        $videoInvalidPathCount = 0;
        $audioInvalidPathCount = 0;
        $mediaPathProblems = collect();
        $mediaPathProblemsCount = 0;
        $lessonsWithoutCategory = collect();
        $lessonsWithoutCategoryCount = 0;
        $carouselWithoutLesson = collect();
        $carouselWithoutLessonCount = 0;
        $testsWithoutLesson = collect();
        $testsWithoutLessonCount = 0;
        $duplicateLessonUrls = collect();
        $duplicateLessonUrlsCount = 0;

        try {
            $report = $service->buildReport();

            $videoRowsCount = (int) ($report['videoRowsCount'] ?? 0);
            $audioRowsCount = (int) ($report['audioRowsCount'] ?? 0);
            $videoMissingPathCount = (int) ($report['videoMissingPathCount'] ?? 0);
            $audioMissingPathCount = (int) ($report['audioMissingPathCount'] ?? 0);
            $videoInvalidPathCount = (int) ($report['videoInvalidPathCount'] ?? 0);
            $audioInvalidPathCount = (int) ($report['audioInvalidPathCount'] ?? 0);
            $mediaPathProblems = $report['mediaPathProblems'] ?? collect();
            $mediaPathProblemsCount = (int) ($report['mediaPathProblemsCount'] ?? 0);
            $lessonsWithoutCategory = $report['lessonsWithoutCategory'] ?? collect();
            $lessonsWithoutCategoryCount = (int) ($report['lessonsWithoutCategoryCount'] ?? 0);
            $carouselWithoutLesson = $report['carouselWithoutLesson'] ?? collect();
            $carouselWithoutLessonCount = (int) ($report['carouselWithoutLessonCount'] ?? 0);
            $testsWithoutLesson = $report['testsWithoutLesson'] ?? collect();
            $testsWithoutLessonCount = (int) ($report['testsWithoutLessonCount'] ?? 0);
            $duplicateLessonUrls = $report['duplicateLessonUrls'] ?? collect();
            $duplicateLessonUrlsCount = (int) ($report['duplicateLessonUrlsCount'] ?? 0);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('course-integrity', [
            'videoRowsCount' => $videoRowsCount,
            'audioRowsCount' => $audioRowsCount,
            'videoMissingPathCount' => $videoMissingPathCount,
            'audioMissingPathCount' => $audioMissingPathCount,
            'videoInvalidPathCount' => $videoInvalidPathCount,
            'audioInvalidPathCount' => $audioInvalidPathCount,
            'mediaPathProblems' => $mediaPathProblems,
            'mediaPathProblemsCount' => $mediaPathProblemsCount,
            'lessonsWithoutCategory' => $lessonsWithoutCategory,
            'lessonsWithoutCategoryCount' => $lessonsWithoutCategoryCount,
            'carouselWithoutLesson' => $carouselWithoutLesson,
            'carouselWithoutLessonCount' => $carouselWithoutLessonCount,
            'testsWithoutLesson' => $testsWithoutLesson,
            'testsWithoutLessonCount' => $testsWithoutLessonCount,
            'duplicateLessonUrls' => $duplicateLessonUrls,
            'duplicateLessonUrlsCount' => $duplicateLessonUrlsCount,
            'error' => $error,
        ]);
    }
}
