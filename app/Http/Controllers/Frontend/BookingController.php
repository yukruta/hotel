<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\BookConfirm;
use App\Models\Booking;
use App\Models\BookingRoomList;
use App\Models\Room;
use App\Models\RoomBookedDate;
use App\Models\RoomNumber;
use App\Models\User;
use App\Notifications\BookingComplete;
use Barryvdh\DomPDF\PDF;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Stripe\Stripe;

class BookingController extends Controller
{
    public function checkout()
    {
        if (Session::has('book_date')) {
            $book_date = Session::get('book_date');
            $room = Room::find($book_date['room_id']);

            $toDate = Carbon::parse($book_date['check_in']);
            $fromDate = Carbon::parse($book_date['check_out']);
            $nights = $toDate->diffInDays($fromDate);
            return view('frontend.checkout.checkout', compact('room', 'nights', 'book_date'));

        } else {

            $notification = array(
                'message' => 'Something went wrong',
                'alert-type' => 'error'
            );
            return redirect('/')->with($notification);
        }
    }

    public function bookingStore(Request $request)
    {
        $validateData = $request->validate([
            'check_in' => 'required',
            'check_out' => 'required',
            'person' => 'required',
            'number_of_rooms' => 'required',
        ]);

        if ($request->available_room < $request->number_of_rooms) {
            $notification = array(
                'message' => 'Room number is less than available',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);

        }
        Session::forget('book_date');

        $data = array();
        $data['number_of_rooms'] = $request->number_of_rooms;
        $data['available_room'] = $request->available_room;
        $data['person'] = $request->person;
        $data['check_in'] = date('Y-m-d', strtotime($request->check_in));
        $data['check_out'] = date('Y-m-d', strtotime($request->check_out));
        $data['room_id'] = $request->room_id;

        Session::put('book_date', $data);
        return redirect()->route('checkout');

    }

