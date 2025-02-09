<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovieRequest;
use App\Models\Movie;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $movies = Movie::query()->orderByDesc('id')->get();

            return $this->successResponse($movies, 'Danh sách phim');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMovieRequest $request)
    {
        $data = $request->validated();
        try {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::uuid();

            if ($request->hasFile('img_thumbnail')) {
                $data['img_thumbnail'] = 'new Image';
            }

            Movie::query()->create($data);

            return $this->successResponse($data, 'Tạo phim thành công');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $slug)
    {
        try {
            $movie = Movie::query()->firstWhere('slug', $slug);

            if (!$movie) {
                throw new \Exception('Phim không tồn tại', Response::HTTP_NOT_FOUND);
            }

            return $this->successResponse($movie, 'okoke');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            return $this->successResponse(null, 'Hiện tại chưa hỗ trợ xóa phim');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }
}
