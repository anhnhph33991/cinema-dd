<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoomController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $rooms = Room::query()->orderByDesc('id')->get();

            return $this->successResponse($rooms, 'Danh sách phòng');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoomRequest $request)
    {
        $data = $request->validated();
        try {
            $data['seat_structures'] = $this->generateSeatStructures();

            Room::create($data);

            return $this->successResponse(
                $data,
                'Tạo phòng thành công',
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    // public function chooseSeat(Request $request, $roomId)
    // {
    //     $request->validate([
    //         'seat_id' => 'required|string',
    //         'user_id' => 'required|exists:users,id',
    //     ]);

    //     // Lấy room từ database
    //     $room = Room::findOrFail($roomId);
    //     $seats = json_decode($room->seat_structures, true);

    //     // Kiểm tra nếu ghế đã được giữ
    //     if ($seats[$request->seat_id]['status'] !== 'available') {
    //         return response()->json(['error' => 'Seat is already taken'], 409);
    //     }

    //     // Cập nhật trạng thái ghế
    //     $seats[$request->seat_id]['user_id'] = $request->user_id;
    //     $seats[$request->seat_id]['status'] = 'holding';
    //     $seats[$request->seat_id]['hold_time'] = now()->addMinutes(10)->timestamp; // 10 phút giữ chỗ

    //     // Lưu vào database
    //     $room->seat_structures = json_encode($seats);
    //     $room->save();

    //     // Cập nhật Firebase
    //     $firebase = (new Factory)
    //         ->withServiceAccount(storage_path('firebase_credentials.json'))
    //         ->withDatabaseUri(env('FIREBASE_DATABASE_URL'))
    //         ->createDatabase();

    //     $firebase->getReference("rooms/{$roomId}/seats/{$request->seat_id}")
    //         ->set($seats[$request->seat_id]);

    //     return response()->json(['message' => 'Seat held successfully']);
    // }

    /**
     * Display the specified resource.
     */
    public function show(string $name)
    {
        try {
            $room = Room::with(['movie:id,name,slug,img_thumbnail,duration,release_date,end_date,trailer_url,is_active,created_at,updated_at'])
                ->where('name', $name)
                ->first();

            if (!$room) {
                throw new \Exception('Phòng không tồn tại', Response::HTTP_NOT_FOUND);
            }

            $seats = json_decode($room['seat_structures'], true);
            $seatMap = [];
            $totalSeats = 0;

            foreach ($seats as $seat) {
                $coordinates_y = $seat['coordinates_y'];
                $coordinates_x = $seat['coordinates_x'];

                if (!isset($seatMap[$coordinates_y])) {
                    $seatMap[$coordinates_y] = [];
                }

                $seat['price'] += $room->surcharge;

                $seatMap[$coordinates_y][$coordinates_x] = $seat;

                if ($seat['type_seat_id'] == 3) {
                    $totalSeats += 2;
                } else {
                    $totalSeats++;
                }
            }

            return $this->successResponse([
                'matrix' => Room::MATRIX,
                'seatMap' => $seatMap,
                'totalSeats' => $totalSeats,
                'room' => $room
            ], "Phòng {$name} thành công");
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    public function generateSeatStructures()
    {
        $rows = range('A', 'L');
        $columns = range(1, 12);
        $seatStructures = [];

        foreach ($rows as $row) {
            foreach ($columns as $column) {
                $type_seat_id = ($row >= 'I') ? '2' : '1';
                $price = $type_seat_id == 1 ? 30000 : 50000;

                $seatStructures[] = [
                    'coordinates_x' => (string) $column,
                    'coordinates_y' => $row,
                    'type_seat_id'  => $type_seat_id,
                    'user_id'       => null,
                    'status'        => 'available',
                    'hold_time'     => null,
                    'price'         => $price
                ];
            }
        }

        return json_encode($seatStructures);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            return $this->successResponse(null, 'Sửa phòng thành công');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }

    public function chooseSeat(Request $request, string $roomId)
    {
        try {
            $room = Room::query()->findOrFail($roomId);

            return $this->successResponse([
                'id' => $roomId,
                'request' => $request->all(),
            ], 'Data từ client gửi lên');
        } catch (\Throwable $th) {

            if ($th instanceof ModelNotFoundException) {
                return $this->errorResponse('Room không tồn tại', Response::HTTP_NOT_FOUND);
            }

            return $this->errorResponse($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $name)
    {
        try {
            $room = Room::query()->firstWhere('name', $name);

            if (!$room) {
                throw new \Exception('Phòng không tồn tại', Response::HTTP_NOT_FOUND);
            }

            $room->delete();

            return $this->successResponse(null, 'Xóa phòng thành công');
        } catch (\Throwable $th) {
            return $this->errorResponse($th->getMessage());
        }
    }
}
