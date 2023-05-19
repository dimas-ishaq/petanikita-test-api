<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        try {
            $products = Product::all();
            return ProductResource::collection($products);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Products not found'], 404);
        }
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'desc' => 'required',
            'category_id' => 'required',
            'price' => 'required'
        ]);
        $product = Product::create($request->all());
        return new ProductResource($product);
    }

    public function show($id)
    {
        try {
            $data = Product::findOrFail($id);
            return new ProductResource($data);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $request->validate([
                'name' => 'required',
                'desc' => 'required',
                'category_id' => 'required',
                'price' => 'required'

            ]);

            $product->name = $request->name;
            $product->desc = $request->desc;
            $product->category_id = $request->category_id;
            $product->price = $request->price;
            $product->save();

            return response()->json([
                'message' => 'Product updated successfully',
                'data' => new ProductResource($product)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Product not found'], 404);
        }
    }
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return response()->json(['message' => 'product deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'product not found'], 404);
        }
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

        $products = Product::where('name', 'LIKE', "%$keyword%")
            ->orWhere('desc', 'LIKE', "%$keyword%")
            ->get();
        // dd($products);
        return response()->json($products);
    }
}
