<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Gallery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Image;

class GalleryController extends Controller
{
    public function allGallery()
    {

        $gallery = Gallery::latest()->get();
        return view('backend.gallery.all_gallery', compact('gallery'));
    }

    public function addGallery()
    {

        return view('backend.gallery.add_gallery');
    }

    public function storeGallery(Request $request)
    {

        $images = $request->file('photo_name');
        foreach ($images as $image) {
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(550, 550)->save('upload/gallery/' . $name_gen);
            $save_url = 'upload/gallery/' . $name_gen;

            Gallery::insert([
                'photo_name' => $save_url,
                'created_at' => Carbon::now(),
            ]);

        }
        $notification = array(
            'message' => 'Gallery Added Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.gallery')->with($notification);
    }

    public function editGallery($id)
    {

        $gallery = Gallery::find($id);
        return view('backend.gallery.edit_gallery', compact('gallery'));
    }

    public function updateGallery(Request $request)
    {

        $gal_id = $request->id;
        $img = $request->file('photo_name');

        $name_gen = hexdec(uniqid()) . '.' . $img->getClientOriginalExtension();
        Image::make($img)->resize(550, 550)->save('upload/gallery/' . $name_gen);
        $save_url = 'upload/gallery/' . $name_gen;

        Gallery::find($gal_id)->update([
            'photo_name' => $save_url,
        ]);
        $notification = array(
            'message' => 'Gallery Updated Successfully',
            'alert-type' => 'info'
        );
        return redirect()->route('all.gallery')->with($notification);

    }

    public function deleteGallery($id)
    {

        $item = Gallery::findOrFail($id);
        $img = $item->photo_name;
        unlink($img);

        Gallery::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Gallery Deleted Successfully',
            'alert-type' => 'error'
        );

        return redirect()->back()->with($notification);
    }

    public function deleteGalleryMultiple(Request $request)
    {

        $selectedItems = $request->input('selectedItems', []);
        foreach ($selectedItems as $itemId) {
            $item = Gallery::find($itemId);
            $img = $item->photo_name;
            unlink($img);
            $item->delete();

        }
        $notification = array(
            'message' => 'Selected image Deleted Successfully',
            'alert-type' => 'error'
        );
        return redirect()->back()->with($notification);
    }

    public function showGallery(){
        $gallery = Gallery::latest()->get();
        return view('frontend.gallery.show_gallery', compact('gallery'));
    }

    public function contactUs($id){
        return view('frontend.gallery.contact_us');
    }

    public function storeContactUs(Request $request){

        Contact::insert([
            'name'=>$request->name,
            'email'=>$request->email,
            'phone'=>$request->phone,
            'subject'=>$request->subject,
            'message'=>$request->message,
            'created_at'=>Carbon::now(),
        ]);
        $notification = array(
            'message' => 'Contact Us Added Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    public function adminContactMessage(){

        $contact = Contact::latest()->get();
        return view('backend.contact.contact_message', compact('contact'));
    }
}
