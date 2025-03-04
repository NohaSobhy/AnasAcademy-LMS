<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use Stripe\Stripe;
use Stripe\Product;

class CourseController extends Controller
{
    public function index()
    {
        return response()->json(Course::with('instructor')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $product = Product::create([
            'name' => $request->title,
            'description' => $request->description,
            'type' => 'service',
        ]);

        $user = auth()->guard('api')->user();
        $instructor_id = $user->id;

        $course = Course::create([
            'instructor_id' => $instructor_id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'status' => $request->status,
            'stripe_product_id' => $product->id,
        ]);

        return response()->json(['message' => 'Course created', 'course' => $course], 201);
    }

    public function show($id)
    {
        $course = Course::with('instructor')->findOrFail($id);
        return response()->json($course);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $user = $request->user();

        if ($course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive'
        ]);

        $course->update($request->all());
        return response()->json(['message' => 'Course updated', 'course' => $course]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $course = Course::findOrFail($id);
        if ($course->instructor_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $course->delete();
        return response()->json(['message' => 'Course deleted']);
    }
}
