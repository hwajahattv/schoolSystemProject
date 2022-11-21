<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Product::select('id','title','description','image')->get();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required',
//            'description'=>'required',
//            'image'=>'required|image'
        ]);
//        dd($request);

        try{
            $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('product/image', $request->image,$imageName);
//            Product::create(['title'=>$request->title,'description'=>$request->description,'image'=>$imageName]);
            $product= new Product;
            $product->title= $request->title;
            $product->description= $request->description;
            $product->image= $imageName;
            $product->save();

            return response()->json([
                'message'=>'Product Created Successfully!!'
            ]);
        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'Something goes wrong while creating a product!!'
            ],500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Product $product)
    {
        return response()->json([
            'product'=>$product
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title'=>'required',
            'description'=>'required',
            'image'=>'nullable'
        ]);

        try{

            $product->fill($request->post())->update();

            if($request->hasFile('image')){

                // remove old image
                if($product->image){
                    $exists = Storage::disk('public')->exists("product/image/{$product->image}");
                    if($exists){
                        Storage::disk('public')->delete("product/image/{$product->image}");
                    }
                }

                $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
                Storage::disk('public')->putFileAs('product/image', $request->image,$imageName);
                $product->image = $imageName;
                $product->save();
            }

            return response()->json([
                'message'=>'Product Updated Successfully!!'
            ]);

        }catch(\Exception $e){
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'Something goes wrong while updating a product!!'
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product)
    {
        try {

            if($product->image){
                $exists = Storage::disk('public')->exists("product/image/{$product->image}");
                if($exists){
                    Storage::disk('public')->delete("product/image/{$product->image}");
                }
            }

            $product->delete();

            return response()->json([
                'message'=>'Product Deleted Successfully!!'
            ]);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'message'=>'Something goes wrong while deleting a product!!'
            ]);
        }
    }
}
