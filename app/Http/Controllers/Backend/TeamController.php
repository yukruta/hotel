<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BookArea;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Image;


class TeamController extends Controller
{
    public function allTeam()
    {
        $team = Team::latest()->get();
        return view('backend.team.all_team', compact('team'));
    }

    public function addTeam()
    {
        return view('backend.team.add_team');
    }

    public function storeTeam(Request $request)
    {

        $image = $request->file('image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(550, 670)->save('upload/team/' . $name_gen);
        $save_url = 'upload/team/' . $name_gen;

        Team::insert([
            'name' => $request->name,
            'position' => $request->position,
            'facebook' => $request->facebook,
            'image' => $save_url,
            'created_at' => Carbon::now(),
        ]);
        $notification = array(
            'message' => 'Team Added Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.team')->with($notification);
    }

    public function editTeam($id)
    {

        $team = Team::findOrFail($id);
        return view('backend.team.edit_team', compact('team'));
    }

    public function updateTeam(Request $request, $id)
    {
        $team_id = $request->id;

        if ($request->file('image')) {
            $image = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(550, 670)->save('upload/team/' . $name_gen);
            $save_url = 'upload/team/' . $name_gen;

            Team::findOrFail($team_id)->update([
                'name' => $request->name,
                'position' => $request->position,
                'facebook' => $request->facebook,
                'image' => $save_url,
                'created_at' => Carbon::now(),
            ]);
            $notification = array(
                'message' => 'Team Updated With Image Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.team')->with($notification);
        } else {
            Team::findOrFail($team_id)->update([
                'name' => $request->name,
                'position' => $request->position,
                'facebook' => $request->facebook,
                'created_at' => Carbon::now(),
            ]);
            $notification = array(
                'message' => 'Team Updated Without Image Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.team')->with($notification);

        }
    }

    public function deleteTeam($id)
    {
        $item = Team::findOrFail($id);
        $img = $item->image;
        unlink($img);

        Team::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Team  Image  Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    //Book Area All Methods

    public function bookArea()
    {

        $book = BookArea::find(1);
        return view('backend.team.book_area', compact('book'));
    }

    public function bookAreaUpdate(Request $request)
    {

        $book_id = $request->id;

        if ($request->file('image')) {
            $image = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(1000, 1000)->save('upload/bookarea/' . $name_gen);
            $save_url = 'upload/bookarea/' . $name_gen;

            BookArea::findOrFail($book_id)->update([
                'short_title' => $request->short_title,
                'main_title' => $request->main_title,
                'short_desc' => $request->short_desc,
                'link_url' => $request->link_url,
                'image' => $save_url,
            ]);
            $notification = array(
                'message' => 'Book Area Updated With Image Successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);


        } else {
            BookArea::findOrFail($book_id)->update([
                'short_title' => $request->short_title,
                'main_title' => $request->main_title,
                'short_desc' => $request->short_desc,
                'link_url' => $request->link_url,
            ]);
            $notification = array(
                'message' => 'Book Area Updated Without Image Successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);

        }

    }
}
