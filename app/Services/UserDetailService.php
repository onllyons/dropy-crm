<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class UserDetailService
{
    private $userCooldownService;

    private $flashCardsService;

    public function __construct(UserCooldownService $userCooldownService, FlashCardsService $flashCardsService)
    {
        $this->userCooldownService = $userCooldownService;
        $this->flashCardsService = $flashCardsService;
    }

    public function getPageData(int $id, string $cooldownFilter = 'all'): array
    {
        $error = null;
        $user = null;
        $subscriptionRows = collect();
        $subscriptionError = null;
        $subscriptionGiftRows = collect();
        $subscriptionGiftError = null;
        $userActionCooldownRows = collect();
        $userActionCooldownError = null;
        $gameActionCooldownRows = collect();
        $gameActionCooldownError = null;
        $cooldownFilterOptions = [
            'all' => 'All',
            'active' => 'Only active',
        ];
        $flashCardsProgress = $this->getEmptyFlashCardsProgress();

        if (!array_key_exists($cooldownFilter, $cooldownFilterOptions)) {
            $cooldownFilter = 'all';
        }

        try {
            $user = DB::table('users')
                ->select('id', 'name', 'surname', 'username', 'email', 'level', 'image', 'bio', 'verified', 'byGoogle', 'appleUser', 'profileAccess', 'time')
                ->selectRaw('FROM_UNIXTIME(`time`) as time_label')
                ->where('id', $id)
                ->first();
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        if ($user) {
            try {
                $subscriptionRows = DB::connection('mysql')
                    ->table('subscriptionManagement')
                    ->select('id', 'user_id', 'subscribe', 'subscribe_start', 'subscribe_expire')
                    ->where('user_id', $id)
                    ->orderByDesc('id')
                    ->get();
            } catch (\Throwable $e) {
                $subscriptionError = $e->getMessage();
            }

            try {
                $subscriptionGiftRows = DB::connection('mysql')
                    ->table('subscriptionManagementGift')
                    ->select('id', 'user_id', 'subscribe_start', 'subscribe_expire')
                    ->where('user_id', $id)
                    ->orderByDesc('id')
                    ->get();
            } catch (\Throwable $e) {
                $subscriptionGiftError = $e->getMessage();
            }

            try {
                $cooldownResult = $this->userCooldownService->getRowsForUser($id, $cooldownFilter);
                $cooldownFilter = $cooldownResult['filter'] ?? $cooldownFilter;
                $cooldownFilterOptions = $cooldownResult['filter_options'] ?? $cooldownFilterOptions;
                $userActionCooldownRows = $cooldownResult['user_action_rows'] ?? collect();
                $userActionCooldownError = $cooldownResult['user_action_error'] ?? null;
                $gameActionCooldownRows = $cooldownResult['game_action_rows'] ?? collect();
                $gameActionCooldownError = $cooldownResult['game_action_error'] ?? null;
            } catch (\Throwable $e) {
                $userActionCooldownError = $e->getMessage();
                $gameActionCooldownError = $e->getMessage();
            }

            try {
                $flashCardsProgress = $this->flashCardsService->getV2ProgressForUser($id);
            } catch (\Throwable $e) {
                $flashCardsProgress['error'] = $e->getMessage();
            }
        }

        return [
            'user' => $user,
            'subscriptionRows' => $subscriptionRows,
            'subscriptionError' => $subscriptionError,
            'subscriptionGiftRows' => $subscriptionGiftRows,
            'subscriptionGiftError' => $subscriptionGiftError,
            'userActionCooldownRows' => $userActionCooldownRows,
            'userActionCooldownError' => $userActionCooldownError,
            'gameActionCooldownRows' => $gameActionCooldownRows,
            'gameActionCooldownError' => $gameActionCooldownError,
            'cooldownFilter' => $cooldownFilter,
            'cooldownFilterOptions' => $cooldownFilterOptions,
            'flashCardsProgress' => $flashCardsProgress,
            'error' => $error,
        ];
    }

    private function getEmptyFlashCardsProgress(): array
    {
        return [
            'summary' => [
                'attempts_total' => 0,
                'completed_attempts' => 0,
                'in_progress_attempts' => 0,
                'completed_lessons' => 0,
                'started_lessons' => 0,
                'catalog_lessons_total' => 0,
                'progress_percent' => 0.0,
                'total_time_seconds' => 0,
                'questions_total' => 0,
                'answers_correct' => 0,
                'answers_wrong' => 0,
                'accuracy_percent' => null,
                'last_activity_at' => null,
            ],
            'moduleProgress' => collect(),
            'recentAttempts' => collect(),
            'error' => null,
        ];
    }
}
