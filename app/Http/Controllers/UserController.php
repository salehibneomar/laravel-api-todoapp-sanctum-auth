<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends ApiController
{

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)
        ->withoutTrashed()
        ->first();

        if(is_null($user) || Hash::check($request->password, $user->password)==false){
            return $this->errorResponse('Invalid Credentials', 404);
        }

        $token = $user->createToken('bearer_token')->plainTextToken;
        return $this->authSuccessResponse($user, $token);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:60',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|max:32|confirmed',
        ]);

        $user = new User();
        $user->fill($request->only([
            'name',
            'email',
        ]));

        $user->password = Hash::make($request->password);
        if($user->save()){
            return $this->successResponse('Account created successfully');
        }

        return $this->errorResponse('Internal Error occurred', 500);
    }

    public function show()
    {
        $user = User::find(Auth::guard('api_guard')->user()->id);
        if(is_null($user)){
            return $this->errorResponse('User Not Found', 404);
        }
        return $this->singleResponse($user);
    }

    public function update(Request $request)
    {
        $user = User::find(Auth::guard('api_guard')->user()->id);
        if(is_null($user)){
            return $this->errorResponse('User Not Found', 404);
        }

        $request->validate([
            'name' => 'min:3|max:60',
            'email' => 'email|unique:users,email,'.$user->id,
            'password' => 'min:6|max:32|confirmed',
        ]);

        $user->fill($request->only([
            'name',
            'email',
        ]));

        if($request->has('password')){
            $user->password = Hash::make($request->password);
        }

        if($user->isClean()){
            return $this->errorResponse('No change', 422);
        }

        if($user->save()){
            return $this->singleResponse($user, 200, 'Account updated successfully');
        }

        return $this->successResponse('Internal Error occurred', 500);
    }

    public function logout(Request $request)
    {
        $user = User::find(Auth::guard('api_guard')->user()->id);
        $user->tokens()->delete();
        return $this->successResponse('Logout successfully');
    }
}
