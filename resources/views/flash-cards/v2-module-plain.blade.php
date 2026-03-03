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
            <div>
                <a href="{{ route('flash-cards.v2.module', ['moduleId' => $module->id]) }}">Back to category view</a>
            </div>

            <pre>
Category: {{ $module->title ?? '-' }} (ID {{ $module->id ?? '-' }})
Slug: {{ $module->slug ?? '-' }}

@foreach (($detail['lessons'] ?? collect()) as $lesson)
{{ $lesson->title ?? '-' }} (ID {{ $lesson->id ?? '-' }})
@forelse (($lesson->items ?? collect()) as $item)
{{ trim((string) ($item->text_from ?? '')) !== '' ? $item->text_from : '-' }} - {{ trim((string) ($item->text_to ?? '')) !== '' ? $item->text_to : '-' }}
@empty
No items for this lesson.
@endforelse

@endforeach
            </pre>
        @else
            <pre>Category not found.</pre>
        @endif
    </body>
</html>
