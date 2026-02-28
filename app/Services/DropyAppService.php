<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DropyAppService
{
    private $bloggerPrMapSchema = null;

    public function getDownloadClickStats(): array
    {
        $todayDate = now()->toDateString();

        $baseQuery = DB::connection('tenant')
            ->table('clickOfApp');

        $totalRows = (int) (clone $baseQuery)->count();
        $todayRows = (int) (clone $baseQuery)
            ->whereDate('timedate', $todayDate)
            ->count();

        return [
            'today_date' => $todayDate,
            'today_rows' => $todayRows,
            'total_rows' => $totalRows,
        ];
    }

    public function getEmailActiveClickGroupedUrls(): Collection
    {
        $normalizedUrlExpr = "REPLACE(REPLACE(REPLACE(REPLACE(`urlPage`, 'https://www.language.onllyons.com', ''), 'http://www.language.onllyons.com', ''), 'https://language.onllyons.com', ''), 'http://language.onllyons.com', '')";

        return DB::connection('tenant')
            ->table('emailAtiveClick')
            ->selectRaw("CASE WHEN {$normalizedUrlExpr} = '' THEN '/' ELSE {$normalizedUrlExpr} END as grouped_url")
            ->selectRaw('COUNT(*) as rows_count')
            ->selectRaw('SUM(CASE WHEN user_id IS NULL OR user_id = 0 THEN 1 ELSE 0 END) as unknown_users')
            ->selectRaw('MIN(`time`) as first_time')
            ->selectRaw('MAX(`time`) as last_time')
            ->groupByRaw("CASE WHEN {$normalizedUrlExpr} = '' THEN '/' ELSE {$normalizedUrlExpr} END")
            ->orderByDesc('rows_count')
            ->get();
    }

    public function getBloggerPrMapSchema(): array
    {
        if ($this->bloggerPrMapSchema !== null) {
            return $this->bloggerPrMapSchema;
        }

        $tableExists = Schema::connection('tenant')->hasTable('blogger_pr_map');
        $columns = [
            'id' => false,
            'pr_code' => false,
            'blogger_name' => false,
            'campaign_name' => false,
            'platform' => false,
            'profile_url' => false,
            'links_json' => false,
            'notes' => false,
            'is_active' => false,
            'created_at' => false,
            'updated_at' => false,
        ];

        if ($tableExists) {
            foreach (array_keys($columns) as $column) {
                $columns[$column] = Schema::connection('tenant')->hasColumn('blogger_pr_map', $column);
            }
        }

        $this->bloggerPrMapSchema = [
            'table_exists' => $tableExists,
            'columns' => $columns,
        ];

        return $this->bloggerPrMapSchema;
    }

    public function getBloggerPrMapRows(): Collection
    {
        $schema = $this->getBloggerPrMapSchema();
        if (!$schema['table_exists']) {
            return collect();
        }

        $columns = $schema['columns'];
        $select = [];
        foreach (array_keys($columns) as $column) {
            if ($columns[$column]) {
                $select[] = $column;
            }
        }
        if (empty($select)) {
            return collect();
        }

        $query = DB::connection('tenant')
            ->table('blogger_pr_map')
            ->select($select);

        if (!empty($columns['updated_at'])) {
            $query->orderByDesc('updated_at');
        }
        if (!empty($columns['id'])) {
            $query->orderByDesc('id');
        }

        return $query->get()->map(function ($row) use ($columns) {
            if (!empty($columns['links_json'])) {
                $raw = trim((string) ($row->links_json ?? ''));
                $row->links_pretty = $raw !== '' ? $raw : '-';
            } else {
                $row->links_pretty = '-';
            }
            return $row;
        });
    }

    public function createBloggerPrMap(array $payload): void
    {
        $this->saveBloggerPrMap(null, $payload);
    }

    public function updateBloggerPrMap(int $id, array $payload): void
    {
        $this->saveBloggerPrMap($id, $payload);
    }

    public function deleteBloggerPrMap(int $id): void
    {
        $schema = $this->getBloggerPrMapSchema();
        if (!$schema['table_exists']) {
            throw new \RuntimeException('Table blogger_pr_map does not exist.');
        }

        $columns = $schema['columns'];
        if (empty($columns['id'])) {
            throw new \RuntimeException('Column id is required for delete.');
        }

        $affected = DB::connection('tenant')
            ->table('blogger_pr_map')
            ->where('id', $id)
            ->delete();

        if ($affected === 0) {
            throw new \RuntimeException('Mapping row not found.');
        }
    }

    public function getEmailActiveClickGroupedByPrCode(): Collection
    {
        $prExpr = "NULLIF(TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(`urlPage`, 'pr=', -1), '&', 1)), '')";

        $rows = DB::connection('tenant')
            ->table('emailAtiveClick')
            ->selectRaw("{$prExpr} as pr_code")
            ->selectRaw('COUNT(*) as rows_count')
            ->selectRaw('SUM(CASE WHEN user_id IS NULL OR user_id = 0 THEN 1 ELSE 0 END) as unknown_users')
            ->selectRaw('MIN(`time`) as first_time')
            ->selectRaw('MAX(`time`) as last_time')
            ->where('urlPage', 'like', '%pr=%')
            ->whereRaw("{$prExpr} IS NOT NULL")
            ->groupByRaw($prExpr)
            ->orderByDesc('rows_count')
            ->get();

        $mapByCode = collect();
        $schema = $this->getBloggerPrMapSchema();
        $columns = $schema['columns'];

        if (
            $schema['table_exists'] &&
            !empty($columns['pr_code']) &&
            !empty($columns['id'])
        ) {
            $mapSelect = ['id', 'pr_code'];
            foreach (['blogger_name', 'campaign_name', 'platform', 'profile_url', 'is_active'] as $col) {
                if (!empty($columns[$col])) {
                    $mapSelect[] = $col;
                }
            }

            $prCodes = $rows->pluck('pr_code')->filter()->unique()->values();
            if ($prCodes->isNotEmpty()) {
                $mapByCode = DB::connection('tenant')
                    ->table('blogger_pr_map')
                    ->select($mapSelect)
                    ->whereIn('pr_code', $prCodes)
                    ->get()
                    ->keyBy('pr_code');
            }
        }

        return $rows->map(function ($row) use ($mapByCode) {
            $prCode = (string) ($row->pr_code ?? '');
            $mapped = $mapByCode->get($prCode);

            return (object) [
                'pr_code' => $prCode,
                'rows_count' => is_numeric($row->rows_count ?? null) ? (int) $row->rows_count : 0,
                'unknown_users' => is_numeric($row->unknown_users ?? null) ? (int) $row->unknown_users : 0,
                'first_time' => $row->first_time ?? null,
                'last_time' => $row->last_time ?? null,
                'map_found' => $mapped ? 1 : 0,
                'map_id' => $mapped->id ?? null,
                'blogger_name' => $mapped->blogger_name ?? null,
                'campaign_name' => $mapped->campaign_name ?? null,
                'platform' => $mapped->platform ?? null,
                'profile_url' => $mapped->profile_url ?? null,
                'is_active' => isset($mapped->is_active) ? (int) $mapped->is_active : null,
            ];
        });
    }

    private function saveBloggerPrMap($id, array $payload): void
    {
        $schema = $this->getBloggerPrMapSchema();
        if (!$schema['table_exists']) {
            throw new \RuntimeException('Table blogger_pr_map does not exist.');
        }

        $columns = $schema['columns'];
        $prCode = trim((string) ($payload['pr_code'] ?? ''));
        $bloggerName = trim((string) ($payload['blogger_name'] ?? ''));

        if ($prCode === '') {
            throw new \InvalidArgumentException('pr_code is required.');
        }
        if (!preg_match('/^[1-9][0-9]{0,5}$/', $prCode)) {
            throw new \InvalidArgumentException('pr_code format is invalid. Expected numeric code from 1 to 999999.');
        }
        if ($bloggerName === '') {
            throw new \InvalidArgumentException('blogger_name is required.');
        }

        if (!empty($columns['pr_code'])) {
            $duplicateQuery = DB::connection('tenant')
                ->table('blogger_pr_map')
                ->where('pr_code', $prCode);

            if ($id !== null) {
                $duplicateQuery->where('id', '!=', (int) $id);
            }

            if ($duplicateQuery->exists()) {
                throw new \RuntimeException('pr_code already exists.');
            }
        }

        $data = [];
        if (!empty($columns['pr_code'])) {
            $data['pr_code'] = $prCode;
        }
        if (!empty($columns['blogger_name'])) {
            $data['blogger_name'] = $bloggerName;
        }
        if (!empty($columns['campaign_name'])) {
            $campaignName = trim((string) ($payload['campaign_name'] ?? ''));
            $data['campaign_name'] = $campaignName !== '' ? $campaignName : null;
        }
        if (!empty($columns['platform'])) {
            $platform = trim((string) ($payload['platform'] ?? ''));
            $data['platform'] = $platform !== '' ? $platform : null;
        }
        if (!empty($columns['profile_url'])) {
            $profileUrl = trim((string) ($payload['profile_url'] ?? ''));
            $data['profile_url'] = $profileUrl !== '' ? $profileUrl : null;
        }
        if (!empty($columns['links_json'])) {
            $linksRaw = trim((string) ($payload['links_json'] ?? ''));
            if ($linksRaw === '') {
                $data['links_json'] = null;
            } else {
                $decoded = json_decode($linksRaw, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \InvalidArgumentException('links_json is not valid JSON.');
                }
                $data['links_json'] = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }
        if (!empty($columns['notes'])) {
            $notes = trim((string) ($payload['notes'] ?? ''));
            $data['notes'] = $notes !== '' ? $notes : null;
        }
        if (!empty($columns['is_active'])) {
            $data['is_active'] = !empty($payload['is_active']) ? 1 : 0;
        }
        if (!empty($columns['updated_at'])) {
            $data['updated_at'] = now();
        }

        if ($id === null) {
            if (!empty($columns['created_at'])) {
                $data['created_at'] = now();
            }
            DB::connection('tenant')
                ->table('blogger_pr_map')
                ->insert($data);
            return;
        }

        $affected = DB::connection('tenant')
            ->table('blogger_pr_map')
            ->where('id', (int) $id)
            ->update($data);

        if ($affected === 0) {
            $exists = DB::connection('tenant')
                ->table('blogger_pr_map')
                ->where('id', (int) $id)
                ->exists();
            if (!$exists) {
                throw new \RuntimeException('Mapping row not found.');
            }
        }
    }
}
