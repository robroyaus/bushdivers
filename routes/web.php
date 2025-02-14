<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public pages
Route::get('/', \App\Http\Controllers\General\ShowHomeController::class)
    ->name('home');
Route::get('/privacy', function () {
    return \Inertia\Inertia::render('General/Privacy');
});
Route::get('/ranks', \App\Http\Controllers\General\ShowRanksController::class)
    ->name('ranks');
Route::get('/hubs', \App\Http\Controllers\Airports\ShowHubsController::class)
    ->name('hubs');
Route::get('/staff', \App\Http\Controllers\General\ShowStaffController::class)
    ->name('staff');
Route::get('/supporters', \App\Http\Controllers\General\ShowSupportersController::class)
    ->name('supporters');
Route::get('/live-flights', \App\Http\Controllers\Pireps\ShowLiveFlightsController::class)
    ->name('flights.map');
// Fleet
Route::get('/fleet', \App\Http\Controllers\Fleet\ShowFleetController::class)
    ->name('fleet');

// Auth
Route::get('/register', \App\Http\Controllers\Auth\ShowRegisterFormController::class)
    ->name('register.index');
Route::post('/register', \App\Http\Controllers\Auth\RegisterNewUserController::class)
    ->name('register');
Route::get('/login', \App\Http\Controllers\Auth\ShowLoginFormController::class)
    ->name('login.index');
Route::post('/login', \App\Http\Controllers\Auth\AuthenticateUserController::class)
    ->name('login');
Route::get('/password', \App\Http\Controllers\Auth\ShowPasswordRequestController::class)
    ->name('password.request.index');
Route::post('/password', \App\Http\Controllers\Auth\RequestPasswordResetController::class)
    ->name('password.request');
Route::get('/password/reset/{token}', \App\Http\Controllers\Auth\ShowPasswordResetController::class)
    ->name('password.reset.index');
Route::post('/password/reset', \App\Http\Controllers\Auth\ResetPasswordController::class)
    ->name('password.reset');

