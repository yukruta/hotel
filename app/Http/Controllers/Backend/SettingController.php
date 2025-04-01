<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\SmtpSetting;
use Illuminate\Http\Request;
use Intervention\Image\Image;

class SettingController extends Controller
{
    public function smtpSetting()
    {

        $smtp = SmtpSetting::find(1);
        return view('backend.setting.smtp_update', compact('smtp'));
    }

    public function smtpUpdate(Request $request)
    {
        $smtp_id = $request->id;

        SmtpSetting::find($smtp_id)->update([
            'mailer' => $request->mailer,
            'host' => $request->host,
            'port' => $request->port,
            'username' => $request->username,
            'password' => $request->password,
            'encryption' => $request->encryption,
            'from_address' => $request->from_address,

        ]);
        $notification = array(
            'message' => 'SMTP Settings Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    public function siteSetting()
    {

        $site = SiteSetting::find(1);
        return view('backend.site.site_update', compact('site'));
    }

    public function siteUpdate(Request $request)
    {

        $site_id = $request->id;

        if ($request->file('logo')) {
            $image = $request->file('logo');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(110, 44)->save('upload/site/' . $name_gen);
            $save_url = 'upload/site/' . $name_gen;

            SmtpSetting::findOrFail($site_id)->update([
                'phone' => $request->phone,
                'address' => $request->address,
                'email' => $request->email,
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'copyright' => $request->copyright,
                'logo' => $save_url,
            ]);
            $notification = array(
                'message' => 'Site Settings Updated Successfully',
                'alert-type' => 'success'
            );

            return redirect()->back()->with($notification);
        } else {
            SmtpSetting::findOrFail($site_id)->update([
                'phone' => $request->phone,
                'address' => $request->address,
                'email' => $request->email,
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'copyright' => $request->copyright,
            ]);
            $notification = array(
                'message' => 'Site Settings Updated Successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);

        }
    }
}
