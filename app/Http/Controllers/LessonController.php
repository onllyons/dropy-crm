<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class LessonController extends Controller
{
    public function show(string $slug)
    {
        $blog = $slug;

        $lesson = DB::connection('tenant')
            ->table('course')
            ->where('url', $blog)
            ->orderBy('id')
            ->first();

        if (!$lesson) {
            return redirect('/course');
        }

        return view('lesson', [
            'lesson' => $lesson,
            'blog' => $blog,
        ]);
    }

    public function content(string $slug)
    {
        $blog = $slug;

        $lesson = DB::connection('tenant')
            ->table('course')
            ->select('id', 'category_url')
            ->where('url', $blog)
            ->orderBy('id')
            ->first();

        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => 'Lesson not found.',
            ], 404);
        }

        $carousel = DB::connection('tenant')
            ->table('course_carousel')
            ->where('course_url', $blog)
            ->orderBy('id')
            ->get();

        $engTitles = DB::connection('tenant')
            ->table('course_carousel')
            ->where('course_url', $blog)
            ->pluck('eng_title')
            ->values();

        $tests = DB::connection('tenant')
            ->table('course_test')
            ->where('course_url', $blog)
            ->orderBy('series')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'blog' => $blog,
            'lesson' => $lesson,
            'carousel' => $carousel,
            'eng_titles' => $engTitles,
            'tests' => $tests,
        ]);
    }
}
