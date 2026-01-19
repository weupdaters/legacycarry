<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * ✅ Get all active categories
     * GET /api/service-categories
     */
    public function categories()
    {
        $categories = Service::where('is_active', 1)
            ->whereNotNull('category')
            ->selectRaw('MIN(id) as id, category as name')
            ->groupBy('category')
            ->orderBy('category')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $categories
        ]);
    }


    /**
     * ✅ Get services by category
     * GET /api/services?category=Electrician
     */
    public function servicesByCategory($id = null)
    {
        $query = Service::where('is_active', 1);

        // If id is provided → filter by category
        if (!empty($id)) {

            $categoryName = Service::where('id', $id)->value('category');

            if ($categoryName) {
                $query->where('category', $categoryName);
            }
        }

        $services = $query
            ->select('id', 'name', 'category', 'description')
            ->orderBy('name')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $services
        ]);
    }


}
