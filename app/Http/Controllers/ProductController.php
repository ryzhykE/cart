<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getIndex()
    {
        $product = Product::all();
        //dd($product);
        return view('shop.index',['products' => $product]);
    }

    public function getAddToCart(Request $request, $id)
    {
        $product = Product::find($id);
    }
}
