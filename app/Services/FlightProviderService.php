<?php

namespace App\Services;

class FlightProviderService
{
    public function search(array $filters, int $passengers)
    {
        $matched = $this->allFlights()
            ->where('from', strtoupper($filters['from']))
            ->where('to', strtoupper($filters['to']))
            ->filter(fn (array $flight) => str_starts_with($flight['depart_at'], $filters['date']))
            ->when($filters['provider'] ?? null, fn ($items, $provider) => $items->where('provider', $provider))
            ->when($filters['carrier'] ?? null, fn ($items, $carrier) => $items->where('carrier', strtoupper($carrier)))
            ->when(isset($filters['max_stops']), fn ($items) => $items->where('stops', '<=', (int) $filters['max_stops']));

        return $this->lowestPriceFlights($matched, $passengers);
    }

    public function findById(string $flightId, int $passengers): ?array
    {
        return $this->lowestPriceFlights($this->allFlights(), $passengers)
            ->firstWhere('id', $flightId);
    }

    private function allFlights()
    {
        $a = json_decode(file_get_contents(storage_path('app/flight-providers/provider-a.json')), true);
        $b = json_decode(file_get_contents(storage_path('app/flight-providers/provider-b.json')), true);
        $c = json_decode(file_get_contents(storage_path('app/flight-providers/provider-c.json')), true);

        return collect()
            ->merge(collect($a['flights'])->map(fn ($flight) => [
                'provider' => 'provider-a',
                'carrier' => $flight['carrier'],
                'flight_number' => $flight['flight_no'],
                'from' => $flight['from'],
                'to' => $flight['to'],
                'depart_at' => str_replace(' ', 'T', $flight['depart']),
                'arrive_at' => str_replace(' ', 'T', $flight['arrive']),
                'stops' => $flight['stops'],
                'price' => $flight['fare_usd'],
                'currency' => 'USD',
            ]))
            ->merge(collect($b['data'])->map(fn ($flight) => [
                'provider' => 'provider-b',
                'carrier' => $flight['airline_code'],
                'flight_number' => $flight['number'],
                'from' => $flight['origin'],
                'to' => $flight['destination'],
                'depart_at' => str_replace(' ', 'T', $flight['departure_time']).':00',
                'arrive_at' => str_replace(' ', 'T', $flight['arrival_time']).':00',
                'stops' => $flight['segments'],
                'price' => $flight['price']['amount'],
                'currency' => $flight['price']['currency'],
            ]))
            ->merge(collect($c['results'])->map(fn ($flight) => [
                'provider' => 'provider-c',
                'carrier' => $flight['iata'],
                'flight_number' => $flight['code'],
                'from' => $flight['route']['src'],
                'to' => $flight['route']['dst'],
                'depart_at' => gmdate('Y-m-d\TH:i:s', $flight['times']['dep']),
                'arrive_at' => gmdate('Y-m-d\TH:i:s', $flight['times']['arr']),
                'stops' => $flight['layovers'],
                'price' => $flight['total_price'],
                'currency' => $flight['currency'],
            ]));
    }

    private function lowestPriceFlights($flights, int $passengers)
    {
        return $flights
            ->groupBy(fn (array $flight) => $this->flightKey($flight))
            ->map(fn ($offers) => $this->formatFlight($offers->sortBy('price')->first(), $passengers))
            ->values();
    }

    private function formatFlight(array $flight, int $passengers): array
    {
        return [
            'id' => 'flt_'.substr(hash('sha256', $this->flightKey($flight)), 0, 12),
            'provider' => $flight['provider'],
            'carrier' => $flight['carrier'],
            'flight_number' => $flight['flight_number'],
            'from' => $flight['from'],
            'to' => $flight['to'],
            'depart_at' => $flight['depart_at'],
            'arrive_at' => $flight['arrive_at'],
            'duration_minutes' => (strtotime($flight['arrive_at']) - strtotime($flight['depart_at'])) / 60,
            'stops' => $flight['stops'],
            'price' => $flight['price'],
            'total_price' => $flight['price'] * $passengers,
            'currency' => $flight['currency'],
        ];
    }

    private function flightKey(array $flight): string
    {
        return "{$flight['carrier']}|{$flight['flight_number']}|{$flight['from']}|{$flight['to']}|{$flight['depart_at']}|{$flight['arrive_at']}";
    }
}
