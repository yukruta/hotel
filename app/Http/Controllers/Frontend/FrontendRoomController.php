<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Facility;
use App\Models\MultiImage;
use App\Models\Room;
use App\Models\RoomBookedDate;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class FrontendRoomController extends Controller
{
    public function allFrontendRoomList()
    {
        $rooms = Room::latest()->get();
        return view('frontend.room.all_rooms', compact('rooms'));
    }

    public function roomDetailsPage($id)
    {
        $roomdetails = Room::find($id);
        $multiimages = MultiImage::where('room_id', $id)->get();
        $facilities = Facility::where('room_id', $id)->get();

        $otherRooms = Room::where('id', '!=', $id)->orderBy('id', 'DESC')->limit(2)->get();
        return view('frontend.room.room_details', compact('roomdetails', 'facilities', 'multiimages', 'otherRooms'));
    }

    public function bookingSearch(Request $request)
    {

        $request->flash();

        if ($request->check_in == $request->check_out) {
            $notification = array(
                'message' => "Check-in and Check-out date are not available",
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        }
        $sdate = date('Y-m-d', strtotime($request->get('check_in')));
        $edate = date('Y-m-d', strtotime($request->get('check_out')));
        $alldate = Carbon::create($edate)->subDay();
        $d_period = CarbonPeriod::create($sdate, $alldate);
        $dt_array = [];
        foreach ($d_period as $period) {
//            $dt_array[] = date('Y-m-d', strtotime($period));
            $dt_array[] = $period->format('Y-m-d'); // Правильне форматування без strtotime()
        }
        $check_date_booking_ids = RoomBookedDate::whereIn('book_date', $dt_array)->distinct()->pluck('booking_id')->toArray();

        $rooms = Room::withCount('room_numbers')->where('status', 1)->get();

        return view('frontend.room.search_room', compact('rooms', 'check_date_booking_ids'));
    }

    public function searchRoomDetails(Request $request, $id)
    {
        $request->flash();
        $roomdetails = Room::find($id);
        $multiImages = MultiImage::where('room_id', $id)->get();
        $facilities = Facility::where('room_id', $id)->get();

        $otherRooms = Room::where('id', '!=', $id)->orderBy('id', 'DESC')->limit(2)->get();
        $room_id = $id;
        return view('frontend.room.search_room_details', compact('roomdetails', 'facilities', 'multiImages', 'otherRooms', 'room_id'));

    }

    public function checkRoomAvailability(Request $request){

        $sdate = date('Y-m-d', strtotime($request->get('check_in')));
        $edate = date('Y-m-d', strtotime($request->get('check_out')));
        $alldate = Carbon::create($edate)->subDay();
        $d_period = CarbonPeriod::create($sdate, $alldate);
        $dt_array = [];
        foreach ($d_period as $period) {
//            $dt_array[] = date('Y-m-d', strtotime($period));
            $dt_array[] = $period->format('Y-m-d'); // Правильне форматування без strtotime()
        }
        $check_date_booking_ids = RoomBookedDate::whereIn('book_date', $dt_array)->distinct()->pluck('booking_id')->toArray();

        $room = Room::withCount('room_numbers')->find($request->room_id);

        $booking=Booking::withCount('assign_rooms')->whereIn('id', $check_date_booking_ids)->where('room_id', $room->id)->get()->toArray();
        $total_book_room = array_sum(array_column($booking, 'assign_rooms_count'));
        $av_room = ($room->room_numbers_count ?? '') - $total_book_room;
        $toDate = Carbon::parse($request->get('check_in'));
        $fromDate = Carbon::parse($request->get('check_out'));
        $nights = $toDate->diffInDays($fromDate);

        return response()->json(['available_room'=> $av_room, 'total_nights' => $nights]);
    }
}
