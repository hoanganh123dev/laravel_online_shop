<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function create(){
         $categories = Category::orderBy('name', 'asc')->get();
         $data['categories'] = $categories;
        return view("admin.sub_category.create");
    }  
}
