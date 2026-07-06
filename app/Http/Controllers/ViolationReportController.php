<?php

namespace App\Http\Controllers;

use App\Models\ViolationReport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ViolationReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recorded_at' => 'required|string',
            'location' => 'required|string|max:255',
            'reporters' => 'required|array',
            'reporters.*.ho_ten' => 'required|string|max:255',
            'reporters.*.chuc_vu' => 'nullable|string|max:255',
            'reporters.*.cong_ty' => 'nullable|string|max:255',
            'witnesses' => 'required|array',
            'witnesses.*.ho_ten' => 'required|string|max:255',
            'witnesses.*.chuc_vu' => 'nullable|string|max:255',
            'witnesses.*.cong_ty' => 'nullable|string|max:255',
            'violators' => 'required|array',
            'violators.*.ho_ten' => 'required|string|max:255',
            'violators.*.chuc_vu' => 'nullable|string|max:255',
            'violators.*.cong_ty' => 'nullable|string|max:255',
            'target' => 'nullable|string|max:255',
            'violation_content' => 'required|string',
            'violation_count' => 'nullable|string|max:255',
            'violator_attitude' => 'nullable|string',
            'resolution_direction' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $validated['recorded_at'] = Carbon::createFromFormat('H:i d/m/Y', $validated['recorded_at'])->format('Y-m-d H:i:s');

        if (!empty($validated['image'])) {
            $validated['image'] = $this->saveImage($validated['image']);
        }

        $report = ViolationReport::create($validated);

        return response()->json([
            'message' => 'Violation report created successfully',
            'data' => $report,
        ], 201);
    }

    private function saveImage(string $image): string
    {
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            $response = Http::timeout(30)->get($image);
            $imageData = $response->body();
            $mime = $response->header('Content-Type');
            $extension = match (true) {
                str_contains($mime, 'png') => 'png',
                str_contains($mime, 'jpeg') || str_contains($mime, 'jpg') => 'jpg',
                str_contains($mime, 'gif') => 'gif',
                str_contains($mime, 'webp') => 'webp',
                default => 'jpg',
            };
            $filename = 'violation-reports/' . uniqid() . '.' . $extension;
            Storage::disk('public')->put($filename, $imageData);
            return $filename;
        }

        if (str_contains($image, ',')) {
            $image = explode(',', $image)[1];
        }
        $imageData = base64_decode($image, true);
        if ($imageData === false || strlen($imageData) < 100) {
            return '';
        }
        $filename = 'violation-reports/' . uniqid() . '.png';
        Storage::disk('public')->put($filename, $imageData);
        return $filename;
    }
}
