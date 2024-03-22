<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brand;
use App\Models\Product;



class ShopController extends Controller
{
    public function index(Request $request, $categorySlug = null, $subCategorySlug = null) {
        $categorySelected = '';
        $subCategorySelected = '';
        $brandsArray = [];

       

        $categories = Category::orderBy('name', 'ASC')->where('status', 1)->with('sub_category')->get();
        $brands = Brand::orderBy('name', 'ASC')->where('status', 1)->get();
        $products = Product::where('status', 1);

        // Apply Filters here
        if (!empty($categorySlug)) {
            $category = Category::where('slug', $categorySlug)->first();
            $products = $products->where('category_id', $category->id);
            $categorySelected = $category->id;
        }

        if (!empty($subCategorySlug)) {
            $subCategory = SubCategory::where('slug', $subCategorySlug)->first();
            $products = $products->where('sub_category_id', $subCategory->id);
            $subCategorySelected = $subCategory->id;
        }
        
        if(!empty($request->get('brand'))) {
            $brandsArray = explode(',', $request->get('brand'));
            $products = $products->whereIn('brand_id', $brandsArray);
        }

        if ($request->get('price_max') != '' && $request->get('price_min') != '') {
            if ($request->get('price_max') == 1000) {
                $products = $products->whereBetween('price', [intval($request->get('price_min'), $request->get('price_max')), 1000000]);
            } else {
                $products = $products->whereBetween('price', [intval($request->get('price_min'), $request->get('price_max')), intval($request->get('price_max'))]);
            }
        }
 
        if ($request->get('sort') != '') {
            if ($request->get('sort') == 'lastest') {
                $products = $products->orderBy('id', 'DESC');
            } else if ($request->get('sort') == 'price_asc') {
                $products = $products->orderBy('price', 'ASC');
            } else {
                $products = $products->orderBy('price', 'DESC');
            }
        } else {
            $products = $products->orderBy('id', 'DESC');
        }

        $products = $products->get();


        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['products'] = $products;
        $data['categorySelected'] = $categorySelected;
        $data['subCategorySelected'] = $subCategorySelected;
        $data['brandsArray'] = $brandsArray;
        $data['priceMax'] = (intval($request->get('price_max')) == 0) ? 1000 : $request->get('price_max');
        $data['priceMin'] = intval($request->get('price_min'));
        $data['sort'] = $request->get('sort');

        return view('front.shop', $data);
    }
}
