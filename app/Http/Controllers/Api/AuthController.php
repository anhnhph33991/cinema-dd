<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoginRequest;
use App\Http\Requests\StoreRegisterRequest;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\ErrorResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponseTrait, ErrorResponse;
    public function login(StoreLoginRequest $request)
    {
        $credentials = $request->validated();

        $user = User::query()->firstWhere('email', $credentials['email']);

        if (!$user) {
            return $this->errorResponse(
                'Tài khoản không tồn tại',
                Response::HTTP_NOT_FOUND
            );
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return $this->errorResponse('Thông tin tài khoản chưa chính xác', Response::HTTP_NOT_FOUND);
        }

        $token = $user->createToken(env('APP_NAME'))->plainTextToken;

        return $this->successResponse(['token' => $token], 'Login success');
    }

    public function register(StoreRegisterRequest $request)
    {
        $data = $request->validated();

        $user = User::create($data);

        $token = $user->createToken(env('APP_NAME'))->plainTextToken;

        return $this->successResponse(
            $token,
            'Tạo tài khoản thành công',
            Response::HTTP_CREATED
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Đăng xuất thành công');
    }
}
