<?php

namespace App\Http\Controllers;

use App\Models\UserTotalModel;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserTotal extends Controller
{
    //
    public function getUserTotals(Request $request)
    {
        $validator = $this->validate($request, [
            'user_id' => 'required'
        ]);
        //if validation fails return the error
        // if($validator->fails()){
        //     return response()->json([
        //         'message' => 'failure',
        //         'data' => $validator->errors()
        //     ],Response::HTTP_BAD_REQUEST);
        // }
        //get the user totals basing on the user id and created at is today
        $userTotals = UserTotalModel::where('user_id', $request->user_id)->whereDate('updated_at', today())->get();
        //if usrr totals are not empty return the user totals
        if ($userTotals) {
            return response()->json([
                'message' => 'success',
                'data' => $userTotals
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'failure',
                'data' => 'No user totals found'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    //get all user todays order by updated_at
    public function getAllUserTotals(Request $request)
    {

        $validator = $this->validate($request, [
            'user_id' => 'required'
        ]);


        $userTotals = UserTotalModel::where('user_id', $request->user_id)->orderBy('updated_at', 'desc')->get();

        if ($userTotals->isNotEmpty()) {
            return response()->json([
                'message' => 'success',
                'data' => $userTotals
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'message' => 'success',
                'data' =>[]
            ], Response::HTTP_OK);
        }
    }
}
