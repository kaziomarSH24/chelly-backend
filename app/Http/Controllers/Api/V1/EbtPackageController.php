<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EbtPackage;
use Illuminate\Http\Request;

class EbtPackageController extends Controller
{
    /**
     * Display a listing of the active packages (for frontend).
     */
    public function index()
    {
        $packages = EbtPackage::where('is_active', true)->get();
        return response_success('EBT Packages retrieved successfully', $packages);
    }

    /**
     * Display all packages (for admin dashboard).
     */
    public function adminIndex()
    {
        $packages = EbtPackage::orderBy('created_at', 'desc')->get();
        return response_success('EBT Packages retrieved successfully', $packages);
    }

    /**
     * Store a newly created package in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'product_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        $package = EbtPackage::create($validated);
        return response_success('EBT Package created successfully', $package, 201);
    }

    /**
     * Update the specified package in storage.
     */
    public function update(Request $request, $id)
    {
        $package = EbtPackage::find($id);
        if (!$package) {
            return response_error('Package not found', [], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'product_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
        ]);

        $package->update($validated);
        return response_success('EBT Package updated successfully', $package);
    }

    /**
     * Remove the specified package from storage.
     */
    public function destroy($id)
    {
        $package = EbtPackage::find($id);
        if (!$package) {
            return response_error('Package not found', [], 404);
        }

        $package->delete();
        return response_success('EBT Package deleted successfully', null);
    }
}
