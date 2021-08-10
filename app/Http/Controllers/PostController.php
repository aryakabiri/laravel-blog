<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Gumlet\ImageResize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::with('categories' , 'user')->get();

        $response = [
            'success' => true,
            'data' => $posts,
            'message' => 'posts retrieved successfully.',
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['required', 'image'],
            'categories' => ['array'],
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => $validator->messages(),
                'message' => 'validation error.',
            ];
            return  response()->json($response, 422);
        }

        $post = new Post();
        $post->title = $request->get("title");
        $post->description = $request->get("description");

        $imageDirectory = Storage::disk('warehouse')->put('images/posts', $request->image);

        $post->image = $imageDirectory;
        $post->user_id = auth()->user()->id;
        $post->save();

        $post->categories()->attach($request->get("categories"));

        $response = [
            'success' => true,
            'data' => [],
            'message' => 'post created successfully.',
        ];
        return response()->json($response);
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $post = Post::with('categories' , 'user')->findOrFail($id);

        $response = [
            'success' => true,
            'data' => $post,
            'message' => 'post retrieved successfully.',
        ];
        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'image' => ['image'],
            'categories' => ['array'],
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => $validator->messages(),
                'message' => 'validation error.',
            ];
            return  response()->json($response, 422);
        }


        $post = Post::findOrFail($id);


        if (auth()->user()->id !== $post->user_id){
            $response = [
                'success' => false,
                'data' => [],
                'message' => 'forbidden.',
            ];
            return  response()->json($response, 403);
        }

        $post->title = $request->get("title");
        $post->description = $request->get("description");

        if ($request->has("image")){
            Storage::disk('warehouse')->delete($post->image);
            $imageDirectory = Storage::disk('warehouse')->put('images/posts', $request->image);
            $post->image = $imageDirectory;
        }

        $post->save();


        $post->categories()->detach();
        $post->categories()->attach($request->get("categories"));

        $response = [
            'success' => true,
            'data' => [],
            'message' => 'post updated successfully.',
        ];
        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if (auth()->user()->id !== $post->user_id){
            $response = [
                'success' => false,
                'data' => [],
                'message' => 'forbidden.',
            ];
            return  response()->json($response, 403);
        }

        $post->categories()->detach();
        $post->delete();

        $response = [
            'success' => true,
            'data' => [],
            'message' => 'post deleted successfully.',
        ];
        return response()->json($response);
    }
}
