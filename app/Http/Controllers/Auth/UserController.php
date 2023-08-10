<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Traits\LogTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    use LogTrait;


    //
    public function login(Request $request)
    {
        //$this->send

        $this->validate(
            $request,
            [
                'password' => 'required',

                'email' => 'required|email|max:255',


            ]
        );
        if (!Auth::attempt(['email' => $request->email, "password" => $request->password])) {
            return response(["message" => "failure", "data" => "invalid credentials", "statusCode" => Response::HTTP_UNAUTHORIZED]);
        } else {

            $userToken = Auth::user();
            $token = $userToken->createToken("token")->plainTextToken;
            $user_role = Role::where("id", $userToken->roleId)->first();
            $this->createActivityLog("login", "logged in successfully");
            return response([
                "message" => "success",  "data" => ["user" => $userToken , 'role'=>$user_role], "accessToken" => $token,
                "statusCode" => Response::HTTP_OK
            ]);
        }
    }

    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->delete();
        $this->createActivityLog("logout", "logged out successfully");
        return response(["message" => "success", "data" => "logout out successfully", "statusCode" => Response::HTTP_OK], Response::HTTP_ACCEPTED);
    }
}
