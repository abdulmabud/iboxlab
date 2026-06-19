<?php

namespace App\Http\Controllers;

class MockProviderController extends Controller
{
    public function providerA()
    {
        return $this->jsonFromProviderFile('provider-a.json');
    }

    public function providerB()
    {
        return $this->jsonFromProviderFile('provider-b.json');
    }

    public function providerC()
    {
        return $this->jsonFromProviderFile('provider-c.json');
    }

    private function jsonFromProviderFile(string $filename)
    {
        $path = storage_path("app/flight-providers/{$filename}");

        return response()->json(
            json_decode(file_get_contents($path), true),
            200
        );
    }
}
