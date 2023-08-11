<?php

use App\Http\Controllers\Auth\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Agents\FuelAgentController;
use App\Http\Controllers\Boda\BodaUserController;
use App\Http\Controllers\Data\DataController;
use App\Http\Controllers\FileUpload\FileUploadController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\Stages\StageController;
use App\Http\Controllers\Stations\StationController;
use App\Http\Controllers\UserTotal;
use App\Models\Boda\BodaUserModel;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post("/login", [UserController::class, "login"])->name("login");
Route::post("/uploadIDs", [FileUploadController::class, "uploadIdImages"]);
Route::post("/profileUploads", [FileUploadController::class, "profileUploads"]);

    //register boda user
    Route::post("/registerbodauser", [BodaUserController::class, "registerBodaUser"]);
    //register boda user

// Route::middleware('auth:sanctum')->group(function () {
    Route::post("/logout", [UserController::class, "logout"]);

    //fetch districts
    Route::get("/districts", [DataController::class, "getDistricts"]);

    //fetch counties
    Route::post("/counties", [DataController::class, "getCounties"]);

    //fetch sub counties
    Route::post("/subcounties", [DataController::class, "getSubCounties"]);
    //fetch parishes

    Route::post("/parishes", [DataController::class, "getParishes"]);

    //fetch villages
    Route::post("/villages", [DataController::class, "getVillages"]);

    //register fuel station
    Route::post("registerfuelstation", [StationController::class, "registerStation"]);
    //register fuel station

    //fetch stations
    Route::get("/stations", [StationController::class, "fetchstations"]);
    //fetch stations
    //fetch stations by county
    Route::post("/fetchstationsbycounty", [StationController::class, "fetchstationsbycounty"]);
    //fetch stations by county

    //register stage
    Route::post("/registerstage", [StageController::class, "registerStage"]);
    //register stage

    //register boda user
    Route::post("/registerbodauser", [BodaUserController::class, "registerBodaUser"]);
    Route::post("updateBodaUser", [BodaUserController::class, "updateBodaUser"]);
    Route::post("getSuspendedBodaRiders", [BodaUserController::class, "getSuspendedBodaRiders"]);
    //register boda user

    //fetch stages
    Route::get("/stages", [StageController::class, "fetchstages"]);
    //fetch stages

    //agents
    Route::post("/registeragent", [FuelAgentController::class, "registerAgent"]);

    //get user totals
    Route::post("/getusertotals", [UserTotal::class, "getUserTotals"]);
    Route::post("/getallusertotals", [UserTotal::class, "getAllUserTotals"]);


    //stage details
    Route::post("getRegisteredStages", [StageController::class , "getRegisteredStages"]);
    Route::post("getSpecificStageOnBoarded", [StageController::class, "getSpecificStageOnBoarded"]);
    Route::post("activateRegisteredStage", [StageController::class , "activateRegisteredStage"]);
    Route::post("getSuspendedStages", [StageController::class, "getSuspendedStages"]);
    //stage details

    Route::post("unPaidLoans", [LoanController::class , "unPaidLoans"]);
    Route::post("paidLoans", [LoanController::class , "unPaidLoans"]);



// });


//Route::post("register", [UserController::class, "register"])->name("register");
//0759613918
