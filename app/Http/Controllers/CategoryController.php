<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $category = Category::all();

        $response = [
            'success' => true,
            'data' => $category,
            'message' => 'categories retrieved successfully.',
        ];
        return response()->json($response);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => $validator->messages(),
                'message' => 'validation error.',
            ];
            return  response()->json($response, 422);
        }

        $category = new Category();
        $category->name = $request->get("name");
        $category->save();


        $response = [
            'success' => true,
            'data' => [],
            'message' => 'category created successfully.',
        ];
        return response()->json($response);
    }

}
