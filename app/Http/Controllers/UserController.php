<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserController extends Controller
{
    private User $userModel;

    public function __construct(User $user)
    {
        $this->userModel = $user;
    }

    public function register(Request $request)
    {
        $email = $request->username;

        $user = $this->userModel->where('email', $email)->first();

        if (isset($user)) {
            throw new BadRequestHttpException('Email já cadastrado.');
        }

        $user = $this->userModel->create([
            'name' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'error' => false,
            'token' => $user->createToken('API')->plainTextToken
        ], 201);
    }

    public function login(Request $request)
    {
        $email = $request->username;

        $user = $this->userModel->where('email', $email)->first();

        if (!$user) {
            throw new NotFoundHttpException('User Not Found.');
        }

        if (Hash::check($request->password, $user->password)) {
            $token = $user->createToken('API')->plainTextToken;

            return response()->json([
                'error' => false,
                'token' => $token
            ], 200);
        }
        throw new BadRequestHttpException('Wrong Password.');
    }
}
