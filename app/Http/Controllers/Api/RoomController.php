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
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class RoomController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of the resource.
     */

    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path(env('FIREBASE_CREDENTIALS')))
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

        $this->database = $factory->createDatabase();
    }

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

            $reference = $this->database->getReference('rooms/' . $data['name'] . '/seatMap');
            $reference->set($data['seat_structures']);

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
            $roomRef = $this->database->getReference('rooms/' . $name);
            $roomData = $roomRef->getValue();

            if (!$roomData) {
                throw new \Exception('Phòng không tồn tại', Response::HTTP_NOT_FOUND);
            }

            $seatMapRef = $this->database->getReference('rooms/' . $name . '/seatMap');
            $seatMap = $seatMapRef->getValue();

            if (is_string($seatMap)) {
                $seatMap = json_decode($seatMap, true);
            }

            if (!is_array($seatMap)) {
                $seatMap = [];
            }

            $totalSeats = 0;

            foreach ($seatMap as $seats) {
                if (!is_array($seats)) {
                    continue;
                }

                foreach ($seats as $seat) {
                    if (!is_array($seat)) {
                        continue;
                    }

                    if (isset($roomData['surcharge'])) {
                        $seat['price'] += $roomData['surcharge'];
                    }

                    $totalSeats += ($seat['type_seat_id'] == 3) ? 2 : 1;
                }
            }

            return $this->successResponse([
                'matrix' => Room::MATRIX,
                'seatMap' => $seatMap,
                'totalSeats' => $totalSeats,
                'room' => $roomData,
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
                    'seat_id' => $row . '' . (string) $column,
                    'coordinates_x' => (string) $column,
                    'coordinates_y' => $row,
                    'type_seat_id' => $type_seat_id,
                    'user_id' => null,
                    'status' => 'available',
                    'hold_time' => null,
                    'price' => $price
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

    public function chooseSeat(Request $request, string $roomName)
    {
        try {
            $room = Room::query()->firstWhere('name', $roomName);
            if (!$room) {
                throw new ModelNotFoundException('Phòng không tồn tại');
            }

            $seatStructures = $room->seat_structures;

            if (is_string($seatStructures)) {
                $seatStructures = json_decode($seatStructures, true);
            }

            $seatIndex = null;
            foreach ($seatStructures as $index => $seat) {
                if ($seat['seat_id'] == $request->seat_id) {
                    $seatIndex = $index;
                    break;
                }
            }

            if ($seatIndex === null) {
                return $this->errorResponse('Ghế không tồn tại trong phòng', Response::HTTP_NOT_FOUND);
            }

            $seatStructures[$seatIndex] = array_merge($seatStructures[$seatIndex], [
                'status' => $request->status,
                'user_id' => $request->user_id,
            ]);

            $room->seat_structures = $seatStructures;

            $room->save();

            $seatMapRef = $this->database->getReference('rooms/' . $room->name . '/seatMap');
            $seatMap = json_decode($seatMapRef->getValue(), true);

            $seatKey = null;
            foreach ($seatMap as $key => $seat) {
                if (($seat['seat_id'] ?? null) === $request->seat_id) {
                    $seatKey = $key;
                    break;
                }
            }

            $seatMap[$seatKey] = array_merge($seatMap[$seatKey], [
                'status' => $request->status,
                'user_id' => $request->user_id,
            ]);

            $seatMapRef->set(json_encode($seatMap));

            return $this->successResponse([
                'id' => $room->id
            ], 'Data từ client gửi lên');
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                return $this->errorResponse('Room không tồn tại', Response::HTTP_NOT_FOUND);
            }

            return $this->errorResponse($th->getMessage());
        }
    }

    public function updateSeat(Request $request, string $roomName)
    {
        try {
            $room = Room::query()->firstWhere('name', $roomName);
            if (!$room) {
                throw new ModelNotFoundException('Phòng không tồn tại');
            }

            $seatStructures = $room->seat_structures;
            if (is_string($seatStructures)) {
                $seatStructures = json_decode($seatStructures, true);
            }

            foreach ($seatStructures as $seat) {
                if ($seat['user_id'] == $request->user_id) {
                    $seat['status'] = 'available';
                    $seat['user_id'] = null;
                }
            }

            $room->save();

            $seatMapRef = $this->database->getReference('rooms/' . $room->name . '/seatMap');
            $seatMap = $seatMapRef->getValue();

            if (is_string($seatMap)) {
                $seatMap = json_decode($seatMap, true);
            }

            foreach ($seatMap as $seat) {
                if ($seat['user_id'] == $request->user_id) {
                    $seat['status'] = 'available';
                    $seat['user_id'] = null;
                }
            }

            $seatMapRef->set($seatMap);

            return $this->successResponse([
                'id' => $room->id
            ], 'Cập nhật ghế thành công');

        } catch (\Throwable $th) {
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
