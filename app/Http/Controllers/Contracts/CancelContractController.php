<?php

namespace App\Http\Controllers\Contracts;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\ContractCargo;
use App\Models\Enums\PirepState;
use App\Models\Pirep;
use App\Models\PirepCargo;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CancelContractController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $contract = Contract::find($request->id);

        // check if contract has cargo in a non-completed pirep
        $cargo = ContractCargo::where('contract_id', $contract->id)->pluck('id');
        $pc = PirepCargo::whereIn('contract_cargo_id', $cargo)->pluck('pirep_id');
        $pirepsCount = Pirep::where('user_id', Auth::user()->id)
            ->where('state', '<>', PirepState::ACCEPTED)
            ->whereIn('id', $pc)
            ->count();

        if ($pirepsCount > 0) {
            return redirect()->back()->with(['error' => 'Contract is part of an active dispatch and cannot be cancelled']);
        }

        // set contract to not available
        $contract->is_available = true;
        $contract->user_id = null;
        $contract->save();

        $user = User::find(Auth::user()->id);
        if ($user->points >= 1) {
            $user->points -= 1;
            $user->save();
        }

        return redirect()->back()->with(['success' => 'Contract bid cancelled successfully']);
    }
}
