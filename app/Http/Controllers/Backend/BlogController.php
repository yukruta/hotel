<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Image;


class BlogController extends Controller
{
    public function blogCategory()
    {
        $category = BlogCategory::latest()->get();
        return view('backend.category.blog_category', compact('category'));
    }

    public function blogCategoryStore(Request $request)
    {
        {
            BlogCategory::insert([
                'category_name' => $request->category_name,
                'category_slug' => strtolower(str_replace(' ', '-', $request->category_slug))
            ]);

            $notification = array(
                'message' => 'Category Added Successfully',
                'alert-type' => 'success'
            );
            return redirect()->back()->with($notification);
        }
    }

    public function editBlogCategory($id)
    {

        $categories = BlogCategory::find($id);
        return response()->json($categories);
    }

    public function updateBlogCategory(Request $request)
    {

        $cat_id = $request->cat_id;
        BlogCategory::find($cat_id)->update([
            'category_name' => $request->category_name,
            'category_slug' => strtolower(str_replace(' ', '-', $request->category_slug))
        ]);
        $notification = array(
            'message' => 'Category Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notification);
    }

    public function deleteBlogCategory($id)
    {
        BlogCategory::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Category Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    /////All Blog Post Method////////

    public function allBlogPost()
    {
        $post = BlogPost::latest()->get();
        return view('backend.post.all_post', compact('post'));
    }

    public function addBlogPost(Request $request)
    {

        $blogcat = BlogCategory::latest()->get();
        return view('backend.post.add_post', compact('blogcat'));
    }

    public function storeBlogPost(Request $request)
    {

        $image = $request->file('image');
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        Image::make($image)->resize(550, 370)->save('upload/post/' . $name_gen);
        $save_url = 'upload/post/' . $name_gen;

        BlogPost::insert([
            'blogcat_id' => $request->blogcat_id,
            'user_id' => Auth::user()->id,
            'post_title' => $request->post_title,
            'post_slug' => strtolower(str_replace(' ', '-', $request->category_slug)),
            'post_image' => $request->$save_url,
            'short_descp' => $request->short_descp,
            'long_descp' => $request->long_descp,
        ]);
        $notification = array(
            'message' => 'Blog Post Added Successfully',
            'alert-type' => 'success'
        );

        return redirect()->route('all.blog.post')->with($notification);
    }

    public function editBlogPost($id)
    {

        $blogcat = BlogCategory::latest()->get();
        $post = BlogPost::find($id)->get();
        return view('backend.post.edit_post', compact('blogcat', 'post'));
    }

    public function updateBlogPost(Request $request)
    {

        $post_id = $request->id;

        if ($request->file('image')) {
            $image = $request->file('image');
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            Image::make($image)->resize(550, 370)->save('upload/post/' . $name_gen);
            $save_url = 'upload/post/' . $name_gen;

            BlogPost::findOrFail($post_id)->update([
                'blogcat_id' => $request->blogcat_id,
                'user_id' => Auth::user()->id,
                'post_title' => $request->post_title,
                'post_slug' => strtolower(str_replace(' ', '-', $request->category_slug)),
                'post_image' => $request->$save_url,
                'short_descp' => $request->short_descp,
                'long_descp' => $request->long_descp,
            ]);
            $notification = array(
                'message' => 'Blog Post Updated Successfully',
                'alert-type' => 'success'
            );

            return redirect()->route('all.blog.post')->with($notification);
        } else {
            BlogPost::findOrFail($post_id)->update([
                'blogcat_id' => $request->blogcat_id,
                'user_id' => Auth::user()->id,
                'post_title' => $request->post_title,
                'post_slug' => strtolower(str_replace(' ', '-', $request->category_slug)),
                'short_descp' => $request->short_descp,
                'long_descp' => $request->long_descp,
            ]);
            $notification = array(
                'message' => 'Blog Post Updated Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('all.blog.post')->with($notification);
        }
    }

    public function deleteBlogPost($id){
        $item = BlogPost::findOrFail($id);
        $img = $item->post_image;
        unlink($img);

        BlogPost::findOrFail($id)->delete();

        $notification = array(
            'message' => 'Blog Post Deleted Successfully',
            'alert-type' => 'success'
        );

        return redirect()->back()->with($notification);
    }

    public function blogDetails($slug)
    {

        $blog = BlogPost::where('post_slug', $slug)->first();
        $bcategory = BlogCategory::latest()->get();
        $lpost = BlogPost::latest()->limit(3)->get();

        return view('frontend.blog.blog_details', compact('blog', 'bcategory', 'lpost'));
    }

    public function blogCatList($id){

        $blog = BlogPost::where('blogcat_id', $id)->first();
        $namecat = BlogCategory::where('id', $id)->first();
        $bcategory = BlogCategory::latest()->get();
        $lpost = BlogPost::latest()->limit(3)->get();
        return view('frontend.blog.blog_cat_list', compact('blog', 'bcategory', 'lpost', 'namecat'));

    }

    public function blogList($id){

        $blog = BlogPost::latest()->paginate(3);
        $bcategory = BlogCategory::latest()->get();
        $lpost = BlogPost::latest()->limit(3)->get();
        return view('frontend.blog.blog_all', compact('blog', 'bcategory', 'lpost'));
    }
}
