<?php

namespace App\Http\Controllers\Backend;

use App\Models\Game;
use App\Models\Result;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ResultController extends Controller
{
    public function index()
    {
        $gameCount = Game::where('status', 'active')->count();
        $results = Result::take($gameCount)->where('type', 'jodi')->latest('number_date')->get();
        $result = $results->sortBy(function ($item) {
            return $item->game->serial ?? 9999;
        });

        return view('backend.result', compact('result'));
    }

    public function edit(Request $request)
    {
        $result = Result::find($request->id);
        if ($result) {
            return response()->json(['status' => 1, 'data' => $result, 'game' => $result->game,]);
        } else {
            return response()->json(['status' => 0, 'message' => 'Not Found',]);
        }
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'result_id' => 'required|exists:game_results,id',
            'new_result' => 'required|digits:2',
            'game_id' => 'required|exists:games,id',
        ]);

        if (!$validator->passes()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }
        try {
            DB::beginTransaction();
            $game = Game::findOrFail($request->game_id);
            $exResult = Result::findOrFail($request->result_id);

            $reward = $game->reward ?? 1; // default to 1 if not set
            $number = $request->new_result;
            $numberA = substr($number, 0, 1);
            $numberB = substr($number, -1);
            $date = $exResult->number_date;

            Result::where([
                ['game_id', $request->game_id],
                ['number_date', $date],
                ['type', 'ah']
            ])->update(['number' => $numberA]);

            Result::where([
                ['game_id', $request->game_id],
                ['number_date', $date],
                ['type', 'bh']
            ])->update(['number' => $numberB]);

            Result::where([
                ['game_id', $request->game_id],
                ['number_date', $date],
                ['type', 'jodi']
            ])->update(['number' => $number]);

            $types = ['jodi' => $number, 'ah' => $numberA, 'bh' => $numberB];

            foreach ($types as $type => $value) {
                $bids = Bid::where('gameid', $request->game_id)
                    ->where('time', $date)
                    ->where('number', $value)
                    ->where('type', $type)
                    ->get();

                foreach ($bids as $bid) {
                    $user = User::where('phone', $bid->phone)->first();
                    if (!$user) continue;

                    $winAmount = $type === 'jodi'
                        ? $bid->value * $reward
                        : $bid->value * ($reward / 10);

                    $user->wallet += $winAmount;
                    $user->save();

                    Gamewiner::create([
                        'phone' => $bid->phone,
                        'gameid' => $request->game_id,
                        'number' => $value,
                        'amount' => $bid->value,
                        'winamount' => $winAmount,
                        'time' => $date,
                        'type' => $type,
                        'date' => $date,
                    ]);

                    Trans::create([
                        'phone' => $bid->phone,
                        'amount' => $winAmount,
                        'subject' => 'You Win ' . $game->name . ' (' . $bid->playtype . ')',
                        'time' => now('Asia/Kolkata')->format('d-m-Y'),
                        'mobile' => $bid->phone,
                        'wallet' => $user->wallet,
                        'status' => 1,
                        'type' => 'CREDIT',
                        'gameid' => $request->game_id,
                        'gametype' => $type,
                    ]);
                }
            }

            $msg = "Today Result is $number for " . $game->name;
            Notification::create([
                'name' => $msg,
                'subject' => 'Play Online Khaiwal Result',
                'time' => now('Asia/Kolkata')->format('d-m-Y'),
                'phone' => 0,
            ]);


            DB::commit();
            return response()->json(['status' => 1, 'message' => 'Result updated and winners processed successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'message' => 'Something went wrong. Please try again ' . $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        // ----------------
        DB::beginTransaction();

        try {
            $Eraser = [
                'number' => 'Wait',
            ];
            $result = Result::where('game_id', $request->id)
                ->where('number_date', $request->number_date)
                ->update($Eraser);
            $winners = GameWiner::where('gameid', $request->id)
                ->where('time', $request->number_date)
                ->get();
            foreach ($winners as $winner) {
                $user = User::where('phone', $winner->phone)->first();
                if ($user) {
                    $user->wallet -= $winner->winamount;
                    $user->save();
                }
            }
            // Delete from tbl_gamewiner
            GameWiner::where('gameid', $request->id)
                ->where('time', $request->number_date)
                ->delete();

            // Delete from trans
            Trans::where('gameid', $request->id)
                ->where('time', $request->number_date)
                ->delete();

            DB::commit();
            return response()->json(['status' => 1, 'message' => 'result updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'message' => 'something went wrong, please contact developer']);
        }
        // ----------------
    }

    public function addfunction(Request $request)
    {
        $action = [];
        $game = Game::get();
        foreach ($game as $value) {
            $game_id = $value->id;
            $modifiedImmutable = CarbonImmutable::now()->add(1, 'day');
            $action = [
                'number' => 'Wait',
                'number_date' => $modifiedImmutable->format('Y-m-d'),
                'game_id' => $game_id,
                'type' => 'ah',
            ];
            $action1 = [
                'number' => 'Wait',
                'number_date' => $modifiedImmutable->format('Y-m-d'),
                'game_id' => $game_id,
                'type' => 'bh',
            ];
            $action2 = [
                'number' => 'Wait',
                'number_date' => $modifiedImmutable->format('Y-m-d'),
                'game_id' => $game_id,
                'type' => 'jodi',
            ];
            $data = Result::create($action);
            $data = Result::create($action1);
            $data = Result::create($action2);
        }
        echo 'Ho gya';
    }
}
