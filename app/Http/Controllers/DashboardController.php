<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Bar chart: total stock per kategori
        $itemsByCategory = Category::query()
            ->leftJoin('items', 'items.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('COALESCE(SUM(items.stock),0) as total_stock'))
            ->groupBy('categories.name')
            ->orderBy('categories.name')
            ->get();

        $labels = $itemsByCategory->pluck('name');
        $stocks = $itemsByCategory->pluck('total_stock');

        // Doughnut: total repair vs total stock
        $totalStock = (int) Item::sum('stock');
        $totalRepair = (int) Item::sum('repair_count');

        return view('dashboard.index', compact('labels', 'stocks', 'totalStock', 'totalRepair'));
    }
}
