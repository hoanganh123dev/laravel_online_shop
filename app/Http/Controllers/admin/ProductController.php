<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ProductController extends Controller
{
    public function index(Request $request){
        $products = Product::latest('id')->with('product_images');
        if ($request->get('keyword') != '') {
            $products = $products->where('title', 'like', '%' . $request->get('keyword') . '%');
        }
        $products = $products->paginate();
        $data['products'] = $products;
        return view('admin.products.list', $data);
    }
    public function create() {
        $data = [];
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        return view('admin.products.create', $data);
    }

    public function store(Request $request) {
        $rules = [
            'title' => 'required',
            'slug' => 'required | unique:products',
            'price' => 'required|numeric',
            'sku' => 'required | unique:products',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];

        if (!empty($request->track_qty) && $request->track_qty == "Yes") {
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $product = new Product();
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;  
            $product->is_featured = $request->is_featured;
            $product->save();

            //Save gallery pics
            if(!empty($request->image_array)) {
                foreach($request->image_array as $temp_image_id) {

                    $tempImageInfo = TempImage::find($temp_image_id);
                    $extArray = explode('.',$tempImageInfo->name);
                    $ext = last($extArray); //like jpg,gif,png etc


                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $imageName = $product->id.'-'.$productImage->id.'-'.time().'.'.$ext;
                    $productImage->image = $imageName;
                    $productImage->save();

                    //Generate Product Thumbnails

                    // Large Image
                    $sourcePath = public_path().'/temp/'.$tempImageInfo->name;
                    $destPath = public_path().'/uploads/product/large/'.$imageName;
                    $image = Image::make($sourcePath);
                    $image->resize(1400, null, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $image->save($destPath);

                    // Small Image
                    $destPath = public_path().'/uploads/product/small/'.$imageName;
                    $image = Image::make($sourcePath);
                    $image->fit(300, 300);
                    $image->save($destPath);
                }
            }

            $request->session()->flash('success', 'Product has been created successfully'); 

            return response()->json([
                'status' => true,
                'message' => 'Product added successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($id, Request $request) {
        $product = Product::find($id);
        if(empty($product)) {
            // $request->session()->flash('error', 'Product not found');
            return redirect()->route('products.index')->with('error', 'Product not found');
        }

        // Fetch Product Images
        $productImages = ProductImage::where('product_id', $product->id)->get();
        
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();
        $data = [];
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['product'] = $product;
        $data['subCategories'] = $subCategories;
        $data['productImages'] = $productImages;
        return view('admin.products.edit', $data);
    }

    public function update($id, Request $request) {
        $product = Product::find($id);
        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products,slug,'.$product->id.',id',
            'price' => 'required|numeric',
            'sku' => 'required | unique:products,sku,'.$product->id.',id',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];

        if (!empty($request->track_qty) && $request->track_qty == "Yes") {
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;  
            $product->is_featured = $request->is_featured;
            $product->save();

            //Save gallery pics
            

            $request->session()->flash('success', 'Product updated successfully'); 

            return response()->json([
                'status' => true,
                'message' => 'Product updated successfully'
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id, Request $request) {
        $product = Product::find($id);

        if (empty($product)) {
            $request->session()->flash('error', 'Product not found'); 
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }
        $productImages = ProductImage::where('product_id', $id)->get();
        if (!empty($productImages)) {
            foreach ($productImages as $productImage) {
                File::delete(public_path('uploads/product/large/'.$productImage->image));
                File::delete(public_path('uploads/product/small/'.$productImage->image));
            }
            ProductImage::where('product_id', $id)->delete();
        }
        $product->delete();
        $request->session()->flash('success', 'Product deleted successfully'); 
        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully'
        ]);
    }
}
