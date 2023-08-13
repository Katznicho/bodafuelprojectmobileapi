<?php

namespace App\Http\Controllers\Boda;

use App\Http\Controllers\Controller;
use App\Models\Boda\BodaUserModel;
use App\Models\Stages\StageModel;
use App\Models\UserTotalModel;
use App\Traits\LogTrait;
use Bodauser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BodaUserController extends Controller
{
    use LogTrait;
    //

    public function getDailyBodaRiders(Request $request){
        $this->validate($request,[
            'user_id' => 'required'
        ]);
        //get the daily boda riders basing on the user id and created at is today
        $dailyBodaRiders = BodaUserModel::where('user_id',$request->user_id)->whereDate('created_at',today())->get();
        //return the daily boda riders
        return response()->json([
            'dailyBodaRiders' => $dailyBodaRiders
        ],Response::HTTP_OK);

    }

    public function getSuspendedBodaRiders(Request $request){
        $validator = $this->validate($request, [
            'user_id' => 'required'
        ]);
         $boda_riders =  BodaUserModel::where(
            [
                ["user_id", $request->user_id],
                ["bodaUserStatus", "=", 3]
            ]

            )
         ->get();

         if(count($boda_riders)>0){
            return response(["message" => "success", "data" => $boda_riders, "statusCode" => Response::HTTP_OK], Response::HTTP_OK);
         }
         else{
            return response(["message" => "failure", "data" => []], Response::HTTP_OK);
         }

    }

    private function trimNumber($number)
    {
        $phone = $number;
        if (strpos($number, "+256") !== false) {
            $phone =   str_replace("+256", "0", $number);
        }
        if (strpos($number, "256") !== false) {
            $number =  str_replace("256", "0", $number);
        }

        return $phone;
    }

    public function registerBodaUser(Request $request)
    {
        // $loggedInUser = Auth::user();
        $this->validate($request, [
            'bodaUserName' => "required",
            "bodaUserStatus" => "0",
            'bodaUserNIN' => "required",
            'bodaUserPhoneNumber' => "required",
            "bodaUserBodaNumber" => "required",
            "bodaUserBackPhoto" => "required",
            "bodaUserFrontPhoto" => "required",
            "bodaUserRole" => "required",
            "stageId" => "required",
            "longitude" => "required",
            "latitude" => "required",
            'riderPhoto'=>'required',
            'motorcyclePhoto'=>'required'
        ]);
        $phone =  $this->trimNumber($request->bodaUserPhoneNumber);
        //check if the boda user exists using the phone number , boda number and nin number
        $bodaUser = BodaUserModel::where("bodaUserPhoneNumber", $phone)
            ->orWhere("bodaUserBodaNumber", $request->bodaUserBodaNumber)
            ->orWhere("bodaUserNIN", $request->bodaUserNIN)
            ->first();
          //if the boda user exists return the boda user
        if ($bodaUser) {
            return response(["message" => "failure", "data" => "Boda user already exisits", "statusCode" => Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
        }
        //get fuel station id
        $id = StageModel::where("stageId", "=", $request->stageId)->get()->pluck("fuelStationId")[0];
        //dd($id);
        //return

        //check role
        $chairmanId = 0;
        $chairmanId = 0;
        if (strtoupper($request->bodaUserRole) == strtoupper("chairman")) {
            $this->validate($request, ['secondNumber' => "required"]);
            if (BodaUserModel::all()->count() == 0) {
                $chairmanId = 1;
            } else {
                $id = BodaUserModel::all()->last()->bodaUserId;
                $chairmanId = $id + 1;
            }

            //update stage chairman id
            StageModel::where("stageId", $request->stageId)->update(['chairmanId' => $chairmanId]);

            //create boda user
            $bodaUser =  BodauserModel::create([
                'bodaUserName' => $request->bodaUserName,
                "bodaUserStatus" => "0",
                'bodaUserNIN' => $request->bodaUserNIN,
                'bodaUserPhoneNumber' => $phone,
                "bodaUserBodaNumber" => $request->bodaUserBodaNumber,
                "bodaUserBackPhoto" => $request->bodaUserBackPhoto,
                "riderPhoto"=>$request->riderPhoto,
                "motorcyclePhoto"=>$request->motorcyclePhoto,
                "bodaUserFrontPhoto" => $request->bodaUserFrontPhoto,
                "bodaUserRole" => $request->bodaUserRole,
                "alternativePhotoNumber" => $request->secondNumber,
                'fuelStationId' => $id,
                "stageId" => $request->stageId,
                "latitude" => $request->latitude,
                "longitude" => $request->longitude,
                'riderPhoto'=>$request->riderPhoto,
                'motorcylePhoto'=>$request->motorcyclePhoto,
                'user_id'=>$request->user_id,
            ]);

            if ($bodaUser) {
                $this->createActivityLog("Registered Boda User", "Boda User Registered successfully");
                return response(["message" => "success", "data" => $bodaUser, "statusCode" => Response::HTTP_OK], Response::HTTP_OK);
            }
        } else {
            //BodaUserModel::create([]);
            $bodaUser =  BodauserModel::create(
                [
                    'bodaUserName' => $request->bodaUserName,
                    "bodaUserStatus" => "0",
                    'bodaUserNIN' => $request->bodaUserNIN,
                    'bodaUserPhoneNumber' => $phone,
                    "bodaUserBodaNumber" => $request->bodaUserBodaNumber,
                    "bodaUserBackPhoto" => $request->bodaUserBackPhoto,
                    "bodaUserFrontPhoto" => $request->bodaUserFrontPhoto,
                    "bodaUserRole" => $request->bodaUserRole,
                    'fuelStationId' => $request->fuelStationId,
                    "stageId" => $request->stageId,
                    "latitude" => $request->latitude,
                    "longitude" => $request->longitude,
                    'user_id'=>$request->user_id,
                    'riderPhoto'=>$request->riderPhoto,
                    'motorcylePhoto'=>$request->motorcyclePhoto,
                ]
            );

            if ($bodaUser) {
                 //after creating boda user check if the user is in user_totals
                 $adminId =  $request->user_id;
                $userTotal = UserTotalModel::where("user_id",$adminId)->first();
                if($userTotal){
                    $userTotal->daily_boda_riders = $userTotal->daily_boda_riders + 1;
                    $userTotal->save();
                }else{
                    UserTotalModel::create([
                        "user_id" => $adminId,
                        "daily_boda_riders" => 1
                    ]);
                }
                    //
                $this->createActivityLog("Registered Boda User", "Boda User Registered successfully");
                return response(["message" => "success", "data" => $bodaUser, "statusCode" => Response::HTTP_OK], Response::HTTP_OK);
            }
        }
    }

    //function to update boda user
    public function updateBodaUser(Request $request){

        $loggedInUser = Auth::user();

        $this->validate($request,[
            'bodaUserId' => 'required',
            'bodaUserName' => "required",
            "bodaUserStatus" => "0",
            'bodaUserNIN' => "required",
            'bodaUserPhoneNumber' => "required",
            "bodaUserBodaNumber" => "required",
            "bodaUserBackPhoto" => "required",
            "bodaUserFrontPhoto" => "required",
            "bodaUserRole" => "required",
            "stageId" => "required",
            "longitude" => "required",
            "latitude" => "required",
            'riderPhoto'=>'required',
            'motorcyclePhoto'=>'required',
            'bodaUserId'=>'required',
            'bodaUserStatus'=>'required'
        ]);
        $phone =  $this->trimNumber($request->bodaUserPhoneNumber);

          //if the boda user exists return the boda user

        //get fuel station id
        $id = StageModel::where("stageId", "=", $request->stageId)->get()->pluck("fuelStationId")[0];
        //dd($id);
        //return

        //check role
        $chairmanId = 0;
        $chairmanId = 0;
        if (strtoupper($request->bodaUserRole) == strtoupper("chairman")) {
            $this->validate($request, ['secondNumber' => "required"]);
            if (BodaUserModel::all()->count() == 0) {
                $chairmanId = 1;
            } else {
                $id = BodaUserModel::all()->last()->bodaUserId;
                $chairmanId = $id + 1;
            }



            //update stage chairman id
                        //update stage chairman id
            StageModel::where("stageId", $request->stageId)->update(['chairmanId' => $chairmanId]);

            //update boda user
            $bodaUser =  BodaUserModel::where('bodaUserId', $request->bodaUserId)->update([
                'bodaUserName' => $request->bodaUserName,
                "bodaUserStatus" =>$request->bodaUserStatus,
                'bodaUserNIN' => $request->bodaUserNIN,
                'bodaUserPhoneNumber' => $phone,
                "bodaUserBodaNumber" => $request->bodaUserBodaNumber,
                "bodaUserBackPhoto" => $request->bodaUserBackPhoto,
                "riderPhoto"=>$request->riderPhoto,
                "motorcyclePhoto"=>$request->motorcyclePhoto,
                "bodaUserFrontPhoto" => $request->bodaUserFrontPhoto,
                "bodaUserRole" => $request->bodaUserRole,
                "alternativePhotoNumber" => $request->secondNumber,
                'fuelStationId' => $id,
                "stageId" => $request->stageId,
                "latitude" => $request->latitude,
                "longitude" => $request->longitude,
                'riderPhoto'=>$request->riderPhoto,
                'motorcylePhoto'=>$request->motorcyclePhoto,
                'user_id'=>$loggedInUser->adminId,
            ]);
            if ($bodaUser) {
                $this->createActivityLog("Registered Boda User", "Boda User Registered successfully");
                return response(["message" => "success", "data" => $bodaUser, "statusCode" => Response::HTTP_OK], Response::HTTP_OK);
            }
            else{
                return response(["message" => "failure", "data" => "Boda user updated", "statusCode" => Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
            }
        } else {
             //just update the boda user
              //update boda user
            $bodaUser =  BodaUserModel::where('bodaUserId', $request->bodaUserId)->update([
                'bodaUserName' => $request->bodaUserName,
                "bodaUserStatus" =>$request->bodaUserStatus,
                'bodaUserNIN' => $request->bodaUserNIN,
                'bodaUserPhoneNumber' => $phone,
                "bodaUserBodaNumber" => $request->bodaUserBodaNumber,
                "bodaUserBackPhoto" => $request->bodaUserBackPhoto,
                "riderPhoto"=>$request->riderPhoto,
                "motorcyclePhoto"=>$request->motorcyclePhoto,
                "bodaUserFrontPhoto" => $request->bodaUserFrontPhoto,
                "bodaUserRole" => $request->bodaUserRole,
                "alternativePhotoNumber" => $request->secondNumber,
                'fuelStationId' => $id,
                "stageId" => $request->stageId,
                "latitude" => $request->latitude,
                "longitude" => $request->longitude,
                'riderPhoto'=>$request->riderPhoto,
                'motorcylePhoto'=>$request->motorcyclePhoto,
                'user_id'=>$loggedInUser->adminId,
            ]);
            if ($bodaUser) {
                $this->createActivityLog("Registered Boda User", "Boda User Registered successfully");
                return response(["message" => "success", "data" =>"Boda User updated", "statusCode" => Response::HTTP_OK], Response::HTTP_OK);
            }
            else{
                return response(["message" => "failure", "data" => "Boda user already exisits", "statusCode" => Response::HTTP_BAD_REQUEST], Response::HTTP_BAD_REQUEST);
            }

        }

            //






    }
}
