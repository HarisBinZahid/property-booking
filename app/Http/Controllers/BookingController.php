<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Services\BookingService;
use App\Models\Booking;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private BookingService $service) {}

    /**
     * @OA\Post(
     *   path="/api/bookings",
     *   tags={"Bookings"},
     *   security={{"bearerAuth":{}}},
     *   summary="Create booking (guest)",
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(
     *       required={"property_id","start_date","end_date"},
     *       @OA\Property(property="property_id", type="integer"),
     *       @OA\Property(property="start_date", type="string", format="date"),
     *       @OA\Property(property="end_date", type="string", format="date")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Created",
     *     @OA\JsonContent(ref="#/components/schemas/Booking")
     *   ),
     *   @OA\Response(response=422, description="Invalid date range or conflict")
     * )
     */
    public function store(StoreBookingRequest $request)
    {
        $guest = $request->user();

        $property = Property::findOrFail($request->property_id);

        $booking = $this->service->validateAndCreateBooking(
            $guest,
            $property,
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date)
        );

        return response()->json($booking, 201);
    }

    /**
     * @OA\Get(
     *   path="/api/my-bookings",
     *   tags={"Bookings"},
     *   security={{"bearerAuth":{}}},
     *   summary="List current user's bookings",
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Booking"))
     *   )
     * )
     */
    public function myBookings(Request $request)
    {
        return $request->user()->bookings()->with('property')->get();
    }

    /**
     * @OA\Get(
     *   path="/api/bookings",
     *   tags={"Bookings"},
     *   security={{"bearerAuth":{}}},
     *   summary="List all bookings (admin)",
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Booking"))
     *   ),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        return Booking::with(['property', 'guest'])->paginate(15);
    }

    /**
     * @OA\Put(
     *   path="/api/bookings/{id}/status",
     *   tags={"Bookings"},
     *   security={{"bearerAuth":{}}},
     *   summary="Update booking status (admin)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(
     *       required={"status"},
     *       @OA\Property(property="status", type="string", enum={"pending","confirmed","rejected"})
     *     )
     *   ),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(ref="#/components/schemas/Booking")
     *   ),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $this->authorizeAdmin($request);

        $request->validate(['status' => 'required|in:pending,confirmed,rejected']);

        $booking->update(['status' => $request->status]);

        return $booking;
    }


    private function authorizeAdmin(Request $request)
    {
        if (!$request->user()->isAdmin()) abort(403);
    }
}
