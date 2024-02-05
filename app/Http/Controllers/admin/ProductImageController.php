<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductImageController extends Controller
{
    public function update(Request $request)
    {

        $image = $request->image;
        $ext = $image->getClientOriginalExtension();
        $sourcePath = $image->getPathName();

        $productImage = new ProductImage();
        $productImage->product_id = $request->product_id;
        $productImage->image = 'NULL';
        $productImage->save();

        $imageName = $request->product_id . '-' . $productImage->id . '-' . time() . '.' . $ext;
        $productImage->image = $imageName;
        $productImage->save();

        // large image 
        $destPath = public_path() . '/uploads/product/large/' . $imageName;

        File::copy($sourcePath, $destPath);


        $manager = new ImageManager(Driver::class);
        $image = $manager->read($destPath);

        $image->resize(1400, 1400);

        $image->save($destPath);
        // Small image 
        $destPath = public_path() . '/uploads/product/small/' . $imageName;
        File::copy($sourcePath, $destPath);

        $manager = new ImageManager(Driver::class);
        $image = $manager->read($destPath);

        $image->cover(300, 300);
        $image->toPng()->save($destPath);

        $image->save($destPath);

        return response()->json([
            'status' => true,
            'image_id' => $productImage->id,
            'ImagePath' => asset('uploads/product/small/' . $productImage->image),
            "message" => 'Image saved successfully',
        ]);
    }

    // delete the image from the database 
    public function destroy(Request $request)
    {
        $productImage = ProductImage::find($request->id);
        if (empty($productImage)) {
            return response()->json([
                'status' => false,
                'message' => 'Image not found'
            ]);
        }

        // Delete images from folder 
        File::delete(public_path('/uploads/product/large/' . $productImage->image));
        File::delete(public_path('/uploads/product/small/' . $productImage->image));

        $productImage->delete();

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully'
        ]);
    }
}
