<?php

namespace App\Services\Contracts;

use App\Models\Contract;
use App\Models\ContractCargo;
use Carbon\Carbon;

class UpdateContractCargoProgress
{

    protected CheckForContractCompletion $checkForContractCompletion;

    public function __construct(CheckForContractCompletion $checkForContractCompletion)
    {
        $this->checkForContractCompletion = $checkForContractCompletion;
    }

    public function execute($cargo, string $icao)
    {
        $contractCargo = ContractCargo::find($cargo);
        $contract = Contract::find($contractCargo->contract_id);
        $contractCargo->current_airport_id = $icao;
        $contractCargo->save();

        // check if cargo item is completed

        if ($icao == $contract->arr_airport_id) {
            $contractCargo->is_completed = true;
            $contractCargo->completed_at = Carbon::now();
            $contractCargo->save();
        }

        $this->checkForContractCompletion->execute($contract);
    }
}
