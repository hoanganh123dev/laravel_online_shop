<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Validator;
class SubCategoryController extends Controller
{
    public function create(){
         $categories = Category::orderBy('name', 'asc')->get();
         $data['categories'] = $categories;
        return view("admin.sub_category.create");
    }  
}