    public function checkoutStore(Request $request)
    {
        $user = User::where('role', 'admin')->get();
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
            'country' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'payment_method' => 'required',

        ]);

        $book_date = Session::get('book_date');
        $toDate = Carbon::parse($book_date['check_in']);
        $fromDate = Carbon::parse($book_date['check_out']);
        $total_nights = $toDate->diffInDays($fromDate);

        $room = Room::find($book_date['room_id']);
        $subtotal = $room->price * $total_nights * $book_date['number_of_rooms'];
        $discount = (($room->discount / 100) * $subtotal);
        $total_price = $subtotal - $discount;
        $code = rand(000000000, 999999999);

        if ($request->payment_method == 'Stripe') {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            $s_pay = Stripe::create([
                "amount" => $total_price * 100,
                "currency" => "usd",
                "source" => $request->stripeToken,
                "description" => "Payment for Booking. Booking #" . $code,
            ]);

            if ($s_pay['status'] == 'succeeded') {
                $payment_status = 1;
                $transaction_id = $s_pay->id;
            } else {
                $notification = array(
                    'messege' => 'Sorry Payment Failed',
                    'alert-type' => 'error'
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $payment_status = 0;
            $transaction_id = '';

        }

        $data = new Booking();
        $data->room_id = $room->id;
        $data->user_id = Auth::user()->id;
        $data->check_in = date('Y-m-d', strtotime($book_date['check_in']));
        $data->check_out = date('Y-m-d', strtotime($book_date['check_out']));
        $data->person = $book_date['person'];
        $data->number_of_rooms = $book_date['number_of_rooms'];
        $data->total_night = $total_nights;
        $data->actual_price = $room->price;
        $data->subtotal = $subtotal;
        $data->discount = $discount;
        $data->total_price = $total_price;
        $data->payment_method = $request->payment_method;
        $data->transaction_id = $transaction_id;
        $data->payment_status = $payment_status;

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

        $sdate = date('Y-m-d', strtotime($book_date['check_in']));
        $edate = date('Y-m-d', strtotime($book_date['check_out']));
        $eldate = Carbon::create($edate)->subDay();
        $d_period = CarbonPeriod::create($sdate, $eldate);
        foreach ($d_period as $period) {
            $booked_dates = new RoomBookedDate();
            $booked_dates->booking_id = $data->id;
            $booked_dates->room_id = $room->id;
            $booked_dates->book_date = date('Y-m-d', strtotime($period));
            $booked_dates->save();
        }
        Session::forget('book_date');
        $notification = array(
            'message' => 'Successfully booked',
            'alert-type' => 'success'
        );

        Notification::send($user, new BookingComplete($request->name));


        return redirect('/')->with($notification);

    }

    public function bookingList()
    {
        $allData = Booking::orderBy('id', 'desc')->get();
        return view('backend.booking.booking_list', compact('allData'));
    }

    public function editBooking($id)
    {
        $editData = Booking::with('room')->find($id);
        return view('backend.booking.edit_booking', compact('editData'));
    }

    public function updateBookingStatus(Request $request, $id)
    {

        $booking = Booking::find($id);
        $booking->payment_status = $request->payment_status;
        $booking->status = $request->status;
        $booking->save();

        //Start sent email

        $sendmail = Booking::find($id);
        $data = [
            'check_in' => $sendmail->check_in,
            'check_out' => $sendmail->check_out,
            'name' => $sendmail->name,
            'email' => $sendmail->email,
            'phone' => $sendmail->phone,
        ];
        Mail::to($sendmail->email)->send(new BookConfirm($data));

        //End sent email
        $notification = array(
            'message' => 'Booking Status Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    public function updateBooking(Request $request, $id)
    {
        if ($request->available_room < $request->number_of_room) {
            $notification = array(
                'messege' => 'Room number is less than available',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        }
        $data = Booking::find($id);
        $data->number_of_rooms = $request->number_of_room;
        $data->check_in = date('Y-m-d', strtotime($request->check_in));
        $data->check_out = date('Y-m-d', strtotime($request->check_out));
        $data->save();

        RoomBookedDate::where('booking_id', $id)->delete();
        RoomBookedDate::where('booking_id', $id)->delete();

        $sdate = date('Y-m-d', strtotime($request->check_in));
        $edate = date('Y-m-d', strtotime($request->check_out));
        $eldate = Carbon::create($edate)->subDay();
        $d_period = CarbonPeriod::create($sdate, $eldate);
        foreach ($d_period as $period) {
            $booked_dates = new RoomBookedDate();
            $booked_dates->booking_id = $data->id;
            $booked_dates->room_id = $data->rooms_id;
            $booked_dates->book_date = date('Y-m-d', strtotime($period));
            $booked_dates->save();
        }
        $notification = array(
            'message' => 'Booking Status Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);

    }

    public function assignRoom($booking_id)
    {
        $booking = Booking::find($booking_id);
        $booking_date_array = RoomBookedDate::where('booking_id', $booking_id)->pluck('book_date')->toArray();

        $check_date_booking_ids =
            RoomBookedDate::whereIn('book_date', $booking_date_array)
                ->where('room_id', $booking->rooms_id)
                ->distinct()->pluck('booking_id')->toArray();

        $booking_ids = Booking::whereIn('id', $check_date_booking_ids)->pluck('id')->toArray();

        $assign_room_ids = BookingRoomList::whereIn('booking_id', $booking_ids)->pluck('room_number_id')->toArray();

        $room_numbers = RoomNumber::where('rooms_id', $booking->rooms_id)
            ->whereNoIN('id', $assign_room_ids)
            ->where('status', 'Active')
            ->get();

        return view('backend.booking.assign_room', compact('booking', 'room_numbers'));
    }

    public function assignRoomStore($booking_id, $room_number_id)
    {

        $booking = Booking::find($booking_id);
        $check_data = BookingRoomList::where('booking_id', $booking_id)->count();
        if ($check_data < $booking->number_of_rooms) {
            $assign_data = new BookingRoomList();
            $assign_data->booking_id = $booking_id;
            $assign_data->room_id = $booking->rooms_id;
            $assign_data->room_number_id = $room_number_id;
            $assign_data->save();
            $notification = array(
                'message' => 'Room number assigned successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);


        } else {

            $notification = array(
                'message' => 'Room number is less than available',
                'alert-type' => 'error'
            );
            return redirect()->back()->with($notification);
        }
    }

    public function assignRoomDelete($id)
    {
        $assign_data = BookingRoomList::find($id);
        $assign_data->delete();
        $notification = array(
            'message' => 'Room number assigned successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    public function downloadInvoice($id)
    {
        $editData = Booking::with('room')->find($id);
        $pdf = PDF::loadView('backend.booking.booking_invoice', compact('editData'))
            ->setPaper('a4', 'landscape')->setOption([
                'tempDir' => public_path(),
                'chroot' => public_path(),
            ]);
        return $pdf->download('invoice.pdf');
    }

    public function userBooking()
    {
        $id = Auth::user()->id;
        $allData = Booking::where('user_id', $id)->orderBy('id', 'DESC')->get();
        return view('frontend.dashboard.user_booking', compact('allData'));
    }

    public function userInvoice($id)
    {
        $editData = Booking::with('room')->find($id);
        $pdf = PDF::loadView('backend.booking.booking_invoice', compact('editData'))
            ->setPaper('a4', 'landscape')->setOption([
                'tempDir' => public_path(),
                'chroot' => public_path(),
            ]);
        return $pdf->download('invoice.pdf');
    }

    public function markAsRead(Request $request, $notificationId)
    {

        $user = Auth::user();
        $notification = $user->notifications()->where('id', $notificationId)->first();

        if ($notification) {
            $notification->markAsRead();
        }

        return response()->json(['count' => $user->unreadNotifications()->count()]);
    }

}

