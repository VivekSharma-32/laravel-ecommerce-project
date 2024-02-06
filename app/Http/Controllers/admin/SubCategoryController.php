<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
    // This method will show the subcategories page 
    public function index(Request $request)
    {
        $subCategories = SubCategory::select('sub_categories.*', 'categories.name as categoryName')
            ->latest('sub_categories.id')
            ->leftJoin('categories', 'categories.id', 'sub_categories.category_id');

        if (!empty($request->get('keyword'))) {
            $categories = $subCategories->where('sub_categories.name', 'like', '%' . $request->get('keyword') . '%');
            $categories = $subCategories->orWhere('categories.name', 'like', '%' . $request->get('keyword') . '%');
        }


        $subCategories = $subCategories->paginate(10);

        // $data['categories'] =  $categories;

        return view('admin.sub_category.list', compact('subCategories'));
    }

    // This method will show the sub category form 
    public function create()
    {
        $categories = Category::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        return view('admin.sub_category.create', $data);
    }

    // This method will store the data in the database 
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required',
        ]);

        if ($validator->passes()) {

            $subCategory = new SubCategory();

            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;

            $subCategory->save();



            session()->flash('success', 'Sub category created successfully');

            return response()->json([
                'status' => true,
                'message' => 'Subcategory created successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // This method will display the edit sub category form 
    public function edit($id, Request $request)
    {
        $subCategory = SubCategory::find($id);
        if (empty($subCategory)) {
            return redirect()->route('admin.sub_category.index');
        }

        $categories = Category::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        $data['subCategory'] = $subCategory;

        return view('admin.sub_category.edit', $data);
    }

    // This method will update the subcategories data 
    public function update($id, Request $request)
    {

        $subCategory = SubCategory::find($id);
        if (empty($subCategory)) {
            // return redirect()->route('admin.sub_category.index'); 
            return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }


        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories,slug,' . $subCategory->id . ',id'
        ]);

        if ($validator->passes()) {
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->showHome = $request->showHome;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            // session response 

            session()->flash('success', 'Sub category updated successfully');
            return response()->json([
                'status' => true,
                'message' => 'Sub Category updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    // This method will delete the subcategory from the database 
    public function destroy($id, Request $request)
    {
        $subCategory = SubCategory::find($id);
        if (empty($subCategory)) {
            // return redirect()->route('admin.sub_category.index'); 
            return response()->json([
                'status' => false,
                'notFound' => true,
            ]);
        }

        $subCategory->delete();

        // session message
        session()->flash('success', 'Sub category successfully deleted');
        return response()->json([
            'status' => true,
            'message' => "Sub category successfully deleted!!!"
        ]);
    }
}
