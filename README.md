# Flight Search and Booking API

This Laravel API searches mock flight providers and creates bookings from search results.

## Main Endpoints

```text
GET /api/flights/search
POST /api/bookings
GET /api/bookings/{reference}
```

## 1. Search Flights

Endpoint:

```text
GET /api/flights/search
```

Example URL with all supported query parameters:

```text
http://127.0.0.1:8000/api/flights/search?from=DAC&to=DXB&date=2026-07-01&passengers=2&sort=price&order=asc&provider=provider-b&carrier=EK&max_stops=0&min_price=100&max_price=500
```

Query parameters:

| Parameter | Required | Example | Description |
| --- | --- | --- | --- |
| `from` | Yes | `DAC` | Origin airport code. |
| `to` | Yes | `DXB` | Destination airport code. |
| `date` | Yes | `2026-07-01` | Departure date. |
| `passengers` | Yes | `2` | Number of passengers. |
| `sort` | No | `price` | Sort by `price`, `depart`, `stops`, or `duration`. |
| `order` | No | `asc` | Sort order: `asc` or `desc`. |
| `provider` | No | `provider-b` | Filter by provider: `provider-a`, `provider-b`, or `provider-c`. |
| `carrier` | No | `EK` | Filter by airline/carrier code. |
| `max_stops` | No | `0` | Maximum number of stops. |
| `min_price` | No | `100` | Minimum price per passenger. |
| `max_price` | No | `500` | Maximum price per passenger. |

The response returns one result per flight. If the same flight exists in multiple providers, the API returns the lowest-price provider result.

## 2. Create Booking

Endpoint:

```text
POST /api/bookings
```

First call `GET /api/flights/search`, then use the returned flight `id` as `flight_id` when creating a booking.

Example body:

```json
{
  "flight_id": "flt_xxxxx",
  "passengers": [
    {
      "name": "Abdul Mabud",
      "email": "mabud@example.com",
      "phone": "01700000000"
    }
  ]
}
```

Response includes a booking `reference`, booking status, passenger details, and selected flight details.

## 3. Retrieve Booking

Endpoint:

```text
GET /api/bookings/{reference}
```

Example:

```text
http://127.0.0.1:8000/api/bookings/BK-ABC12345
```

Returns the saved booking details for the given booking reference. If the reference does not exist, the API returns `404`.

## Mock Provider Endpoints

```text
GET /api/mock/provider-a
GET /api/mock/provider-b
GET /api/mock/provider-c
```

Each endpoint returns its provider data in its original schema.
