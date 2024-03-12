<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;
// use App\Models\SubCategory as ModelsSubCategory;

class ProductSubCategoryController extends Controller
{
    public function index(Request $request) {
        
        if (!empty($request->category_id)) {
            // $subCategorires = SubCategory::findByCategoryId($request->category_id);($request->category_id)
            $subCategorires = SubCategory::where('category_id', $request->category_id)
            ->orderBy('name', 'ASC')
            ->get();

            return response()->json([
                "status" => "success",
                "data" => $subCategorires
            ]);

        } else {
            return response()->json([
                "status" => "true",
                'subCategories' => []
            ]);
        }
    }
}
