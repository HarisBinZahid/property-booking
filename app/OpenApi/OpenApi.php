<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *   version="1.0.0",
 *   title="Mini Property Booking API",
 *   description="Laravel 12 + Sanctum (token) API for properties, availability, and bookings."
 * )
 *
 * @OA\Server(
 *   url=L5_SWAGGER_CONST_HOST,
 *   description="Local API"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="Token",
 *   description="Use the token from /api/login. Header: Authorization: Bearer {token}"
 * )
 */
class OpenApi {}
