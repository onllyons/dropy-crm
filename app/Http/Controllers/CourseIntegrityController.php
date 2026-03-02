<?php

namespace App\Http\Controllers;

use App\Services\CourseIntegrityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseIntegrityController extends Controller
{
    public function index(Request $request, CourseIntegrityService $service): View
    {
        $error = null;
        $realCheckEnabled = $request->boolean('real_check', true);
        $realCheckLimit = (int) $request->query('real_check_limit', 500);
        $realCheckLimit = max(10, min($realCheckLimit, 50000));
        $startAfterId = (int) $request->query('start_after_id', 0);
        $startAfterId = max(0, $startAfterId);
        $videoRowsCount = 0;
        $audioRowsCount = 0;
        $videoMissingPathCount = 0;
        $audioMissingPathCount = 0;
        $videoInvalidPathCount = 0;
        $audioInvalidPathCount = 0;
        $mediaCheckedRowsCount = 0;
        $mediaCheckedMinId = null;
        $mediaCheckedMaxId = null;
        $mediaMissingOnServerCount = 0;
        $mediaPathProblems = collect();
        $mediaPathProblemsCount = 0;
        $testContentRowsCount = 0;
        $testContentMissingPathCount = 0;
        $testContentCheckedRowsCount = 0;
        $testContentCheckedMinId = null;
        $testContentCheckedMaxId = null;
        $testContentUnknownExtensionCount = 0;
        $testContentMissingOnServerCount = 0;
        $testContentProblems = collect();
        $testContentProblemsCount = 0;
        $nextStartAfterId = $startAfterId;
        $lessonsWithoutCategory = collect();
        $lessonsWithoutCategoryCount = 0;
        $carouselWithoutLesson = collect();
        $carouselWithoutLessonCount = 0;
        $testsWithoutLesson = collect();
        $testsWithoutLessonCount = 0;
        $duplicateLessonUrls = collect();
        $duplicateLessonUrlsCount = 0;

        try {
            $report = $service->buildReport($realCheckEnabled, $realCheckLimit, $startAfterId);

            $videoRowsCount = (int) ($report['videoRowsCount'] ?? 0);
            $audioRowsCount = (int) ($report['audioRowsCount'] ?? 0);
            $videoMissingPathCount = (int) ($report['videoMissingPathCount'] ?? 0);
            $audioMissingPathCount = (int) ($report['audioMissingPathCount'] ?? 0);
            $videoInvalidPathCount = (int) ($report['videoInvalidPathCount'] ?? 0);
            $audioInvalidPathCount = (int) ($report['audioInvalidPathCount'] ?? 0);
            $mediaCheckedRowsCount = (int) ($report['mediaCheckedRowsCount'] ?? 0);
            $mediaCheckedMinId = $report['mediaCheckedMinId'] ?? null;
            $mediaCheckedMaxId = $report['mediaCheckedMaxId'] ?? null;
            $mediaMissingOnServerCount = (int) ($report['mediaMissingOnServerCount'] ?? 0);
            $mediaPathProblems = $report['mediaPathProblems'] ?? collect();
            $mediaPathProblemsCount = (int) ($report['mediaPathProblemsCount'] ?? 0);
            $testContentRowsCount = (int) ($report['testContentRowsCount'] ?? 0);
            $testContentMissingPathCount = (int) ($report['testContentMissingPathCount'] ?? 0);
            $testContentCheckedRowsCount = (int) ($report['testContentCheckedRowsCount'] ?? 0);
            $testContentCheckedMinId = $report['testContentCheckedMinId'] ?? null;
            $testContentCheckedMaxId = $report['testContentCheckedMaxId'] ?? null;
            $testContentUnknownExtensionCount = (int) ($report['testContentUnknownExtensionCount'] ?? 0);
            $testContentMissingOnServerCount = (int) ($report['testContentMissingOnServerCount'] ?? 0);
            $testContentProblems = $report['testContentProblems'] ?? collect();
            $testContentProblemsCount = (int) ($report['testContentProblemsCount'] ?? 0);
            $nextStartAfterId = (int) ($report['nextStartAfterId'] ?? $startAfterId);
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
            'realCheckEnabled' => $realCheckEnabled,
            'realCheckLimit' => $realCheckLimit,
            'startAfterId' => $startAfterId,
            'nextStartAfterId' => $nextStartAfterId,
            'mediaCheckedRowsCount' => $mediaCheckedRowsCount,
            'mediaCheckedMinId' => $mediaCheckedMinId,
            'mediaCheckedMaxId' => $mediaCheckedMaxId,
            'mediaMissingOnServerCount' => $mediaMissingOnServerCount,
            'mediaPathProblems' => $mediaPathProblems,
            'mediaPathProblemsCount' => $mediaPathProblemsCount,
            'testContentRowsCount' => $testContentRowsCount,
            'testContentMissingPathCount' => $testContentMissingPathCount,
            'testContentCheckedRowsCount' => $testContentCheckedRowsCount,
            'testContentCheckedMinId' => $testContentCheckedMinId,
            'testContentCheckedMaxId' => $testContentCheckedMaxId,
            'testContentUnknownExtensionCount' => $testContentUnknownExtensionCount,
            'testContentMissingOnServerCount' => $testContentMissingOnServerCount,
            'testContentProblems' => $testContentProblems,
            'testContentProblemsCount' => $testContentProblemsCount,
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
