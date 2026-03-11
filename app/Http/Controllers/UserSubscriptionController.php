<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserSubscriptionController extends Controller
{
    public function grantPro(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        try {
            $userExists = DB::table('users')
                ->where('id', $id)
                ->exists();

            if (!$userExists) {
                return back()->with('error', "User #{$id} not found.");
            }

            $days = (int) $validated['days'];
            $now = time();
            $expire = $now + ($days * 86400);

            $latestRow = DB::connection('mysql')
                ->table('subscriptionManagement')
                ->where('user_id', $id)
                ->orderByDesc('id')
                ->first(['id']);

            if ($latestRow) {
                DB::connection('mysql')
                    ->table('subscriptionManagement')
                    ->where('id', $latestRow->id)
                    ->update([
                        'subscribe' => 2,
                        'subscribe_start' => $now,
                        'subscribe_expire' => $expire,
                    ]);

                $rowId = (int) $latestRow->id;
            } else {
                $rowId = (int) DB::connection('mysql')
                    ->table('subscriptionManagement')
                    ->insertGetId([
                        'user_id' => $id,
                        'subscribe' => 2,
                        'subscribe_start' => $now,
                        'subscribe_expire' => $expire,
                    ]);
            }

            return back()->with('status', "Pro subscription granted for {$days} days. Row #{$rowId} updated.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function expireCurrent(Request $request, int $id): RedirectResponse
    {
        try {
            $userExists = DB::table('users')
                ->where('id', $id)
                ->exists();

            if (!$userExists) {
                return back()->with('error', "User #{$id} not found.");
            }

            $latestRow = DB::connection('mysql')
                ->table('subscriptionManagement')
                ->where('user_id', $id)
                ->orderByDesc('id')
                ->first(['id']);

            if (!$latestRow) {
                return back()->with('error', "No subscriptionManagement row found for user #{$id}.");
            }

            $expiredAt = time() - 3600;

            DB::connection('mysql')
                ->table('subscriptionManagement')
                ->where('id', $latestRow->id)
                ->update([
                    'subscribe_expire' => $expiredAt,
                ]);

            $hasActiveGift = DB::connection('mysql')
                ->table('subscriptionManagementGift')
                ->where('user_id', $id)
                ->where('subscribe_expire', '>', time())
                ->exists();

            $message = "Current subscription expired. Row #{$latestRow->id} updated.";
            if ($hasActiveGift) {
                $message .= ' Warning: active gift subscription still exists.';
            }

            return back()->with('status', $message);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
