@props([
    'id' => 'modalOverlay',
    'title' => 'Confirm action',
    'description' => 'This is a minimal modal for MVP feedback.'
])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
    <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold">{{ $title }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ $description }}</p>
            </div>
            <button id="closeModalButton" class="rounded-lg border border-slate-200 p-2 text-slate-700" type="button" aria-label="Close modal">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6l-12 12"/>
                </svg>
            </button>
        </div>
        <div class="mt-6 flex justify-end gap-2">
            <button id="cancelModalButton" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" type="button">Cancel</button>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" type="button">Confirm</button>
        </div>
    </div>
</div>
