<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubCategoryController extends Controller
{
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

    public function create()
    {
        $categories = Category::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        return view('admin.sub_category.create', $data);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required'
        ]);

        if ($validator->passes()) {

            $subCategory = new SubCategory();

            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->category_id = $request->category;

            $subCategory->save();



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
}
