<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CourseIntegrityService
{
    private const MEDIA_REAL_CHECK_BASE = 'https://www.language.onllyons.com/ru/ru-en/packs/assest/course';
    private const PROD_BASE_URL = 'https://www.language.onllyons.com';

    public function buildReport(bool $realCheckEnabled = true, int $realCheckLimit = 500, int $startAfterId = 0): array
    {
        $videoPathIsValidSql = "
            (
                file_path LIKE '%/course/video-lessons/%'
                OR file_path LIKE '%video-lessons/%'
                OR file_path NOT LIKE '%/%'
            )
        ";
        $audioPathIsValidSql = "
            (
                file_path LIKE '%/course/audio-lessons/%'
                OR file_path LIKE '%audio-lessons/%'
                OR file_path NOT LIKE '%/%'
            )
        ";

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
            ->whereRaw("NOT ({$videoPathIsValidSql})")
            ->count();

        $audioInvalidPathCount = (clone $audioBase)
            ->whereRaw("TRIM(COALESCE(file_path, '')) <> ''")
            ->whereRaw("NOT ({$audioPathIsValidSql})")
            ->count();

        $mediaPathProblems = (clone $mediaBase)
            ->select(
                'id',
                'course_url',
                'series',
                'variant',
                'file_path',
                DB::raw("NULL AS check_url"),
                DB::raw("
                    CASE
                        WHEN variant = 'v' AND TRIM(COALESCE(file_path, '')) <> '' AND NOT ({$videoPathIsValidSql}) THEN 'invalid_video_path'
                        WHEN variant = 'a' AND TRIM(COALESCE(file_path, '')) <> '' AND NOT ({$audioPathIsValidSql}) THEN 'invalid_audio_path'
                        ELSE 'ok'
                    END AS issue
                ")
            )
            ->where(function ($query) use ($videoPathIsValidSql, $audioPathIsValidSql) {
                $query->where(function ($subQuery) use ($videoPathIsValidSql) {
                        $subQuery->where('variant', 'v')
                            ->whereRaw("TRIM(COALESCE(file_path, '')) <> ''")
                            ->whereRaw("NOT ({$videoPathIsValidSql})");
                    })
                    ->orWhere(function ($subQuery) use ($audioPathIsValidSql) {
                        $subQuery->where('variant', 'a')
                            ->whereRaw("TRIM(COALESCE(file_path, '')) <> ''")
                            ->whereRaw("NOT ({$audioPathIsValidSql})");
                    });
            })
            ->orderBy('variant')
            ->orderBy('course_url')
            ->orderBy('id')
            ->get();

        $realCheckLimit = max(10, min($realCheckLimit, 50000));
        $startAfterId = max(0, $startAfterId);
        $mediaCheckedRowsCount = 0;
        $mediaCheckedMinId = null;
        $mediaCheckedMaxId = null;
        $mediaMissingOnServer = collect();

        if ($realCheckEnabled) {
            $rowsToCheck = DB::connection('tenant')
                ->table('course_carousel')
                ->select('id', 'course_url', 'series', 'variant', 'file_path')
                ->whereIn('variant', ['v', 'a'])
                ->whereRaw("TRIM(COALESCE(file_path, '')) <> ''")
                ->where('id', '>', $startAfterId)
                ->orderBy('id')
                ->limit($realCheckLimit)
                ->get();

            $mediaCheckedRowsCount = $rowsToCheck->count();
            $mediaCheckedMinId = $rowsToCheck->min('id');
            $mediaCheckedMaxId = $rowsToCheck->max('id');
            $urlStatusCache = [];

            foreach ($rowsToCheck as $row) {
                $variant = trim((string) ($row->variant ?? ''));
                $filePath = trim((string) ($row->file_path ?? ''));
                $checkUrl = $this->resolveMediaUrl($variant, $filePath);

                if ($checkUrl === null) {
                    continue;
                }

                if (!array_key_exists($checkUrl, $urlStatusCache)) {
                    $urlStatusCache[$checkUrl] = $this->urlExists($checkUrl);
                }

                if (!$urlStatusCache[$checkUrl]) {
                    $row->issue = 'missing_on_server';
                    $row->check_url = $checkUrl;
                    $mediaMissingOnServer->push($row);
                }
            }
        }

        $mediaPathProblems = $mediaPathProblems
            ->concat($mediaMissingOnServer)
            ->sortBy(function ($row) {
                $variant = (string) ($row->variant ?? '');
                $courseUrl = (string) ($row->course_url ?? '');
                $id = (int) ($row->id ?? 0);
                return $variant . '|' . $courseUrl . '|' . str_pad((string) $id, 10, '0', STR_PAD_LEFT);
            })
            ->values();

        $testContentBase = DB::connection('tenant')
            ->table('course_test');

        $testContentRowsCount = (clone $testContentBase)->count();
        $testContentMissingPathCount = (clone $testContentBase)
            ->whereRaw("TRIM(COALESCE(file_path, '')) = ''")
            ->count();
        $testContentCheckedRowsCount = 0;
        $testContentCheckedMinId = null;
        $testContentCheckedMaxId = null;
        $testContentUnknownExtensionCount = 0;
        $testContentMissingOnServerCount = 0;
        $testContentProblemsChecked = collect();

        if ($realCheckEnabled) {
            $testRowsToCheck = DB::connection('tenant')
                ->table('course_test')
                ->select('id', 'course_url', 'series', 'variant', 'file_path')
                ->whereRaw("TRIM(COALESCE(file_path, '')) <> ''")
                ->where('id', '>', $startAfterId)
                ->orderBy('id')
                ->limit($realCheckLimit)
                ->get();

            $testContentCheckedRowsCount = $testRowsToCheck->count();
            $testContentCheckedMinId = $testRowsToCheck->min('id');
            $testContentCheckedMaxId = $testRowsToCheck->max('id');
            $urlStatusCache = [];

            foreach ($testRowsToCheck as $row) {
                $filePath = trim((string) ($row->file_path ?? ''));
                $resolved = $this->resolveCourseTestContentUrl($filePath);
                $checkUrl = $resolved['url'] ?? null;
                $issue = $resolved['issue'] ?? null;

                if ($issue === 'unknown_extension') {
                    $row->issue = 'unknown_extension';
                    $row->check_url = null;
                    $testContentProblemsChecked->push($row);
                    $testContentUnknownExtensionCount++;
                    continue;
                }

                if ($checkUrl === null) {
                    continue;
                }

                if (!array_key_exists($checkUrl, $urlStatusCache)) {
                    $urlStatusCache[$checkUrl] = $this->urlExists($checkUrl);
                }

                if (!$urlStatusCache[$checkUrl]) {
                    $row->issue = 'missing_on_server';
                    $row->check_url = $checkUrl;
                    $testContentProblemsChecked->push($row);
                    $testContentMissingOnServerCount++;
                }
            }
        }

        $testContentProblems = $testContentProblemsChecked
            ->sortBy(function ($row) {
                $courseUrl = (string) ($row->course_url ?? '');
                $id = (int) ($row->id ?? 0);
                return $courseUrl . '|' . str_pad((string) $id, 10, '0', STR_PAD_LEFT);
            })
            ->values();

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

        $nextStartAfterId = max(
            $startAfterId,
            (int) ($mediaCheckedMaxId ?? 0),
            (int) ($testContentCheckedMaxId ?? 0)
        );

        return [
            'videoRowsCount' => (clone $videoBase)->count(),
            'audioRowsCount' => (clone $audioBase)->count(),
            'videoMissingPathCount' => (int) $videoMissingPathCount,
            'audioMissingPathCount' => (int) $audioMissingPathCount,
            'videoInvalidPathCount' => (int) $videoInvalidPathCount,
            'audioInvalidPathCount' => (int) $audioInvalidPathCount,
            'realCheckEnabled' => $realCheckEnabled,
            'realCheckLimit' => $realCheckLimit,
            'startAfterId' => $startAfterId,
            'mediaCheckedRowsCount' => (int) $mediaCheckedRowsCount,
            'mediaCheckedMinId' => $mediaCheckedMinId,
            'mediaCheckedMaxId' => $mediaCheckedMaxId,
            'mediaMissingOnServerCount' => (int) $mediaMissingOnServer->count(),
            'mediaPathProblems' => $mediaPathProblems,
            'mediaPathProblemsCount' => $mediaPathProblems->count(),
            'testContentRowsCount' => (int) $testContentRowsCount,
            'testContentMissingPathCount' => (int) $testContentMissingPathCount,
            'testContentCheckedRowsCount' => (int) $testContentCheckedRowsCount,
            'testContentCheckedMinId' => $testContentCheckedMinId,
            'testContentCheckedMaxId' => $testContentCheckedMaxId,
            'testContentUnknownExtensionCount' => (int) $testContentUnknownExtensionCount,
            'testContentMissingOnServerCount' => (int) $testContentMissingOnServerCount,
            'testContentProblems' => $testContentProblems,
            'testContentProblemsCount' => (int) $testContentProblems->count(),
            'nextStartAfterId' => $nextStartAfterId,
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

    private function resolveMediaUrl(string $variant, string $filePath): ?string
    {
        $path = trim($filePath);
        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $this->normalizeAbsoluteUrl($path);
        }

        if (strpos($path, '/ru/ru-en/packs/assest/course/') === 0) {
            return 'https://www.language.onllyons.com' . $path;
        }

        if (strpos($path, '/packs/assest/course/') === 0) {
            return 'https://www.language.onllyons.com/ru/ru-en' . $path;
        }

        if (strpos($path, 'packs/assest/course/') === 0) {
            return 'https://www.language.onllyons.com/ru/ru-en/' . ltrim($path, '/');
        }

        if (strpos($path, '/') === 0) {
            return 'https://www.language.onllyons.com' . $path;
        }

        $normalized = ltrim($path, '/');
        if ($variant === 'v') {
            return self::MEDIA_REAL_CHECK_BASE . '/video-lessons/' . $normalized;
        }

        if ($variant === 'a') {
            return self::MEDIA_REAL_CHECK_BASE . '/audio-lessons/' . $normalized;
        }

        return 'https://www.language.onllyons.com/' . $normalized;
    }

    private function resolveCourseTestContentUrl(string $filePath): array
    {
        $path = trim($filePath);
        if ($path === '') {
            return ['url' => null, 'issue' => null];
        }

        $pathOnly = parse_url($path, PHP_URL_PATH);
        $pathForExt = is_string($pathOnly) && $pathOnly !== '' ? $pathOnly : $path;
        $extension = strtolower((string) pathinfo($pathForExt, PATHINFO_EXTENSION));

        $videoExtensions = ['mp4', 'mov', 'webm', 'mkv'];
        $audioExtensions = ['mp3', 'wav', 'ogg', 'm4a', 'aac'];
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'];

        $bucket = null;
        if (in_array($extension, $videoExtensions, true)) {
            $bucket = 'videos';
        } elseif (in_array($extension, $audioExtensions, true)) {
            $bucket = 'audios';
        } elseif (in_array($extension, $imageExtensions, true)) {
            $bucket = 'images';
        }

        if ($bucket === null) {
            return ['url' => null, 'issue' => 'unknown_extension'];
        }

        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return ['url' => $this->normalizeAbsoluteUrl($path), 'issue' => null];
        }

        if (strpos($path, '/ru/ru-en/packs/assest/course/content/') === 0) {
            return ['url' => self::PROD_BASE_URL . $path, 'issue' => null];
        }

        if (strpos($path, '/packs/assest/course/content/') === 0) {
            return ['url' => self::PROD_BASE_URL . '/ru/ru-en' . $path, 'issue' => null];
        }

        if (strpos($path, 'packs/assest/course/content/') === 0) {
            return ['url' => self::PROD_BASE_URL . '/ru/ru-en/' . ltrim($path, '/'), 'issue' => null];
        }

        if (strpos($path, '/course/content/') === 0) {
            return ['url' => self::PROD_BASE_URL . '/ru/ru-en/packs/assest' . $path, 'issue' => null];
        }

        if (strpos($path, 'course/content/') === 0) {
            return ['url' => self::PROD_BASE_URL . '/ru/ru-en/packs/assest/' . ltrim($path, '/'), 'issue' => null];
        }

        if (strpos($path, 'content/') === 0) {
            return ['url' => self::MEDIA_REAL_CHECK_BASE . '/' . ltrim($path, '/'), 'issue' => null];
        }

        if (strpos($path, '/') === 0) {
            return ['url' => self::PROD_BASE_URL . $path, 'issue' => null];
        }

        if (strpos($path, 'videos/') === 0 || strpos($path, 'images/') === 0 || strpos($path, 'audios/') === 0) {
            return ['url' => self::MEDIA_REAL_CHECK_BASE . '/content/' . ltrim($path, '/'), 'issue' => null];
        }

        return ['url' => self::MEDIA_REAL_CHECK_BASE . '/content/' . $bucket . '/' . ltrim($path, '/'), 'issue' => null];
    }

    private function normalizeAbsoluteUrl(string $url): string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === 'localhost' || $host === '127.0.0.1' || $host === 'db') {
            $path = (string) parse_url($url, PHP_URL_PATH);
            if ($path !== '') {
                return self::PROD_BASE_URL . $path;
            }
        }

        return $url;
    }

    private function urlExists(string $url): bool
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, 'DropyCRM-CourseIntegrity/1.0');

            curl_exec($ch);
            $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if (($httpCode >= 200 && $httpCode < 400) || $httpCode === 401 || $httpCode === 403) {
                return true;
            }

            return false;
        }

        $headers = @get_headers($url);
        if (!is_array($headers) || empty($headers[0])) {
            return false;
        }

        if (preg_match('/\\s(\\d{3})\\s/', (string) $headers[0], $matches) !== 1) {
            return false;
        }

        $httpCode = (int) ($matches[1] ?? 0);
        return ($httpCode >= 200 && $httpCode < 400) || $httpCode === 401 || $httpCode === 403;
    }
}
