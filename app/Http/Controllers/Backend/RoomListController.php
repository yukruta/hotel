<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomBookedDate;
use App\Models\RoomNumber;
use App\Models\RoomType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomListController extends Controller
{
    public function viewRoomList()
    {
        $room_number_list = RoomNumber::with(['room_type', 'last_booking.booking:id,check_in,check_out, status,code,name,phone'])
            ->orderBy('room_type_id', 'ASC')
            ->leftJoin('room_types', 'room_types.id', 'room_numbers.room_type_id')
            ->leftJoin('booking_room_list', 'booking_room_list.room_number_id', 'room_numbers.id')
            ->leftJoin('bookings', 'bookings.id', 'booking_room_list.booking_id')
            ->select(
                'room_numbers.*',
                'room_numbers.id as id',
                'room_types.name',
                'bookings_id as booking_id',
                'bookings.check_in',
                'bookings.check_out',
                'bookings.name as customer_name',
                'bookings.phone as customer_phone',
                'bookings.status as booking_status',
                'bookings.code as booking_no',
            )
            ->orderBy('room_types.id', 'ASC')
            ->orderBy('bookings.id', 'desc')
            ->get();

        return view('backend.allroom.roomlist.view_roomlist', compact('room_number_list'));
    }

    public function addRoomList()
    {
        $roomtype=RoomType::all();
        return view('backend.allroom.roomlist.add_roomlist', compact('roomtype'));
    }

    public function storeRoomList(Request $request){


        if ($request->check_in == $request->check_out){
            $request->flash();
            $notification=array(
                'message'=>'Check-in and Check-out date must be different',
                'alert-type'=>'error'
            );
            return redirect()->back()->with($notification);
        }

        if ($request->available_room < $request->number_of_rooms){
            $request->flash();
            $notification=array(
                'message'=>'Number of rooms must be less than available',
                'alert-type'=>'error'
            );
            return redirect()->back()->with($notification);
        }

        $room = Room::find($request['room_id']);
        if ($room->room_capacity < $request['number_of_person']){
            $notification=array(
                'message'=>'You Enter Maximum Numbers of Guest',
                'alert-type'=>'error'
            );
            return redirect()->back()->with($notification);
        }


        $toDate = Carbon::parse($request['check_in']);
        $fromDate = Carbon::parse($request['check_out']);
        $total_nights = $toDate->diffInDays($fromDate);

        $subtotal = $room->price * $total_nights * $request->number_of_rooms;
        $discount = (($room->discount / 100) * $subtotal);
        $total_price = $subtotal - $discount;
        $code = rand(000000000, 999999999);

        $data = new Booking();
        $data->room_id = $room->id;
        $data->user_id = Auth::user()->id;
        $data->check_in = date('Y-m-d', strtotime($request['check_in']));
        $data->check_out = date('Y-m-d', strtotime($request['check_out']));
        $data->person = $request->number_of_person;
        $data->number_of_rooms = $request->number_of_rooms;
        $data->total_night = $total_nights;
        $data->actual_price = $room->price;
        $data->subtotal = $subtotal;
        $data->discount = $discount;
        $data->total_price = $total_price;
        $data->payment_method = 'COD';
        $data->payment_status = 0;

        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone = $request->phone;
        $data->country = $request->country;
        $data->state = $request->state;
        $data->zip_code = $request->zip_code;
        $data->address = $request->address;

        $data->code = $code;
        $data->status = 0;
        $data->created_at = Carbon::now();
        $data->save();

        $sdate = date('Y-m-d', strtotime($request['check_in']));
        $edate = date('Y-m-d', strtotime($request['check_out']));
        $eldate = Carbon::create($edate)->subDay();
        $d_period = CarbonPeriod::create($sdate, $eldate);
        foreach ($d_period as $period) {
            $booked_dates = new RoomBookedDate();
            $booked_dates->booking_id = $data->id;
            $booked_dates->room_id = $room->id;
            $booked_dates->book_date = date('Y-m-d', strtotime($period));
            $booked_dates->save();
        }

        $notification = array(
           'message'=>'Room Booked Successfully',
            'alert-type'=>'success'
        );
        return redirect()->with($notification);
    }
}
