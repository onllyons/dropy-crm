<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExcludedMobileScreensService
{
    private const TABLE_RULES = 'excluded_mobile_screens';
    private const TABLE_STUDY_LANG = 'users_study_lang';
    private const TABLE_ANALYTICS_APP = 'visitorBehaviorAnalyticsApp';

    public function getPageData(?string $previewLang, ?string $previewStudyLang, bool $includeGlobal): array
    {
        $this->assertRulesTableExists();

        $rules = $this->listRules();
        $screenOptions = $this->getScreenOptions($rules);
        $uiLangOptions = $this->getUiLangOptions($rules);
        $studyLangOptions = $this->getStudyLangOptions($rules);

        $resolvedPreviewLang = $this->resolvePreviewValue($previewLang, $uiLangOptions);
        $resolvedPreviewStudyLang = $this->resolvePreviewValue($previewStudyLang, $studyLangOptions);

        $hiddenScreens = collect();
        if ($resolvedPreviewLang !== '' && $resolvedPreviewStudyLang !== '') {
            $hiddenScreens = $this->resolveHiddenScreens(
                $rules,
                $resolvedPreviewLang,
                $resolvedPreviewStudyLang,
                $includeGlobal
            );
        }

        return [
            'rules' => $rules,
            'screenOptions' => $screenOptions,
            'uiLangOptions' => $uiLangOptions,
            'studyLangOptions' => $studyLangOptions,
            'previewLang' => $resolvedPreviewLang,
            'previewStudyLang' => $resolvedPreviewStudyLang,
            'includeGlobal' => $includeGlobal,
            'hiddenScreens' => $hiddenScreens,
        ];
    }

    public function validationOptions(): array
    {
        $this->assertRulesTableExists();

        $rules = $this->listRules();
        $screenOptions = $this->getScreenOptions($rules);
        $uiLangOptions = $this->getUiLangOptions($rules);
        $studyLangOptions = $this->getStudyLangOptions($rules);

        return [
            'screens' => $screenOptions,
            'langs' => $uiLangOptions,
            'study_langs' => $studyLangOptions,
            'langs_with_any' => collect(['*'])->concat($uiLangOptions)->values()->all(),
            'study_langs_with_any' => collect(['*'])->concat($studyLangOptions)->values()->all(),
        ];
    }

    public function createRule(array $payload): void
    {
        $this->assertRulesTableExists();

        $rule = $this->normalizeRulePayload($payload);
        $this->ensureDuplicateDoesNotExist($rule['screen'], $rule['lang'], $rule['studyLang'], null);

        DB::connection('tenant')
            ->table(self::TABLE_RULES)
            ->insert($rule);
    }

    public function updateRule(int $id, array $payload): void
    {
        $this->assertRulesTableExists();

        $exists = DB::connection('tenant')
            ->table(self::TABLE_RULES)
            ->where('id', $id)
            ->exists();

        if (!$exists) {
            throw new \RuntimeException('Rule not found.');
        }

        $rule = $this->normalizeRulePayload($payload);
        $this->ensureDuplicateDoesNotExist($rule['screen'], $rule['lang'], $rule['studyLang'], $id);

        DB::connection('tenant')
            ->table(self::TABLE_RULES)
            ->where('id', $id)
            ->update($rule);
    }

    public function deleteRule(int $id): void
    {
        $this->assertRulesTableExists();

        $deleted = DB::connection('tenant')
            ->table(self::TABLE_RULES)
            ->where('id', $id)
            ->delete();

        if ($deleted === 0) {
            throw new \RuntimeException('Rule not found.');
        }
    }

    public function listRules(): Collection
    {
        return DB::connection('tenant')
            ->table(self::TABLE_RULES)
            ->select('id', 'screen', 'lang', 'studyLang')
            ->orderBy('screen')
            ->orderBy('lang')
            ->orderBy('studyLang')
            ->orderByDesc('id')
            ->get();
    }

    private function getScreenOptions(Collection $rules): array
    {
        $options = collect();

        if (Schema::connection('tenant')->hasTable(self::TABLE_ANALYTICS_APP)) {
            $fromAnalytics = DB::connection('tenant')
                ->table(self::TABLE_ANALYTICS_APP)
                ->select('screen', DB::raw('COUNT(*) as count'))
                ->whereNotNull('screen')
                ->where('screen', '!=', '')
                ->groupBy('screen')
                ->orderByDesc('count')
                ->limit(500)
                ->pluck('screen');

            $options = $options->concat($fromAnalytics);
        }

        $options = $options
            ->concat($rules->pluck('screen'))
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return $options->all();
    }

    private function getUiLangOptions(Collection $rules): array
    {
        $options = collect();

        if (Schema::connection('tenant')->hasTable(self::TABLE_STUDY_LANG)) {
            $fromUsersStudyLang = DB::connection('tenant')
                ->table(self::TABLE_STUDY_LANG)
                ->select('lang')
                ->whereNotNull('lang')
                ->where('lang', '!=', '')
                ->distinct()
                ->pluck('lang');

            $options = $options->concat($fromUsersStudyLang);
        }

        $options = $options
            ->concat($rules->pluck('lang'))
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '' && $value !== '*';
            })
            ->unique()
            ->sort()
            ->values();

        return $options->all();
    }

    private function getStudyLangOptions(Collection $rules): array
    {
        $options = collect();

        if (Schema::connection('tenant')->hasTable(self::TABLE_STUDY_LANG)) {
            $fromUsersStudyLang = DB::connection('tenant')
                ->table(self::TABLE_STUDY_LANG)
                ->select('studyLang')
                ->whereNotNull('studyLang')
                ->where('studyLang', '!=', '')
                ->distinct()
                ->pluck('studyLang');

            $options = $options->concat($fromUsersStudyLang);
        }

        $options = $options
            ->concat($rules->pluck('studyLang'))
            ->map(function ($value) {
                return trim((string) $value);
            })
            ->filter(function ($value) {
                return $value !== '' && $value !== '*';
            })
            ->unique()
            ->sort()
            ->values();

        return $options->all();
    }

    private function resolvePreviewValue(?string $requested, array $allowed): string
    {
        if (empty($allowed)) {
            return '';
        }

        $candidate = trim((string) $requested);
        if ($candidate !== '' && in_array($candidate, $allowed, true)) {
            return $candidate;
        }

        return (string) ($allowed[0] ?? '');
    }

    private function resolveHiddenScreens(Collection $rules, string $currentLang, string $currentStudyLang, bool $includeGlobal): Collection
    {
        return $rules
            ->filter(function ($rule) use ($currentLang, $currentStudyLang, $includeGlobal) {
                $lang = trim((string) ($rule->lang ?? ''));
                $studyLang = trim((string) ($rule->studyLang ?? ''));

                $matchSpecific = ($lang === $currentLang && $studyLang === $currentStudyLang);
                $matchAnyStudy = ($lang === $currentLang && $studyLang === '*');
                $matchAnyUi = ($lang === '*' && $studyLang === $currentStudyLang);
                $matchGlobal = ($includeGlobal && $lang === '*' && $studyLang === '*');

                return $matchSpecific || $matchAnyStudy || $matchAnyUi || $matchGlobal;
            })
            ->pluck('screen')
            ->map(function ($screen) {
                return trim((string) $screen);
            })
            ->filter()
            ->unique()
            ->sort()
            ->values();
    }

    private function ensureDuplicateDoesNotExist(string $screen, string $lang, string $studyLang, ?int $excludeId): void
    {
        $query = DB::connection('tenant')
            ->table(self::TABLE_RULES)
            ->where('screen', $screen)
            ->where('lang', $lang)
            ->where('studyLang', $studyLang);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \RuntimeException('Duplicate rule is not allowed.');
        }
    }

    private function normalizeRulePayload(array $payload): array
    {
        $screen = trim((string) ($payload['screen'] ?? ''));
        $lang = trim((string) ($payload['lang'] ?? ''));
        $studyLang = trim((string) ($payload['studyLang'] ?? ''));

        if ($screen === '') {
            throw new \InvalidArgumentException('Screen is required.');
        }
        if ($lang === '') {
            throw new \InvalidArgumentException('lang is required.');
        }
        if ($studyLang === '') {
            throw new \InvalidArgumentException('studyLang is required.');
        }

        return [
            'screen' => $screen,
            'lang' => $lang === '*' ? '*' : strtolower($lang),
            'studyLang' => $studyLang === '*' ? '*' : strtolower($studyLang),
        ];
    }

    private function assertRulesTableExists(): void
    {
        if (!Schema::connection('tenant')->hasTable(self::TABLE_RULES)) {
            throw new \RuntimeException('Table excluded_mobile_screens does not exist in tenant DB.');
        }
    }
}

