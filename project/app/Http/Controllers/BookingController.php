<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\FlightProviderService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request, FlightProviderService $flights)
    {
        $validated = $request->validate([
            'flight_id' => ['required', 'string'],
            'passengers' => ['required', 'array', 'min:1'],
            'passengers.*.name' => ['required', 'string'],
            'passengers.*.email' => ['nullable', 'email'],
            'passengers.*.phone' => ['nullable', 'string'],
        ]);

        $passengerCount = count($validated['passengers']);
        $flight = $flights->findById($validated['flight_id'], $passengerCount);

        if (! $flight) {
            return response()->json(['message' => 'Flight not found.'], 404);
        }

        $booking = Booking::create([
            'reference' => $this->bookingReference(),
            'status' => 'confirmed',
            'flight_id' => $flight['id'],
            'provider' => $flight['provider'],
            'carrier' => $flight['carrier'],
            'flight_number' => $flight['flight_number'],
            'from' => $flight['from'],
            'to' => $flight['to'],
            'depart_at' => $flight['depart_at'],
            'arrive_at' => $flight['arrive_at'],
            'stops' => $flight['stops'],
            'price' => $flight['price'],
            'total_price' => $flight['total_price'],
            'currency' => $flight['currency'],
            'passenger_count' => $passengerCount,
            'passengers' => $validated['passengers'],
        ]);

        return response()->json([
            'message' => 'Booking created successfully',
            'booking' => $this->bookingResponse($booking),
        ], 201);
    }

    public function show(string $reference)
    {
        $booking = Booking::where('reference', $reference)->first();

        if (! $booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        return response()->json([
            'booking' => $this->bookingResponse($booking),
        ], 200);
    }

    private function bookingReference(): string
    {
        do {
            $reference = 'BK-'.Str::upper(Str::random(8));
        } while (Booking::where('reference', $reference)->exists());

        return $reference;
    }

    private function bookingResponse(Booking $booking): array
    {
        return [
            'reference' => $booking->reference,
            'status' => $booking->status,
            'flight_id' => $booking->flight_id,
            'provider' => $booking->provider,
            'carrier' => $booking->carrier,
            'flight_number' => $booking->flight_number,
            'from' => $booking->from,
            'to' => $booking->to,
            'depart_at' => str_replace(' ', 'T', (string) $booking->depart_at),
            'arrive_at' => str_replace(' ', 'T', (string) $booking->arrive_at),
            'stops' => $booking->stops,
            'passenger_count' => $booking->passenger_count,
            'passengers' => $booking->passengers,
            'price' => $booking->price,
            'total_price' => $booking->total_price,
            'currency' => $booking->currency,
        ];
    }
}
