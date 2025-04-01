<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\MultiImage;
use App\Models\Room;
use App\Models\RoomNumber;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Intervention\Image\Image;
use PHPUnit\Framework\Constraint\Count;


class RoomController extends Controller
{
    public function editRoom($id)
    {
        $basic_facility = Facility::where('rooms_id', $id)->get();
        $multiimage = MultiImage::where('rooms_id', $id)->get();
        $editData = Room::find($id);
        $allroomNo=RoomNumber::where('rooms_id', $id)->get();

        return view('backend.allroom.rooms.edit_rooms', compact('editData', 'basic_facility', 'multiimage', 'allroomNo'));
    }

    public function updateRoom(Request $request, $id)
    {
        $room = Room::find($id);
        $room->roomtype_id = $request->roomtype_id;
        $room->total_adult = $request->total_adult;
        $room->total_child = $request->total_child;
        $room->room_capacity = $request->room_capacity;
        $room->price = $request->price;
        $room->size = $request->size;
        $room->view = $request->view;
        $room->bed_style = $request->bed_style;
        $room->discount = $request->discount;
        $room->short_desc = $request->short_desc;
        $room->description = $request->description;
        $room->status = 1;

        //update single image

        if ($request->file('image')) {
            $image = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(550, 850)->save('upload/rooming/' . $name_gen);
            $room['image'] = $name_gen;
        }
        $room->save();

        //update for facility table

        if ($request->facility_name == Null) {
            $notification = array(
                'message' => 'Sorry, not any basic facility selected.',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        } else {
            Facility::where('rooms_id', $id)->delete();
            $facility = Count($request->facility_name);
            for ($i = 0; $i < $facility; $i++) {
                $fcount = new Facility();
                $fcount->rooms_id = $room->id;
                $fcount->facility_name = $request->facility_name[$i];
                $fcount->save();
            }
        }

        //update multi image
        if ($room->save()) {
            $files = $request->file->multi_img;
            if (!empty($files)) {
                $subimage = MultiImage::where('rooms_id', $id)->get()->toArray();
                MultiImage::where('rooms_id', $id)->delete();
            }
            if (!empty($files)) {
                foreach ($files as $file) {
                    $imgName = date('YmdHis') . $file->getClientOriginalName();
                    $file->move('/upload/rooming/multi_img', $imgName);
                    $subimage['multi_img'] = $imgName;

                    $subimage = new MultiImage();
                    $subimage->room_id = $room->id;
                    $subimage->multi_img = $imgName;
                    $subimage->save();
                }
            }
        }
        $notification = array(
            'message' => 'Room update successfully.',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function multiImageDelete($id)
    {
        $deletedata = MultiImage::where('id', $id)->first();

        if ($deletedata) {
            $imagePath = $deletedata->multi_img;

            if (file_exists($imagePath)) {
                unlink($imagePath);
                echo 'Image unlink successfully';
            } else {
                echo 'Image link not found';
            }

            MultiImage::where('id', $id)->delete();
        }

        $notification = array(
            'message' => 'Image deleted successfully.',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    public function storeRoomNumber(Request $request, $id){

        $data=new RoomNumber();
        $data->rooms_id=$id;
        $data->room_type_id=$request->room_type_id;
        $data->room_no=$request->room_no;
        $data->status=$request->status;
        $data->save();

        $notification = array(
            'message' => 'Room number added successfully.',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }

    public function editRoomNumber($id){

        $editroomno=RoomNumber::find($id);
        return view('backend.allroom.rooms.edit_room_no', compact('editroomno'));
    }

    public function updateRoomNumber(Request $request, $id){
        $data=RoomNumber::find($id);
        $data->room_no=$request->room_no;
        $data->status=$request->status;
        $data->save();

        $notification = array(
            'message' => 'Room number updated successfully.',
            'alert-type' => 'success'
        );
        return redirect()->route('room.type.list')->with($notification);
    }

    public function deleteRoomNumber($id){
        RoomNumber::find($id)->delete();

        $notification = array(
            'message' => 'Room number deleted successfully.',
            'alert-type' => 'success'
        );
        return redirect()->route('room.type.list')->with($notification);
    }

    public function deleteRoom(Request $request, $id){

        $room=Room::find($id);

        if(file_exists('upload/rooming/' . $room->image) && !empty($room->image)) {
            unlink('upload/rooming/' . $room->image);
        }

        $subimage = MultiImage::where('rooms_id', $room->id)->get()->toArray();
        if (!empty($subimage)) {
            foreach ($subimage as $key => $value) {
                if(!empty($value)){
                    unlink('upload/rooming/multi_img/' . $value["multi_img"]);

                }

            }
        }
        RoomType::where('id', $room->roomtype_id)->delete();
        MultiImage::where('rooms_id', $room->id)->delete();
        Facility::where('rooms_id', $room->id)->delete();
        RoomNumber::where('rooms_id', $room->id)->delete();
        $room->delete();

        $notification = array(
            'message' => 'Room number deleted successfully.',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }


}
