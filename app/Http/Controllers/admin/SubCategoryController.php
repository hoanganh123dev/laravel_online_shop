<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Validator;
class SubCategoryController extends Controller
{
    public function index(Request $request) {
        $subcategories = SubCategory::select('sub_categories.*', 'categories.name as categoryName')
        ->latest('sub_categories.id')
        ->leftJoin('categories', 'categories.id', 'sub_categories.category_id');

        if(!empty($request->get('keyword'))) {
            $subcategories = $subcategories->where('sub_categories.name','like', '%'.$request->get('keyword').'%');
            $subcategories = $subcategories->orwhere('categories.name','like', '%'.$request->get('keyword').'%');
        }
        $subcategories = $subcategories->paginate(10);
        
        return view('admin.sub_category.list',compact('subcategories'));
    }

    public function create() {
         $categories = Category::orderBy('name', 'asc')->get();
         $data['categories'] = $categories;
         return view("admin.sub_category.create")->with('categories', $categories);
    }  

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            "name" => 'required',
            'slug' => 'required | unique:sub_categories',
            'category'=>'required',
            'status' => 'required'
        ]);

        if ($validator->passes()) {
            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','Sub category created successfully');

            return response([
                'status' => true,
                'message' => 'Sub category created successfully'
            ]);
            
        } else {
            return response([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function edit($id ,Request $request) {
        $subCategory= SubCategory::find($id);
        if(empty($subCategory)) {
            $request->session()->flash('error','Record not found');
            return redirect()->route('sub-categories.index');
        }

        $categories = Category::orderBy('name', 'asc')->get();
        $data['categories'] = $categories;
        $data['subCategory'] = $subCategory;
        return view("admin.sub_category.edit")
            ->with('categories', $categories)
            ->with('subCategory', $subCategory);
    }

    public function update($id, Request $request) {

        $subCategory= SubCategory::find($id);

        if(empty($subCategory)) {
            $request->session()->flash('error','Record not found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
            // return redirect()->route('sub-categories.index');
        }

        $validator = Validator::make($request->all(), [
            "name" => 'required',
            // 'slug' => 'required | unique:sub_categories',
            'slug'=> 'required|unique:sub_categories,slug,'.$subCategory->id.',id',
            'category'=>'required',
            'status' => 'required'
        ]);

        if ($validator->passes()) {

            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            $request->session()->flash('success','Sub category updated successfully');

            return response([
                'status' => true,
                'message' => 'Sub category updated successfully'
            ]);
            
        } else {
            return response([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }

    public function destroy($id, Request $request) {
        
        $subCategory= SubCategory::find($id);

        if(empty($subCategory)) {
            $request->session()->flash('error','Record not found');
            return response([
                'status' => false,
                'notFound' => true
            ]);
            // return redirect()->route('sub-categories.index');
        }

        $subCategory->delete();

        $request->session()->flash('success','Sub category deleted successfully');

        return response([
            'status' => true,
            'message' => 'Sub category deleted successfully'
        ]);
    }
}
