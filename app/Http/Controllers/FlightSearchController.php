<?php

namespace App\Http\Controllers;

use App\Services\FlightProviderService;
use Illuminate\Http\Request;

class FlightSearchController extends Controller
{
    public function search(Request $request, FlightProviderService $flights)
    {
        $request->validate([
            'from' => ['required', 'string'],
            'to' => ['required', 'string'],
            'date' => ['required', 'date_format:Y-m-d'],
            'passengers' => ['required', 'integer', 'min:1'],
            'sort' => ['sometimes', 'in:price,depart,stops,duration'],
            'order' => ['sometimes', 'in:asc,desc'],
            'provider' => ['sometimes', 'in:provider-a,provider-b,provider-c'],
            'carrier' => ['sometimes', 'string'],
            'max_stops' => ['sometimes', 'integer', 'min:0'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $passengers = (int) $request->passengers;
        $sort = $request->query('sort', 'price');
        $desc = $request->query('order') === 'desc';

        $results = $flights->search($request->query(), $passengers)
            ->filter(fn (array $flight) => $this->priceMatches($flight, $request))
            ->sortBy($this->sortBy($sort), descending: $desc)
            ->values();

        return response()->json([
            'search' => [
                'from' => strtoupper($request->from),
                'to' => strtoupper($request->to),
                'date' => $request->date,
                'passengers' => $passengers,
                'sort' => $sort,
                'order' => $desc ? 'desc' : 'asc',
            ],
            'results_count' => $results->count(),
            'data' => $results,
        ], 200);
    }

    private function priceMatches(array $flight, Request $request): bool
    {
        $price = $flight['price'];

        return ($request->min_price === null || $price >= (float) $request->min_price)
            && ($request->max_price === null || $price <= (float) $request->max_price);
    }

    private function sortBy(string $sort): string
    {
        return match ($sort) {
            'depart' => 'depart_at',
            'stops' => 'stops',
            'duration' => 'duration_minutes',
            default => 'price',
        };
    }
}
