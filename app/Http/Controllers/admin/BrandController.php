<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    // This method will show the brands page 
    public function index(Request $request)
    {
        $brands = Brand::latest('id');

        if (!empty($request->get('keyword'))) {
            $categories = $brands->where('name', 'like', '%' . $request->get('keyword') . '%');
        }

        $brands = $brands->paginate(10);

        return view('admin.brands.list', compact('brands'));
    }

    // This method will show the create form page 
    public function create()
    {
        return view('admin.brands.create');
    }

    // This method will store the data in the database 
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands',
        ]);

        if ($validator->passes()) {
            $brand = new Brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            return response()->json([
                'status' => true,
                'message' => "Brand added successfully"
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // This method will redirect to the edit brand page 
    public function edit($id, Request $request)
    {
        $brand = Brand::find($id);

        if (empty($brand)) {
            // session message here 
            return redirect()->route('brands.index');
        }
        $data['brand'] = $brand;

        return view('admin.brands.edit', $data);
    }

    // This method will update the brand 
    public function update($id, Request $request)
    {
        $brand = Brand::find($id);
        if (empty($brand)) {
            // Session message below here

            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Brand not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $brand->id . ',id'
        ]);

        if ($validator->passes()) {
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            return response()->json([
                'status' => true,
                'message' => 'Brand upated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // This method will delete the brands data 
    public function destroy($id, Request $request)
    {
        $brand = Brand::find($id);
        if (empty($brand)) {
            // Session message below here

            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Brand not found'
            ]);
        }



        $brand->delete();

        return response()->json([
            'status' => true,
            'message' => 'Brand deleted successfully.'
        ]);
    }
}
