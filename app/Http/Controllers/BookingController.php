<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function getBookedDates($propertyId)
{
    $bookings = Booking::where('property_id', $propertyId)
                       ->whereIn('status', ['confirmed', 'pending']) // filtre pour les réservations confirmées
                       ->get(['start_date', 'end_date']);

    return response()->json($bookings);
}

public function store(Request $request){
    $validator = Validator::make($request->all(), [
        'property_id' => 'required|integer|exists:properties,id',
        'start_date' => 'required|date|after_or_equal:today',
        'end_date' => 'required|date|after:start_date',
        'total_price' => 'required|numeric|min:0',
        'status' => 'required|string|in:confirmed,pending,refused',
    ]);
    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
    $userId = auth()->id();
    $booking = $validator->validated();
    $booking['tenant_id'] = $userId;
    $booking = Booking::create($booking);
    return response()->json(['message' => 'Booking created successfully', 'booking' => $booking], 201);
}

public function getMyBooking()
{
    $bookings = Booking::where('tenant_id', auth()->id())
                       ->with('property')
                       ->get();

    return response()->json($bookings);
}

public function getBookingsRequest()
{
    $ownerId = auth()->id();

        $bookings = Booking::whereHas('property', function ($query) use ($ownerId) {
            $query->where('owner_id', $ownerId);
        })->with('property')->get();

    return response()->json($bookings);
}
public function update(Request $request, $id){
    $booking = Booking::find($id);

    if (!$booking) {
        return response()->json(['error' => 'Booking not found'], 404);
    }

    $booking->status = $request->input("status");
    $booking->save();

    return response()->json($booking, 200);
}
}
