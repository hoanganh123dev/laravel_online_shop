<?php

namespace App\Http\Controllers\admin;
use App\Models\TempImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TempImageController extends Controller
{
    public function create(Request $request) {
        if($request->image){
            $image = $request->image;
            $extenstion = $image->getClientOriginalExtension();
            $newFileName = time() . '.' . $extenstion;

            $tempImage = new TempImage();
            $tempImage->name = $newFileName;
            $tempImage->save();

            $image->move(public_path('uploads/temp'), $newFileName);

            return response()->json([
                'status' => true,
                'name'=> $newFileName,
                'id' => $tempImage->id,
            ]);
        }
    }
}
