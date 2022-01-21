<?php

namespace Tests\Unit\Services\Financial;

use App\Models\Aircraft;
use App\Models\AirlineFees;
use App\Models\Airport;
use App\Models\Contract;
use App\Models\ContractCargo;
use App\Models\Enums\AirlineTransactionTypes;
use App\Models\Enums\FinancialConsts;
use App\Models\Enums\TransactionTypes;
use App\Models\Fleet;
use App\Models\Pirep;
use App\Models\PirepCargo;
use App\Models\User;
use App\Services\Finance\CalcContractPay;
use App\Services\Finance\ProcessPirepFinancials;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractPayTest extends TestCase
{

    use RefreshDatabase;

    protected CalcContractPay $calcContractPay;
    protected ProcessPirepFinancials $processPirepFinancials;
    protected Model $contract;
    protected Model $contractCargo;
    protected Model $pirep;
    protected Model $pirepCargo;
    protected Model $fleet;
    protected Model $aircraft;
    protected Model $aircraftPrivate;
    protected Model $user;
    protected Model $airport;

    protected function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->calcContractPay = $this->app->make(CalcContractPay::class);
        $this->processPirepFinancials = $this->app->make(ProcessPirepFinancials::class);
        $this->user = User::factory()->create();
        $this->fleet = Fleet::factory()->create([
            'fuel_type' => 1,
            'size' => 'S'
        ]);
        $this->aircraft = Aircraft::factory()->create([
            'fleet_id' => $this->fleet->id
        ]);
        $this->aircraftPrivate = Aircraft::factory()->create([
            'fleet_id' => $this->fleet->id,
            'owner_id' => $this->user->id,
            'registration' => 'N12345'
        ]);
        $this->contract = Contract::factory()->create([
            'is_completed' => true,
            'completed_at' => Carbon::now()
        ]);
        $this->contractCargo = ContractCargo::factory()->create([
            'contract_id' => $this->contract->id,
            'is_completed' => true
        ]);

        $this->airport = Airport::factory()->create();

        $this->pirep = Pirep::factory()->create([
            'user_id' => $this->user->id,
            'destination_airport_id' => $this->airport->identifier
        ]);
        $this->pirepCargo = PirepCargo::factory()->create([
            'pirep_id' => $this->pirep->id,
            'contract_cargo_id' => $this->contractCargo->id
        ]);

        AirlineFees::factory()->create([
            'fee_type' => AirlineTransactionTypes::FuelFees,
            'fee_name' => 'Avgas',
            'fee_amount' => 2.15
        ]);
        AirlineFees::factory()->create([
            'fee_type' => AirlineTransactionTypes::GroundHandlingFees,
            'fee_name' => 'Cargo Handling',
            'fee_weight' => 1,
            'fee_amount' => 0.15
        ]);
        AirlineFees::factory()->create([
            'fee_type' => AirlineTransactionTypes::LandingFees,
            'fee_name' => 'Landing Fees - Small',
            'fee_amount' => 35.00
        ]);

    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_company_pay_is_added_to_ledger()
    {
        $expectedPay = $this->contract->contract_value;

        $p = $this->calcContractPay->execute($this->contract->id);
        $this->assertDatabaseHas('account_ledgers', [
            'transaction_type' => AirlineTransactionTypes::ContractIncome,
            'total' => $expectedPay
        ]);
    }

    public function test_pilot_pay_is_returned()
    {
        $expectedPay = (FinancialConsts::PilotPay / 100) * $this->contract->contract_value;
        $companyPay = (FinancialConsts::CompanyPay / 100) * $this->contract->contract_value;
        $totalPay = $expectedPay + $companyPay;
        $pay = $this->calcContractPay->execute($this->contract->id);
        $this->assertEquals($expectedPay, $pay);
        $this->assertEquals($totalPay, $this->contract->contract_value);
    }

    public function test_pilot_pay_is_returned_for_private()
    {
        $expectedPay = (60 / 100) * $this->contract->contract_value;
        $companyPay = (40 / 100) * $this->contract->contract_value;
        $totalPay = $expectedPay + $companyPay;
        $pay = $this->calcContractPay->execute($this->contract->id, null, false, true);
        $this->assertEquals($expectedPay, $pay);
        $this->assertEquals($totalPay, $this->contract->contract_value);
    }

    public function test_pilot_pay_is_returned_for_rental()
    {
        $expectedPay = (60 / 100) * $this->contract->contract_value;
        $companyPay = (40 / 100) * $this->contract->contract_value;
        $totalPay = $expectedPay + $companyPay;
        $pay = $this->calcContractPay->execute($this->contract->id, null, true, false);
        $this->assertEquals($expectedPay, $pay);
        $this->assertEquals($totalPay, $this->contract->contract_value);
    }

    public function test_contract_company_pay_is_made_as_part_of_pirep_process()
    {
        $companyPay = $this->contract->contract_value;

        $this->processPirepFinancials->execute($this->pirep);
        $this->assertDatabaseHas('account_ledgers', [
            'transaction_type' => AirlineTransactionTypes::ContractIncome,
            'total' => $companyPay
        ]);
    }

    public function test_contract_company_pilot_pay_is_made_as_part_of_pirep_process()
    {
        $pilotPay = (FinancialConsts::PilotPay / 100) * $this->contract->contract_value;

        $this->processPirepFinancials->execute($this->pirep);
        $this->assertDatabaseHas('account_ledgers', [
            'transaction_type' => AirlineTransactionTypes::ContractExpenditure,
            'total' => -$pilotPay
        ]);
    }

    public function test_contract_pilot_pay_is_made_as_part_of_pirep_process()
    {
        $pilotPay = (FinancialConsts::PilotPay / 100) * $this->contract->contract_value;

        $this->processPirepFinancials->execute($this->pirep);

        $this->assertDatabaseHas('user_accounts', [
            'type' => TransactionTypes::FlightPay,
            'total' => $pilotPay
        ]);
    }


}
