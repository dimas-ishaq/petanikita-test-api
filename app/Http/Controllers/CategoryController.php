<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            $category = Category::all();
            return CategoryResource::collection($category);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categories not found'], 404);
        }
    }
    public function create(Request $request)
    {


        $validated = $request->validate([
            'name' => 'required|max:255',
        ]);
        $category = Category::create($request->all());
        return new CategoryResource($category);
    }
    public function update(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);
            $request->validate([
                'name' => 'required',
            ]);
            $category->name = $request->name;
            $category->save();

            return response()->json([
                'message' => 'Category updated successfully',
                'data' => new CategoryResource($category)
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categories not found'], 404);
        }
    }

    public function delete($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Categories not found'], 404);
        }
    }
}
