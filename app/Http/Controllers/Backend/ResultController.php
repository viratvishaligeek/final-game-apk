<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Bid;
use App\Models\Game;
use App\Models\Gamewiner;
use App\Models\Notification;
use App\Models\Result;
use App\Models\Trans;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::today()->format('Y-m-d'));
        $allGames = Game::where('status', 'active')->orderBy('serial', 'asc')->get();
        $existingResults = Result::where('type', 'jodi')->where('number_date', $selectedDate)->get()->keyBy('game_id');
        return view('backend.result.index', compact('allGames', 'existingResults', 'selectedDate'));
    }

    public function storeOrUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'game_id'     => 'required|exists:games,id',
            'number_date' => 'required|date',
            'new_result'  => 'required|digits:2',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }
        try {
            DB::beginTransaction();
            $gameId      = $request->game_id;
            $numberDate  = $request->number_date;
            $number      = $request->new_result;
            $numberA     = substr($number, 0, 1);
            $numberB     = substr($number, -1);

            $game = Game::findOrFail($gameId);
            Result::updateOrCreate(
                ['game_id' => $gameId, 'number_date' => $numberDate, 'type' => 'ah'],
                ['number' => $numberA]
            );
            Result::updateOrCreate(
                ['game_id' => $gameId, 'number_date' => $numberDate, 'type' => 'bh'],
                ['number' => $numberB]
            );
            Result::updateOrCreate(
                ['game_id' => $gameId, 'number_date' => $numberDate, 'type' => 'jodi'],
                ['number' => $number]
            );
            $isToday = Carbon::parse($numberDate)->isToday();
            if ($isToday) {
                $reward = $game->reward ?? 1;
                $types  = ['jodi' => $number, 'ah' => $numberA, 'bh' => $numberB];
                foreach ($types as $type => $winningValue) {
                    $bids = Bid::where('game_id', $gameId)
                        ->where('time', $numberDate)
                        ->where('number', $winningValue)
                        ->where('type', $type)
                        ->get();
                    foreach ($bids as $bid) {
                        $user = User::where('phone', $bid->phone)->first();
                        if (!$user) continue;

                        $winAmount = ($type === 'jodi')
                            ? ($bid->value * $reward)
                            : ($bid->value * ($reward / 10));

                        // Credit User Wallet
                        $user->wallet += $winAmount;
                        $user->save();

                        // Record Game Winner
                        Gamewiner::create([
                            'phone'     => $bid->phone,
                            'gameid'    => $gameId,
                            'number'    => $winningValue,
                            'amount'    => $bid->value,
                            'winamount' => $winAmount,
                            'time'      => $numberDate,
                            'type'      => $type,
                            'date'      => $numberDate,
                        ]);

                        // Log Transaction
                        Trans::create([
                            'phone'    => $bid->phone,
                            'amount'   => $winAmount,
                            'subject'  => 'You Win ' . $game->name . ' (' . strtoupper($type) . ')',
                            'time'     => $numberDate,
                            'mobile'   => $bid->phone,
                            'wallet'   => $user->wallet,
                            'status'   => 1,
                            'type'     => 'CREDIT',
                            'gameid'   => $gameId,
                            'gametype' => $type,
                        ]);
                    }
                }

                // In-App Notification Log
                $msg = "Today's Result for {$game->name} is {$number}";
                Notification::create([
                    'name'    => $msg,
                    'subject' => 'Game Result Out',
                    'time'    => now('Asia/Kolkata')->format('d-m-Y'),
                    'phone'   => 0,
                ]);

                // Push FCM Notification
                try {
                    Http::withHeaders([
                        'Authorization' => 'key=AAAAV_zN780:APA91bG4WGXQyWXynkZb1OiGJyOJHeBWtK5MKI2GDbgljREeSCTk-rOoT7be7FoRC7jO3Kv4FFy0ndri0fsPTkQ7ILNut95X-Vu8cr3WCmEEO0bnH2bfvIwdJYhKWOOmTNzUH_EnP-o',
                        'Content-Type'  => 'application/json',
                    ])->post('https://fcm.googleapis.com/fcm/send', [
                        'to'           => '/topics/weather',
                        'notification' => [
                            'title' => $game->name . ' Result Published',
                            'body'  => $msg,
                        ],
                    ]);
                } catch (\Exception $e) {
                    // Ignore FCM failures
                }
            }

            DB::commit();
            $successMsg = $isToday
                ? "Live Result ($number) published! Winners credited and notifications sent."
                : "History Result ($number) updated for $numberDate.";

            return redirect()->back()->with('success', $successMsg);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Operation failed: ' . $e->getMessage());
        }
    }

    /**
     * Revert Result to 'Wait' state & Rollback Credited Balances
     */
    public function revert(Request $request)
    {
        $request->validate([
            'game_id'     => 'required|exists:games,id',
            'number_date' => 'required|date'
        ]);

        DB::beginTransaction();

        try {
            // Update results back to 'Wait'
            Result::where('game_id', $request->game_id)
                ->where('number_date', $request->number_date)
                ->update(['number' => 'Wait']);

            // Deduct user wallet balances
            $winners = Gamewiner::where('gameid', $request->game_id)
                ->where('time', $request->number_date)
                ->get();

            foreach ($winners as $winner) {
                $user = User::where('phone', $winner->phone)->first();
                if ($user) {
                    $user->wallet -= $winner->winamount;
                    $user->save();
                }
            }

            // Remove logs
            Gamewiner::where('gameid', $request->game_id)
                ->where('time', $request->number_date)
                ->delete();

            Trans::where('gameid', $request->game_id)
                ->where('time', $request->number_date)
                ->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Result reverted to Wait & wallet balances adjusted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error reverting result: ' . $e->getMessage());
        }
    }
}
