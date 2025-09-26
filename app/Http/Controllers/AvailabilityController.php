<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\Availability;
use App\Models\Property;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
     /**
     * @OA\Get(
     *   path="/api/properties/{id}/availabilities",
     *   tags={"Availability"},
     *   security={{"bearerAuth":{}}},
     *   summary="List availability ranges (admin)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Availability"))
     *   )
     * )
     */
    public function index(Property $property)
    {
        return response()->json(
            $property->availabilities()->orderBy('start_date')->get(),
            200
        );
    }

    /**
     * @OA\Post(
     *   path="/api/properties/{id}/availabilities",
     *   tags={"Availability"},
     *   security={{"bearerAuth":{}}},
     *   summary="Add availability (admin)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(
     *       required={"start_date","end_date"},
     *       @OA\Property(property="start_date", type="string", format="date"),
     *       @OA\Property(property="end_date", type="string", format="date")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Created",
     *     @OA\JsonContent(ref="#/components/schemas/Availability")
     *   )
     * )
     */
    public function store(Request $request, Property $property)
    {
        $data = $request->validate([
            'start_date' => ['required','date'],
            'end_date'   => ['required','date'],
        ]);

        if ($data['start_date'] > $data['end_date']) {
            throw ValidationException::withMessages([
                'start_date' => ['Start date must be before end date.'],
            ]);
        }

        // If you disallow same-day windows, keep this. If you allow, remove and adjust tests.
        if ($data['start_date'] === $data['end_date']) {
            throw ValidationException::withMessages([
                'end_date' => ['Same-day availability is not allowed.'],
            ]);
        }

        // INCLUSIVE overlap: overlap if max(start) <= min(end)
        $overlap = Availability::where('property_id', $property->id)
            ->where('start_date', '<=', $data['end_date'])
            ->where('end_date', '>=', $data['start_date'])
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_date' => ['Overlaps an existing availability range.'],
            ]);
        }

        $row = Availability::create([
            'property_id' => $property->id,
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
        ]);

        return response()->json($row, 201);
    }

    /**
     * @OA\Delete(
     *   path="/api/properties/{pid}/availabilities/{aid}",
     *   tags={"Availability"},
     *   security={{"bearerAuth":{}}},
     *   summary="Delete availability (admin)",
     *   @OA\Parameter(name="pid", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="aid", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="No content")
     * )
     */
    public function destroy(Property $property, Availability $availability)
    {
        if ($availability->property_id !== $property->id) {
            abort(404);
        }
        $availability->delete();
        return response()->noContent(); // 204
    }
}
