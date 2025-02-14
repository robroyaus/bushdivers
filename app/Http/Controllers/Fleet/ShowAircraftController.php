<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\AccountLedger;
use App\Models\Aircraft;
use App\Models\Airport;
use App\Services\Aircraft\CheckAircraftMaintenanceStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShowAircraftController extends Controller
{
    protected CheckAircraftMaintenanceStatus $checkAircraftMaintenanceStatus;

    public function __construct(CheckAircraftMaintenanceStatus $checkAircraftMaintenanceStatus)
    {
        $this->checkAircraftMaintenanceStatus = $checkAircraftMaintenanceStatus;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, $id): Response
    {
        $aircraft = Aircraft::with('fleet', 'pireps', 'engines', 'maintenance')->find($id);
        $hubs = Airport::where('is_hub', true)->get();
        $maintenanceStatus = $this->checkAircraftMaintenanceStatus->execute($id);

        return Inertia::render('Fleet/Aircraft', ['aircraft' => $aircraft, 'maintenanceStatus' => $maintenanceStatus, 'hubs' => $hubs]);
    }
}
