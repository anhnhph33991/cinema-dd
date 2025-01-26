<?php

namespace App\Services;

use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService extends BaseService
{
    /**
     * @var UserRepository $repository
     */

    public function getRepository()
    {
        return UserRepository::class;
    }

    public function getUsers()
    {
        try {
            return $this->repository->getAll();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function fetchUser($id)
    {
        try {
            $user = $this->repository->find($id);

            if (!$user) {
                throw new \Exception('Not found user', Response::HTTP_NOT_FOUND);
            }

            return new UserResource($user);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function createUser(StoreUserRequest $request)
    {
        $data = $request->validated();
        try {
            $data['password'] = Hash::make($data['password']);

            if ($request->hasFile('avatar')) {
                $data['avatar'] = Storage::put('users', $request->file('avatar'));
            }
            return $this->repository->create($data);
        } catch (\Throwable $th) {
            throw $th;

            if ($data['image'] && Storage::exists($data['image'])) {
                Storage::delete($data['image']);
            }
        }
    }

    public function updateUser(Request $request, $id)
    {
        try {
            $user = $this->repository->update($id, $request->all());

            if (!$user) {
                throw new \Exception('User Not Found', Response::HTTP_NOT_FOUND);
            }

            return $user;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function deleteUser($id)
    {
        try {
            $result = $this->repository->delete($id);

            return $result;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
