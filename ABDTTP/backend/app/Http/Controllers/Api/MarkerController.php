<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Marker;
use App\Http\Requests\StoreMarkerRequest;
use App\Http\Requests\UpdateMarkerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarkerController extends Controller
{
    public function index()
    {
        return Marker::all()->map(function ($marker) {
            if ($marker->image_url && !str_starts_with($marker->image_url, 'http')) {
                $marker->display_image = asset(Storage::url($marker->image_url));
            } else {
                $marker->display_image = $this->getRandomDuckUrl();
            }
            return $marker;
        });
    }

    public function store(StoreMarkerRequest $request)
    {
        $data = $request->validated();
        
        if (isset($data['created_by']) == false && $request->user()) {
            $data['created_by'] = $request->user()->id;
        }

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('markers', 'public');
            $data['image_url'] = $path;
        }

        $marker = Marker::create($data);

        if ($marker->image_url) {
            $marker->display_image = asset(Storage::url($marker->image_url));
        } else {
            $marker->display_image = $this->getRandomDuckUrl();
        }

        return response()->json($marker, 201);
    }

    public function show(Marker $marker)
    {
        if ($marker->image_url && !str_starts_with($marker->image_url, 'http')) {
            $marker->display_image = Storage::url($marker->image_url);
        } else {
            $marker->display_image = $this->getRandomDuckUrl();
        }
        return $marker;
    }

    public function update(UpdateMarkerRequest $request, Marker $marker)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($marker->image_url && Storage::disk('public')->exists($marker->image_url)) {
                Storage::disk('public')->delete($marker->image_url);
            }
            
            $path = $request->file('image')->store('markers', 'public');
            $data['image_url'] = $path;
        }

        $marker->update($data);

        if ($marker->image_url) {
            $marker->display_image = asset(Storage::url($marker->image_url));
        } else {
            $marker->display_image = $this->getRandomDuckUrl();
        }

        return $marker;
    }

    public function destroy(Request $request, Marker $marker)
    {
        if ($marker->created_by && $marker->created_by !== $request->user()->id) {
            return response()->json(['error' => 'Alleen de maker kan deze marker verwijderen.'], 403);
        }

        if ($marker->image_url && Storage::disk('public')->exists($marker->image_url)) {
            Storage::disk('public')->delete($marker->image_url);
        }

        $marker->delete();

        return response()->json(['message' => 'Marker deleted successfully.']);
    }

    /**
     * Omzeilt de CORS-policy van randomd.uk door de JSON server-side te fetchen via IPv4
     */
    private function getRandomDuckUrl(): string
    {
        try {
            $response = Http::withoutVerifying()
                ->withOptions(['curl' => [CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4]])
                ->timeout(3)
                ->get('https://random-d.uk/api/v2/random');

            if ($response->successful()) {
                return $response->json()['url'] ?? 'https://random-d.uk/api/v2/random';
            }
        } catch (\Exception $e) {
            Log::error('RandomD.uk API timeout of fout op localhost: ' . $e->getMessage());
        }

        return 'https://random-d.uk/api/v2/random';
    }
}
