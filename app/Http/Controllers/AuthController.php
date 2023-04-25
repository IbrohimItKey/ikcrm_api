<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use http\Env\Response;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Auth"},
     *     summary="Register with your email",
     *     operationId="Register",
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="first_name",
     *                     description="write your first name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     description="write your last name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="middle_name",
     *                     description="write your middle name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     description="write your email",
     *                     type="email"
     *                 ),
     *                 @OA\Property(
     *                     property="status",
     *                     description="write your status",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="role_id",
     *                     description="Integer role_id must be select option",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="write your password",
     *                     type="password"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     description="write your password again",
     *                     type="password"
     *                 ),
     *                 @OA\Property(
     *                     property="avatar",
     *                     description="upload your photo",
     *                     type="file"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
   public function Register(UserRequest $request){

       $user = User::create([
          'first_name'=>$request->first_name,
          'last_name'=>$request->last_name,
          'middle_name'=>$request->middle_name,
          'email'=>$request->email,
          'status'=>$request->status,
          'role_id'=>$request->role_id,
          'password'=>bcrypt($request->password),
          'avatar'=>$request->avatar,
       ]);
       $token = $user->createToken('myapptoken')->plainTextToken;
       $response = [
         'user'=>$user,
         'token'=>$token
       ];
       return response($response, 201);
   }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Auth"},
     *     summary="Login with your email",
     *     operationId="Login",
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="email",
     *                     description="write your email",
     *                     type="string",
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     description="write your password",
     *                     type="string"
     *                 )
     *             )
     *         )
     *     )
     * )
     */
   public function Login(Request $request){
       $fields = $request->validate([
          'email'=>'required|string',
          'password'=>'required|string'
       ]);
       $user = User::select('id', 'first_name', 'last_name', 'middle_name', 'avatar AS image', 'password')->where('email', $fields['email'])->first();
       if(!$user||!Hash::check($fields['password'], $user->password)){
           return response(['message'=>'bad creds', 401]);
       }
       $token = $user->createToken('myapptoken')->plainTextToken;
       $user->token = $token;
       $user->save();
       $response = [
           'status'=>true,
           'message'=>'Success',
           'token'=>$token,
           'token_expired_date'=>date('Y-m-d H:i:s', strtotime('+24 hours')),
           'user'=>$user
       ];
       return response($response, 201);
   }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Auth"},
     *     summary="Logout",
     *     operationId="Logout",
     *     @OA\Response(
     *         response=405,
     *         description="Invalid input"
     *     ),
     *     @OA\RequestBody(
     *         description="Input data format",
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 type="object",
     *             )
     *         )
     *     ),
     *     security={
     *         {"bearer_token": {}}
     *     }
     * )
     */
    public function Logout() {
        auth()->user()->tokens()->delete();
        $response = [
            'status'=>true,
            'message'=>'Logged out'
        ];
        return response($response);
    }
}
