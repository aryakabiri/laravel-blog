<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users', 'email'],
            'phone_number' => ['required', 'string', 'max:11', 'unique:users,phone_number'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => $validator->messages(),
                'message' => 'validation error.',
            ];
            return response()->json($response, 422);
        }

        $user = new User();
        $user->name = $request->get("name");
        $user->password = bcrypt($request->get("password"));
        $user->phone_number = $request->get("phone_number");
        $user->email = $request->get("email");
        $user->save();

        $user->sendEmailVerificationNotification();

        $response = [
            'success' => true,
            'data' => ['token' => $user->createToken('API Token')->plainTextToken],
            'message' => 'user registered successfully.',
        ];
        return response()->json($response);

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'string', 'max:11'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => $validator->messages(),
                'message' => 'validation error.',
            ];
            return response()->json($response, 422);
        }

        if (!Auth::attempt($request->only('phone_number', 'password'))){
            $response = [
                'success' => false,
                'data' => $validator->messages(),
                'message' => 'Credentials not match.',
            ];
            return response()->json($response, 401);
        }

        $response = [
            'success' => true,
            'data' => ['token' => auth()->user()->createToken('API Token')->plainTextToken],
            'message' => 'user registered successfully.',
        ];
        return response()->json($response);

    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return [
            'message' => 'Tokens Revoked'
        ];
    }

    public function emailVerification(Request $request)
    {
            $user = User::find($request->route('id'));

            if(!$user){
                abort(404);
            }
            if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
                abort(404);
            }
            if ($user->markEmailAsVerified())
                event(new \Illuminate\Auth\Events\Verified($user));

            echo "email verified";

    }
}
