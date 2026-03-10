<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Flash-cards V2 Plain List</title>
    </head>
    <body>
        @php
            $module = $detail['module'] ?? null;
        @endphp

        @if (!empty($error))
            <pre>{{ $error }}</pre>
        @elseif ($module)
            <pre>
@foreach (($detail['lessons'] ?? collect()) as $lesson)
@php
    $viewType = strtolower(trim((string) request()->query('type', 'word')));
    $showAllTypes = $viewType === 'all' || request()->query('all_types') === '1';
    $isWordsLesson = strtolower(trim((string) ($lesson->lesson_type ?? ''))) === 'words';
    if (!$isWordsLesson && !$showAllTypes) {
        continue;
    }

    $lessonItems = collect($lesson->items ?? collect());
    if (!$showAllTypes) {
        $lessonItems = $lessonItems->filter(function ($item) {
            return strtolower(trim((string) ($item->type ?? ''))) === 'word';
        })->values();
    }

    if ($lessonItems->isEmpty()) {
        continue;
    }

    $lessonJson = [
        'lesson_id' => $lesson->id ?? null,
        'title' => $lesson->title ?? null,
        'level' => $lesson->level ?? null,
        'type' => $lesson->lesson_type ?? null,
        'words_total' => (int) $lessonItems->count(),
        'items' => $lessonItems->map(function ($item) {
            $row = [
                'id' => $item->id ?? null,
                'text_from' => $item->text_from ?? null,
                'text_to' => $item->text_to ?? null,
            ];

            return $row;
        })->values()->all(),
    ];

    if ($isWordsLesson) {
        $lessonJson['items'] = $lessonItems->map(function ($item) {
            $row = [
                'id' => $item->id ?? null,
                'text_from' => $item->text_from ?? null,
                'text_to' => $item->text_to ?? null,
                'ipa' => $item->ipa ?? null,
            ];

            return $row;
        })->values()->all();
    }
@endphp
{{ json_encode($lessonJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}

@endforeach
            </pre>
        @else
            <pre>Category not found.</pre>
        @endif
    </body>
</html>
