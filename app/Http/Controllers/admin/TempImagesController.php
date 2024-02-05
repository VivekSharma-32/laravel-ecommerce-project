<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TempImagesController extends Controller
{

    // This method will save the images in the database 
    public function create(Request $request)
    {
        $image = $request->image;
        if (!empty($image)) {
            $ext = $image->getClientOriginalExtension();
            $newName = time() . '.' . $ext;

            $tempImage = new TempImage();
            $tempImage->name = $newName;
            $tempImage->save();

            $image->move(public_path() . '/temp/', $newName);

            // Generate Thumbnail 
            $sourcePath = public_path() . '/temp/' . $newName;
            $destPath = public_path() . '/temp/thumb/' . $newName;

            File::copy($sourcePath, $destPath);

            $manager = new ImageManager(Driver::class);
            $image = $manager->read($destPath);
            // crop the best fitting 5:3 (600x360) ratio and resize to 600x360 pixel
            $image->cover(300, 275);
            $image->toPng()->save($destPath);

            return response()->json([
                'status' => true,
                'image_id' => $tempImage->id,
                'ImagePath' => asset('/temp/thumb/' . $newName),
                'message' => 'Image uploaded successfully'
            ]);
        }
    }
}