Route::middleware('auth')->group(function () {
    Route::get('/logout', \App\Http\Controllers\Auth\LogoutUserController::class)
        ->name('logout');
    Route::get('/finances', \App\Http\Controllers\General\ShowFinancesController::class)
        ->name('company.finances');
    // Crew
    Route::get('/dashboard', \App\Http\Controllers\Crew\ShowDashboardController::class)
        ->name('dashboard');
    Route::get('/intro', \App\Http\Controllers\Crew\ShowIntroController::class)
        ->name('intro');
    Route::get('/roster', \App\Http\Controllers\Crew\ShowPilotRosterController::class)
        ->name('roster');
    Route::get('/profile', \App\Http\Controllers\Crew\ShowProfileController::class)
        ->name('profile.index');
    Route::put('/profile', \App\Http\Controllers\Crew\UpdateProfileController::class)
        ->name('profile.update');
    Route::get('/logbook', \App\Http\Controllers\Pireps\ShowLogbookController::class)
        ->name('logbook');
    Route::get('/logbook/{pirep}', \App\Http\Controllers\Pireps\ShowPirepController::class)
        ->name('logbook.detail');
    Route::get('/jumpseat', \App\Http\Controllers\Crew\ShowJumpseatController::class)
        ->name('jumpseat');
    Route::post('/jumpseat', \App\Http\Controllers\Crew\ProcessJumpseatController::class)
        ->name('jumpseat.process');
    Route::get('/my-finances', \App\Http\Controllers\Crew\ShowUserFinancesController::class)
        ->name('crew.finances');

    Route::get('/resources', \App\Http\Controllers\General\ShowResourcesController::class)
        ->name('resources');

    Route::get('/fleet-aircraft', \App\Http\Controllers\Fleet\ShowFleetAircraftController::class)
        ->name('fleet.aircraft');

    // Airports
    Route::get('/airports/{icao?}', \App\Http\Controllers\Airports\ShowAirportController::class)
        ->name('airport');

    // Aircraft/Fleet
    Route::get('/aircraft/{id}', \App\Http\Controllers\Fleet\ShowAircraftController::class)
        ->name('aircraft');
    Route::get('/rentals/{icao?}', \App\Http\Controllers\Rentals\ShowRentalAircraftController::class)
        ->name('rentals');
    Route::post('/rentals', \App\Http\Controllers\Rentals\RentAircraftController::class)
        ->name('rentals.rent');
    Route::post('/rentals/end/{id}', \App\Http\Controllers\Rentals\EndRentalController::class)
        ->name('rentals.end');
    Route::post('/aircraft/maintenance', \App\Http\Controllers\Fleet\PerformMaintenanceController::class)
        ->name('aircraft.maintenance');


    // Flights
    Route::get('/my-contracts', \App\Http\Controllers\Contracts\ShowActiveContractsController::class)
        ->name('bids');
    Route::get('/completed-contracts', \App\Http\Controllers\Contracts\ShowCompletedContractsController::class)
        ->name('contracts.completed');
    Route::get('/dispatch', \App\Http\Controllers\Dispatch\ShowDispatchController::class)
        ->name('dispatch');
    Route::post('/dispatch', \App\Http\Controllers\Dispatch\CreateDispatchController::class)
        ->name('dispatch.create');
    Route::post('/dispatch/cancel', \App\Http\Controllers\Dispatch\CancelDispatchController::class)
        ->name('dispatch.cancel');
    Route::get('/contracts', \App\Http\Controllers\Contracts\ShowContractsPageController::class)
        ->name('contracts');
    Route::post('/contracts', \App\Http\Controllers\Contracts\FindContractsController::class)
        ->name('contracts.search');
    Route::post('/contracts/bid', \App\Http\Controllers\Contracts\BidForContractController::class)
        ->name('contracts.bid');
    Route::post('/contracts/custom', \App\Http\Controllers\Contracts\CreateCustomRouteController::class)
        ->name('contracts.custom');
    Route::post('/contracts/cancel', \App\Http\Controllers\Contracts\CancelContractController::class)
        ->name('contracts.cancel');
    Route::get('/pireps/submit', \App\Http\Controllers\Pireps\ShowPirepSubmissionController::class)
        ->name('pireps.submit');
    Route::post('/pireps/submit', \App\Http\Controllers\Pireps\ProcessPirepSubmissionController::class)
        ->name('pireps.process');

    Route::middleware('admin')->group(function () {
        Route::get('/admin/pireps', \App\Http\Controllers\Admin\Pireps\ShowPirepsListController::class)
            ->name('admin.pireps');
        Route::get('/admin/users', \App\Http\Controllers\Admin\Users\ShowUsersController::class)
            ->name('admin.users');
        Route::get('/admin/users/admin/{userId}', \App\Http\Controllers\Admin\Users\SetAdminController::class)
            ->name('admin.users.admin');
        Route::get('/admin/users/active/{userId}', \App\Http\Controllers\Admin\Users\SetStatusController::class)
            ->name('admin.users.active');
        Route::get('/admin/fleet', \App\Http\Controllers\Admin\Fleet\ShowFleetListController::class)
            ->name('admin.fleet');
        Route::get('/admin/fleet/create', \App\Http\Controllers\Admin\Fleet\ShowCreateFleetController::class)
            ->name('admin.fleet.create');
        Route::post('/admin/fleet/create', \App\Http\Controllers\Admin\Fleet\CreateFleetController::class)
            ->name('admin.flee.store');
        Route::get('/admin/fleet/edit/{id}', \App\Http\Controllers\Admin\Fleet\ShowUpdateFleetController::class)
            ->name('admin.fleet.edit');
        Route::post('/admin/fleet/edit/{id}', \App\Http\Controllers\Admin\Fleet\UpdateFleetController::class)
            ->name('admin.fleet.update');
        Route::get('/admin/fleet/delete/{id}', \App\Http\Controllers\Admin\Fleet\DeleteFleetController::class)
            ->name('admin.fleet.delete');
        Route::get('/admin/aircraft/create', \App\Http\Controllers\Admin\Fleet\ShowAddAircraftController::class)
            ->name('admin.aircraft.add');
        Route::post('/admin/aircraft/create', \App\Http\Controllers\Admin\Fleet\AddAircraftController::class)
            ->name('admin.aircraft.store');
        Route::get('/admin/aircraft/delete/{id}', \App\Http\Controllers\Admin\Fleet\DeleteAircraftController::class)
            ->name('admin.aircraft.delete');
        Route::post('/pireps/approve', \App\Http\Controllers\Pireps\ApprovePirepController::class)
            ->name('pireps.approve');
        Route::get('/admin/resources', \App\Http\Controllers\Admin\Resources\ShowResourcesController::class)
            ->name('admin.resources');
        Route::post('/admin/categories', \App\Http\Controllers\Admin\Resources\AddResourceCategoryController::class)
            ->name('admin.categories.add');
        Route::post('/admin/resources', \App\Http\Controllers\Admin\Resources\AddResourcesController::class)
            ->name('admin.resources.add');
        Route::delete('/admin/resources/{id}', \App\Http\Controllers\Admin\Resources\RemoveResourcesController::class)
            ->name('admin.resources.delete');
        Route::patch('/admin/resources/{id}', \App\Http\Controllers\Admin\Resources\EditResourcesController::class)
            ->name('admin.resources.edit');
    });
});
