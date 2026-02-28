<?php

namespace App\Http\Controllers;

use App\Services\ExcludedMobileScreensService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExcludedMobileScreensController extends Controller
{
    public function index(Request $request, ExcludedMobileScreensService $service)
    {
        $error = null;
        $rules = collect();
        $screenOptions = [];
        $uiLangOptions = [];
        $studyLangOptions = [];
        $previewLang = '';
        $previewStudyLang = '';
        $includeGlobal = $request->boolean('include_global', true);
        $hiddenScreens = collect();

        try {
            $pageData = $service->getPageData(
                (string) $request->query('preview_lang', ''),
                (string) $request->query('preview_study_lang', ''),
                $includeGlobal
            );

            $rules = $pageData['rules'] ?? collect();
            $screenOptions = $pageData['screenOptions'] ?? [];
            $uiLangOptions = $pageData['uiLangOptions'] ?? [];
            $studyLangOptions = $pageData['studyLangOptions'] ?? [];
            $previewLang = (string) ($pageData['previewLang'] ?? '');
            $previewStudyLang = (string) ($pageData['previewStudyLang'] ?? '');
            $includeGlobal = (bool) ($pageData['includeGlobal'] ?? $includeGlobal);
            $hiddenScreens = $pageData['hiddenScreens'] ?? collect();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        return view('app.excluded-mobile-screens', [
            'rules' => $rules,
            'screenOptions' => $screenOptions,
            'uiLangOptions' => $uiLangOptions,
            'studyLangOptions' => $studyLangOptions,
            'previewLang' => $previewLang,
            'previewStudyLang' => $previewStudyLang,
            'includeGlobal' => $includeGlobal,
            'hiddenScreens' => $hiddenScreens,
            'error' => $error,
        ]);
    }

    public function store(Request $request, ExcludedMobileScreensService $service)
    {
        try {
            $payload = $this->validatedPayload($request, $service);
            $service->createRule($payload);

            return back()->with('status', 'Rule added.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, int $id, ExcludedMobileScreensService $service)
    {
        try {
            $payload = $this->validatedPayload($request, $service);
            $service->updateRule($id, $payload);

            return back()->with('status', 'Rule updated.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(int $id, ExcludedMobileScreensService $service)
    {
        try {
            $service->deleteRule($id);
            return back()->with('status', 'Rule deleted.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function validatedPayload(Request $request, ExcludedMobileScreensService $service): array
    {
        $options = $service->validationOptions();
        $screenOptions = $options['screens'] ?? [];
        $langOptions = $options['langs_with_any'] ?? [];
        $studyLangOptions = $options['study_langs_with_any'] ?? [];

        if (empty($screenOptions)) {
            throw new \RuntimeException('No valid screens found. Cannot create/update rule.');
        }

        return $request->validate(
            [
                'screen' => ['required', 'string', Rule::in($screenOptions)],
                'lang' => ['required', 'string', Rule::in($langOptions)],
                'studyLang' => ['required', 'string', Rule::in($studyLangOptions)],
            ],
            [
                'screen.required' => 'screen is required.',
                'screen.in' => 'Select a valid screen from dropdown.',
                'lang.required' => 'lang is required.',
                'lang.in' => 'Select a valid UI language.',
                'studyLang.required' => 'studyLang is required.',
                'studyLang.in' => 'Select a valid study language.',
            ]
        );
    }
}

