<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Image;


class TestimonialController extends Controller
{
    public function allTestimonial()
    {
        $testimonials = Testimonial::latest()->get();
        return view('backend.testimonial.all_testimonial', compact('testimonials'));
    }

    public function addTestimonial()
    {
        return view('backend.testimonial.add_testimonial');
    }

    public function storeTestimonial(Request $request)
    {
        $image = $request->file('image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();

        Image::make($image)->resize(50, 50)->save('upload/testimonial/' . $name_gen);
        $save_url = 'upload/testimonial/' . $name_gen;

        Testimonial::insert([
            'name' => $request->name,
            'city' => $request->city,
            'message' => $request->message,
            'image' => $request->image,
            'created_at' => Carbon::now(),

        ]);
        $notification = array(
            'message' => 'Testimonial Inserted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.testimonial')->with($notification);
    }

    public function editTestimonial($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        return view('backend.testimonial.edit_testimonial', compact('testimonial'));
    }

    public function updateTestimonial(Request $request, $id)
    {
        $test_id = $request->id;

        if ($request->file('image')) {
            $image = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(50, 50)->save('upload/testimonial/' . $name_gen);
            $save_url = 'upload/testimonial/' . $name_gen;

            Testimonial::insert([
                'name' => $request->name,
                'city' => $request->city,
                'message' => $request->message,
                'image' => $request->image,
                'created_at' => Carbon::now(),
            ]);
            $notification = array(
                'message' => 'Testimonial Updated Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.team')->with($notification);
        } else {
            Testimonial::insert([
                'name' => $request->name,
                'city' => $request->city,
                'message' => $request->message,
                'created_at' => Carbon::now(),
            ]);
            $notification = array(
                'message' => 'Testimonial Updated Without Image Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.team')->with($notification);

        }
    }

    public function deleteTestimonial($id){
        $item = Testimonial::findOrFail($id);
        $img = $item->image;
        unlink($img);

        Testimonial::findOrFail($id)->delete();

        $notification = array(
           'message' => 'Testimonial Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }
}
