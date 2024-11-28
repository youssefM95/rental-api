<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\PaymentStatus;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'bookingId' => 'required|integer|exists:bookings,id'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $userId = auth()->id();
        $booking = Booking::find($request->input("bookingId"));
        $paymentStatus = PaymentStatus::firstWhere('name',"pending");
        $paymentMethod = PaymentMethod::firstWhere('name',"card");

        $payment = Payment::create(['booking_id' => $request->input("bookingId"), 'user_id'=>$userId,'payment_status_id'=>$paymentStatus->id,
        'payment_method_id' => $paymentMethod->id, 'amount'=> $booking->total_price, "is_payed"=>true]);
        return response()->json(['message' => 'payment created successfully', 'payment' => $payment], 201);
    }

    public function getUserPayments(){

        $userId = auth()->id();
        $payments =  Payment::where("user_id",$userId)->with(['paymentStatus','paymentMethod'])->get();
        return response()->json($payments, 201);
    }

    public function getPaymentsByUserProperty(){

        $userId = auth()->id();
        $payments = Payment::whereHas('booking.property', function ($query) use ($userId) {
            $query->where('owner_id', $userId)
            ;
        })->with(['paymentStatus','paymentMethod'])->get();
        return response()->json($payments, 201);
    }

    public function accept(Request $request){
        $payment = Payment::find($request->id);

        if (!$payment) {
            return response()->json(['error' => 'payment not found'], 404);
        }
        $paymentStatus = PaymentStatus::firstWhere('name',"accepted");
        $payment->payment_status_id = $paymentStatus->id;
        $payment->save();

        return response()->json($payment, 200);
    }
    public function refuse(Request $request){
        $payment = Payment::find($request->id);

        if (!$payment) {
            return response()->json(['error' => 'payment not found'], 404);
        }
        $booking = Booking::find($payment->booking_id);
        $paymentStatus = PaymentStatus::firstWhere('name',"refused");
        $payment->payment_status_id = $paymentStatus->id;
        $booking->is_payed = false;
        $payment->save();
        $booking->save();
        return response()->json($payment, 200);
    }
}
