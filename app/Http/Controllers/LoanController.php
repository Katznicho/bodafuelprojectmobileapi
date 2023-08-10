<?php

namespace App\Http\Controllers;

use App\Models\Boda\BodaUserModel;
use App\Models\LoanModel;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    //boda users who havent paid
    public function unPaidLoans(Request $request){
        $validator = $this->validate($request, [
            'user_id' => 'required',
        ]);

        $bodausers = BodaUserModel::where([
            ['user_id','=' , $request->user_id],
            ['bodaUserStatus', '=' , '2']
        ])
        ->orderBy('updated_at', 'desc')
        ->get();

        return response(["message" => "success", "data" =>$bodausers], Response::HTTP_OK);
    }

    public function paidLoans(Request $request)
    {
        $bodausers = BodaUserModel::where([
            ['user_id', '=', $request->user_id],
            ['bodaUserStatus', '=', '1']
        ])
        ->orderBy('updated_at', 'desc')
        ->get();

        if (count($bodausers) > 0) {
            $paidBodaUsers = collect([]);

            foreach ($bodausers as $boda) {
                $loan = LoanModel::where('boadUserId', $boda->bodaUserPhoneNumber)->first();

                if ($loan && $loan->status == 1 || $loan->status == '1') {
                    $paidBodaUsers->push($boda);
                }
            }

            return response(["message" => "success", "data" => $paidBodaUsers], Response::HTTP_OK);
        } else {
            return response(["message" => "failure", "data" => []], Response::HTTP_OK);
        }
    }
}
