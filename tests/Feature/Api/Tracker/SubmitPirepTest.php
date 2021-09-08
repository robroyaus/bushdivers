<?php

namespace Tests\Feature\Api\Tracker;

use App\Events\PirepFiled;
use App\Models\Aircraft;
use App\Models\Booking;
use App\Models\Enums\AircraftState;
use App\Models\Fleet;
use App\Models\Flight;
use App\Models\Pirep;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SubmitPirepTest extends TestCase
{
    use RefreshDatabase;

    protected Model $user;
    protected Model $pirep;
    protected Model $flight;
    protected Model $fleet;
    protected Model $aircraft;
    protected Model $booking;

    public function setUp(): void
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->user = User::factory()->create([
            'rank_id' => 1,
            'flights_time' => 299,
            'points' => 49,
            'created_at' => Carbon::now()->addYears(-2)
        ]);
        $this->fleet = Fleet::factory()->create();
        $this->aircraft = Aircraft::factory()->create([
            'fleet_id' => $this->fleet->id,
            'fuel_onboard' => 50,
            'current_airport_id' => 'AYMR'
        ]);
        $this->flight = Flight::factory()->create([
            'dep_airport_id' => 'AYMR',
            'arr_airport_id' => 'AYMH'
        ]);
        $this->booking = Booking::factory()->create([
            'flight_id' => $this->flight->id,
            'user_id' => $this->user->id
        ]);
        $this->pirep = Pirep::factory()->create([
            'user_id' => $this->user->id,
            'flight_id' => $this->flight->id,
            'booking_id' => $this->booking->id,
            'aircraft_id' => $this->aircraft
        ]);

        $this->booking->has_dispatch = $this->pirep->id;
        $this->booking->save();

    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_pirep_submitted_successfully()
    {
        Artisan::call('db:seed --class=RankSeeder');
        Sanctum::actingAs(
            $this->user,
            ['*']
        );
        $data = [
            'pirep_id' => $this->pirep->id,
            'fuel_used' => 25,
            'distance' => 76,
            'flight_time' => 45,
            'landing_rate' => -149.12,
            'block_off_time'=> Carbon::now()->addHours(-1),
            'block_on_time' => Carbon::now()->addMinutes(-5)
        ];

        $response = $this->putJson('/api/pirep/submit', $data);

        $response->assertStatus(200);
    }

    public function test_pilot_calcs_peformed_when_pirep_submitted()
    {
        Event::fake();

        Sanctum::actingAs(
            $this->user,
            ['*']
        );
        $data = [
            'pirep_id' => $this->pirep->id,
            'fuel_used' => 25,
            'distance' => 76,
            'flight_time' => 45,
            'landing_rate' => -149.12,
            'block_off_time'=> Carbon::now()->addHours(-1),
            'block_on_time' => Carbon::now()->addMinutes(-5)
        ];

        $response = $this->putJson('/api/pirep/submit', $data);

        Event::assertDispatched(PirepFiled::class);
    }

    public function test_pilot_pay_calc_when_pirep_submitted()
    {
        Artisan::call('db:seed --class=RankSeeder');
        Sanctum::actingAs(
            $this->user,
            ['*']
        );
        $data = [
            'pirep_id' => $this->pirep->id,
            'fuel_used' => 25,
            'distance' => 76,
            'flight_time' => 45,
            'landing_rate' => -149.12,
            'block_off_time'=> Carbon::now()->addHours(-1),
            'block_on_time' => Carbon::now()->addMinutes(-5)
        ];

        $this->putJson('/api/pirep/submit', $data);
        $pay = 25.00 * (45 / 60);

        $this->assertDatabaseHas('users', [
            'account_balance' => $pay
        ]);
    }

    public function test_pilot_location_and_flights_updated()
    {
        Artisan::call('db:seed --class=RankSeeder');

        $user = User::factory()->create([
            'rank_id' => 1,
            'flights_time' => 299,
            'points' => 8
        ]);

        $pirep = Pirep::factory()->create([
            'user_id' => $user->id,
            'flight_id' => $this->flight->id,
            'booking_id' => $this->booking->id,
            'aircraft_id' => $this->aircraft
        ]);

        Sanctum::actingAs(
            $user,
            ['*']
        );
        $data = [
            'pirep_id' => $pirep->id,
            'fuel_used' => 25,
            'distance' => 50,
            'flight_time' => 60,
            'landing_rate' => 150,
            'block_off_time'=> Carbon::now()->addHours(-1),
            'block_on_time' => Carbon::now()->addMinutes(-5)
        ];

        $this->putJson('/api/pirep/submit', $data);
        $pay = 25.00 * (45 / 60);

        $pirep = Pirep::find($this->pirep->id);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'current_airport_id' => $this->flight->arr_airport_id,
            'flights_time' => $this->user->flights_time + 60,
            'flights' => $user->flights + 1,
            'points' => 120
        ]);
    }

    public function test_aircraft_location_and_state_updated()
    {
        Artisan::call('db:seed --class=RankSeeder');
        Sanctum::actingAs(
            $this->user,
            ['*']
        );
        $data = [
            'pirep_id' => $this->pirep->id,
            'fuel_used' => 25,
            'distance' => 76,
            'flight_time' => 45,
            'landing_rate' => -149.12,
            'block_off_time'=> Carbon::now()->addHours(-1),
            'block_on_time' => Carbon::now()->addMinutes(-5),
            'submitted_at' => Carbon::now()
        ];

        $location = $this->flight->arr_airport_id;
        $hours = $this->aircraft->flight_time_mins += 45;
        $fuel = $this->aircraft->fuel_onboard -= 25;

        $this->putJson('/api/pirep/submit', $data);

        $pirep = Pirep::where('aircraft_id', $this->aircraft->id)->first();

        $this->assertDatabaseHas('aircraft', [
            'id' => $this->aircraft->id,
            'flight_time_mins' => $hours,
            'fuel_onboard' => $fuel,
            'state' => AircraftState::AVAILABLE,
            'current_airport_id' => $location,
            'last_flight' => $pirep->submitted_at
        ]);
    }

    public function test_pilot_gets_rank_upgraded()
    {
        Artisan::call('db:seed --class=RankSeeder');
        Sanctum::actingAs(
            $this->user,
            ['*']
        );
        $data = [
            'pirep_id' => $this->pirep->id,
            'fuel_used' => 25,
            'distance' => 76,
            'flight_time' => 45,
            'landing_rate' => -149.12,
            'block_off_time'=> Carbon::now()->addHours(-1),
            'block_on_time' => Carbon::now()->addMinutes(-5),
            'submitted_at' => Carbon::now()
        ];

        $this->putJson('/api/pirep/submit', $data);

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'rank_id' => 2
        ]);
    }

    public function test_pilot_gets_award_added()
    {
        Artisan::call('db:seed --class=RankSeeder');
        Artisan::call('db:seed --class=AwardSeeder');
        Sanctum::actingAs(
            $this->user,
            ['*']
        );
        $data = [
            'pirep_id' => $this->pirep->id,
            'fuel_used' => 25,
            'distance' => 76,
            'flight_time' => 45,
            'landing_rate' => -149.12,
            'block_off_time'=> Carbon::now()->addHours(-1),
            'block_on_time' => Carbon::now()->addMinutes(-5),
            'submitted_at' => Carbon::now()
        ];

        $this->putJson('/api/pirep/submit', $data);

        $this->assertDatabaseHas('award_user', [
            'user_id' => $this->user->id,
            'award_id' => 1
        ]);
    }
}
