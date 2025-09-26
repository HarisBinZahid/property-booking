<?php

namespace App\Http\Services;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function isRangeInsideAvailability(Property $property, Carbon $start, Carbon $end): bool
    {
        return $property->availabilities()
            ->where('start_date', '<=', $start)
            ->where('end_date', '>=', $end)
            ->exists();
    }


    public function hasOverlapWithConfirmedBookings(Property $property, Carbon $start, Carbon $end): bool
    {
        return $property->bookings()
            ->where('status', 'confirmed')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->exists();
    }


    public function validateAndCreateBooking(User $guest, Property $property, Carbon $start, Carbon $end): Booking
    {
        if (!$this->isRangeInsideAvailability($property, $start, $end)) {
            throw ValidationException::withMessages(['date_range' => 'Selected dates are not available.']);
        }

        if ($this->hasOverlapWithConfirmedBookings($property, $start, $end)) {
            throw ValidationException::withMessages(['date_range' => 'Conflict with an existing booking.']);
        }

        return Booking::create([
            'property_id' => $property->id,
            'guest_id' => $guest->id,
            'start_date' => $start,
            'end_date' => $end,
            'status' => 'pending',
        ]);
    }
}
