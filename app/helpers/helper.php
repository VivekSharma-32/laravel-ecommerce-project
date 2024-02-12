<?php

use App\Models\Category;
use App\Models\ProductImage;

function getCategories()
{
    return Category::orderBy('name', "ASC")
        ->where('showHome', 'Yes')
        ->where('status', 1)
        ->with('sub_category')
        ->orderBy('id', 'DESC')
        ->get();
}

function getProductImage($productId)
{
    return ProductImage::where('product_id', $productId)->first();
}
