<?php

namespace App\Services;

use App\Models\Enums\PirepState;
use App\Models\Pirep;
use App\Models\Rank;
use App\Models\User;

class RankService
{
    public function checkRank(int $userId)
    {
        $user = User::find($userId);
        // get users rank
        $currentRank = Rank::find($user->rank_id);
        if ($nextRank = Rank::find($currentRank->id + 1)) {
            // check users hours and points vs next ranks requirements
            if ($user->flights_time >= $nextRank->hours && $user->points >= $nextRank->points) {
                // upgrade to next rank
                $user->rank_id = $nextRank->id;
                $user->save();
            }
        }
    }
}
