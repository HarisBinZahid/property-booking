<?php

namespace App\OpenApi;

/**
 * Common reusable Schemas
 *
 * @OA\Schema(
 *   schema="Property",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="user_id", type="integer", example=1),
 *   @OA\Property(property="title", type="string", example="Cozy Studio"),
 *   @OA\Property(property="description", type="string"),
 *   @OA\Property(property="price_per_night", type="number", format="float", example=89.99),
 *   @OA\Property(property="location", type="string", example="Dubai Marina"),
 *   @OA\Property(property="amenities", type="array", @OA\Items(type="string")),
 *   @OA\Property(property="images", type="array", @OA\Items(type="string")),
 * )
 *
 * @OA\Schema(
 *   schema="Availability",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="property_id", type="integer"),
 *   @OA\Property(property="start_date", type="string", format="date", example="2025-10-01"),
 *   @OA\Property(property="end_date", type="string", format="date", example="2025-10-15"),
 * )
 *
 * @OA\Schema(
 *   schema="Booking",
 *   type="object",
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="property_id", type="integer"),
 *   @OA\Property(property="guest_id", type="integer"),
 *   @OA\Property(property="start_date", type="string", format="date"),
 *   @OA\Property(property="end_date", type="string", format="date"),
 *   @OA\Property(property="status", type="string", enum={"pending","confirmed","rejected"}, example="pending")
 * )
 *
 * @OA\Schema(
 *   schema="LoginRequest",
 *   required={"email","password"},
 *   @OA\Property(property="email", type="string", format="email", example="admin@example.com"),
 *   @OA\Property(property="password", type="string", example="password")
 * )
 *
 * @OA\Schema(
 *   schema="LoginResponse",
 *   @OA\Property(property="token", type="string", example="1|abcdef..."),
 *   @OA\Property(property="user", type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="role", type="string", enum={"admin","guest"})
 *   )
 * )
 */
class Schemas {}
