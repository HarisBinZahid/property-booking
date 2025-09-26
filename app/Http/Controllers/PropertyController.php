<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    /**
     * @OA\Get(
     *   path="/api/properties",
     *   tags={"Properties"},
     *   security={{"bearerAuth":{}}},
     *   summary="List properties (with filters)",
     *   @OA\Parameter(name="location", in="query", @OA\Schema(type="string")),
     *   @OA\Parameter(name="min_price", in="query", @OA\Schema(type="number")),
     *   @OA\Parameter(name="max_price", in="query", @OA\Schema(type="number")),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Property"))
     *   )
     * )
     */
    public function index(Request $request)
    {
        $q = Property::query();


        if ($location = $request->query('location')) $q->where('location', 'like', "%$location%");
        if ($min = $request->query('min_price')) $q->where('price_per_night', '>=', $min);
        if ($max = $request->query('max_price')) $q->where('price_per_night', '<=', $max);


        if ($start = $request->query('start_date')) {
            $end = $request->query('end_date', $start);
            $q->whereHas('availabilities', fn($qa) => $qa->where('start_date', '<=', $start)->where('end_date', '>=', $end));
        }

        return $q->with('availabilities')->paginate(10);
    }

     /**
     * @OA\Get(
     *   path="/api/properties/{id}",
     *   tags={"Properties"},
     *   security={{"bearerAuth":{}}},
     *   summary="Get single property (with availabilities & bookings summary)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(ref="#/components/schemas/Property")
     *   ),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(Property $property)
    {
        return $property->load(['availabilities', 'bookings']);
    }

    /**
     * @OA\Post(
     *   path="/api/properties",
     *   tags={"Properties"},
     *   security={{"bearerAuth":{}}},
     *   summary="Create property (admin)",
     *   @OA\RequestBody(required=true,
     *     @OA\JsonContent(
     *       required={"title","description","price_per_night","location"},
     *       @OA\Property(property="title", type="string"),
     *       @OA\Property(property="description", type="string"),
     *       @OA\Property(property="price_per_night", type="number"),
     *       @OA\Property(property="location", type="string"),
     *       @OA\Property(property="amenities", type="array", @OA\Items(type="string")),
     *       @OA\Property(property="images", type="array", @OA\Items(type="string"))
     *     )
     *   ),
     *   @OA\Response(response=201, description="Created",
     *     @OA\JsonContent(ref="#/components/schemas/Property")
     *   ),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function store(StorePropertyRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        return Property::create($data);
    }

    /**
     * @OA\Put(
     *   path="/api/properties/{id}",
     *   tags={"Properties"},
     *   security={{"bearerAuth":{}}},
     *   summary="Update property (admin)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/Property")),
     *   @OA\Response(response=200, description="OK",
     *     @OA\JsonContent(ref="#/components/schemas/Property")
     *   )
     * )
     */
    public function update(StorePropertyRequest $request, Property $property)
    {
        $property->update($request->validated());
        return $property;
    }

     /**
     * @OA\Delete(
     *   path="/api/properties/{id}",
     *   tags={"Properties"},
     *   security={{"bearerAuth":{}}},
     *   summary="Delete property (admin)",
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *   @OA\Response(response=204, description="No content")
     * )
     */
    public function destroy(Property $property)
    {
        $property->delete();
        return response()->noContent();
    }
}
