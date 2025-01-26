<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    use ApiResponseTrait;

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $users = $this->userService->getUsers();

            return $this->successResponse($users, 'Get all user success');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $result = $this->userService->createUser($request);

            return $this->successResponse(
                new UserResource($result),
                'Create user success',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), $th->getCode());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $user = $this->userService->fetchUser($id);

            return $this->successResponse($user, 'Get user success');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), $th->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = $this->userService->updateUser($request, $id);

            return $this->successResponse($user, 'Update user success');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage(), $th->getCode());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->userService->deleteUser($id);

            return $this->successResponse(
                [],
                "delete success {$id}",
            );
        } catch (\Throwable $th) {
            return $this->errorResponse(
                $th->getMessage(),
            );
        }
    }
}
