<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use App\Models\Property;

use Illuminate\Support\Facades\File;
use App\Models\PropertyDto;
use Illuminate\Validation\UnauthorizedException;

class PropertyController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $properties =Property::where('owner_id' ,'!=' ,$userId)->with('images')->get();

        // Transform properties to DTOs
        $propertiesDto = $properties->map(function ($property) {
            $images = $property->images->map(function ($image) {
                // Check if the image file exists and encode it in base64
                if ($image->path != "" && File::exists($image->path)) {
                    return "data:".File::mimeType($image->path).";base64,".base64_encode(File::get($image->path));
                }
                return null; // Or handle the case when the file does not exist
            })->filter(); // Remove null values if the file does not exist

            return new PropertyDto(
                $property->id,
                $property->owner_id,
                $property->title,
                $property->description,
                $property->location,
                $property->price_per_day,
                $property->category,
                $images->toArray() // Convert collection to array,

            );
        });

        // Return the transformed DTOs as JSON
        return response()->json($propertiesDto);
    }


    public function myProperties()  {
        $userId = auth()->id();
        if($userId){

            $properties = Property::where('owner_id' ,'=' ,$userId)->with('images')->get();
            // Transform properties to DTOs
            $propertiesDto = $properties->map(function ($property) {
                $images = $property->images->map(function ($image) {
                    // Check if the image file exists and encode it in base64
                    if ($image->path != "" && File::exists($image->path)) {
                        return "data:".File::mimeType($image->path).";base64,".base64_encode(File::get($image->path));
                    }
                    return null; // Or handle the case when the file does not exist
                })->filter(); // Remove null values if the file does not exist

            return new PropertyDto(
                $property->id,
                $property->owner_id,
                $property->title,
                $property->description,
                $property->location,
                $property->price_per_day,
                $property->category,
                $images->toArray() // Convert collection to array
            );
        });

        // Return the transformed DTOs as JSON
        return response()->json($propertiesDto);
        }
        return response()->json(['error' => 'Utilisateur non authentifié'], 401);
    }
    public function store(Request $request)
    {
        // Validate the images
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max size 2MB per file
        ]);
        $property = Property::create($request->all());
        foreach ($request->file('images') as $image) {
            // Generate a unique name for the image
            $imageName = time() . '-' . $image->getClientOriginalName();

            // Specify the path where you want to save the image on the C drive
            $imagePath = 'C:/uploads/images/'."$property->id";

            // Move the image to the C drive location
            $image->move($imagePath, $imageName);
            Image::create(['property_id' => $property->id, 'name'=>$imageName,'path'=>$imagePath.'/'.$imageName]);
        }
        return response()->json($property, 201);
    }

    public function show($id)
    {
        $property = Property::with(['images','owner'])->find($id);


    // Transform properties to DTOs

        $images = $property->images->map(function ($image) {
            // Check if the image file exists and encode it in base64
            if ($image->path != "" && File::exists($image->path)) {
                return  ["id" => $image->id,"base64" =>"data:".File::mimeType($image->path).";base64,".base64_encode(File::get($image->path))];
            }
            return null; // Or handle the case when the file does not exist
        })->filter(); // Remove null values if the file does not exist

        $propertyDto  = new PropertyDto(
            $property->id,
            $property->owner_id,
            $property->title,
            $property->description,
            $property->location,
            $property->price_per_day,
            $property->category,
            $images->toArray(),
            $property->owner
        );

    // Return the transformed DTOs as JSON
    return response()->json($propertyDto);
    }

    public function update(Request $request, $id)
    {
        $property = Property::with("images")->find($id);

        if (!$property) {
            return response()->json(['error' => 'Property not found'], 404);
        }

        // Validate request data
        $validatedData = $request->validate([
            'owner_id' => 'required|integer|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_day' => 'required|numeric',
            'location' => 'required|string',
            'category' => 'required|string',
        ]);
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Max size 2MB per file
        ]);
        if ($request->hasFile('images')) {
        foreach ($request->file('images') as $image) {
            // Generate a unique name for the image
            $imageName = time() . '-' . $image->getClientOriginalName();

            // Specify the path where you want to save the image on the C drive
            $imagePath = 'C:/uploads/images/'."$property->id";

            // Move the image to the C drive location
            $image->move($imagePath, $imageName);
            Image::create(['property_id' => $property->id, 'name'=>$imageName,'path'=>$imagePath.'/'.$imageName]);
        }
    }
    $deletedImages = $request->input("deletedImages");
    if($deletedImages && count($deletedImages) > 0){
        $imagesCollection = collect($property->images);
        $imageToBeDeleted = $imagesCollection->whereIn("id", $deletedImages);
        foreach($imageToBeDeleted as $image){
            File::delete($image->path);
        }
        Image::destroy($deletedImages);
    }
    // Update property with validated data
    $property->update($validatedData);

    return response()->json($property, 200);
    }

    public function destroy($id)
    {
        $property = Property::with('images')->find($id);
        $property->images->map(function ($image){
            File::delete($image->path);
        } );
        $property->delete();
        return response()->json(null, 204);
    }
}
