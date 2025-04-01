<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Backend\TeamController;
use App\Http\Controllers\Backend\RoomTypeController;
use App\Http\Controllers\Backend\RoomController;
use App\Http\Controllers\Backend\RoomListController;
use App\Http\Controllers\Backend\SettingController;
use App\Http\Controllers\Backend\TestimonialController;
use App\Http\Controllers\Backend\BlogController;
use App\Http\Controllers\Backend\CommentController;
use App\Http\Controllers\Backend\ReportController;
use App\Http\Controllers\Backend\GalleryController;
use App\Http\Controllers\Backend\RoleController;
use App\Http\Controllers\Frontend\FrontendRoomController;
use App\Http\Controllers\Frontend\BookingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'index']);

Route::get('/dashboard', function () {
    return view('frontend.dashboard.user_dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [UserController::class, 'userProfile'])->name('user.profile');
    Route::post('/profile/store', [UserController::class, 'userStore'])->name('profile.store');
    Route::get('/user/logout', [UserController::class, 'userLogout'])->name('user.logout');
    Route::get('/user/change/password', [UserController::class, 'userChangePassword'])->name('user.change.password');
    Route::post('/password/change/password', [UserController::class, 'changePasswordStore'])->name('password.change.store');
});

require __DIR__ . '/auth.php';

// Admin Group Middleware
Route::middleware(['auth', 'roles:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'adminDashboard'])->name('dashboard');
    Route::get('/logout', [AdminController::class, 'adminLogout'])->name('logout');
    // Profile Routes
    Route::get('/profile', [AdminController::class, 'adminProfile'])->name('profile');
    Route::post('/profile/store', [AdminController::class, 'adminProfileStore'])->name('profile.store');
    // Password Change Routes
    Route::get('/change/password', [AdminController::class, 'adminChangePassword'])->name('change.password');
    Route::post('/password/update', [AdminController::class, 'adminPasswordUpdate'])->name('password.update');
});
// End Admin Group Middleware

Route::get('/admin/login', [AdminController::class, 'adminLogin'])->name('admin.login');

// Admin Group Middleware
Route::middleware(['auth', 'roles:admin'])->group(function () {

    /// Team All Route
    Route::controller(TeamController::class)->group(function () {

        Route::get('/all/team', 'allTeam')->name('all.team');
        Route::get('/add/team', 'addTeam')->name('add.team');
        Route::post('/team/store', 'storeTeam')->name('team.store');
        Route::get('/edit/team/{id}', 'editTeam')->name('edit.team');
        Route::post('/team/update', 'updateTeam')->name('team.update');
        Route::get('/delete/team/{id}', 'deleteTeam')->name('delete.team');
    });

    // Book Area All Route
    Route::controller(TeamController::class)->group(function () {

        Route::get('/book/area', 'bookArea')->name('book.area');
        Route::post('/book/area/update', 'bookAreaUpdate')->name('book.area.update');

    });

    // RoomType All Route
    Route::controller(RoomTypeController::class)->group(function () {

        Route::get('/room/type/list', 'roomTypeList')->name('room.type.list');
        Route::get('/add/room/type', 'addRoomType')->name('add.room.type');
        Route::post('/room/type/store', 'roomTypeStore')->name('room.type.store');

    });

    // Room All Route
    Route::controller(RoomController::class)->group(function () {

        Route::get('/edit/room/{id}', 'editRoom')->name('edit.room');
        Route::post('/update/room/{id}', 'updateRoom')->name('update.room');
        Route::get('/multi/image/delete/{id}', 'multiImageDelete')->name('multi.image.delete');
        Route::post('/store/room/no/{id}', 'storeRoomNumber')->name('store.room.no');
        Route::get('/edit/roomno/{id}', 'editRoomNumber')->name('edit.roomno');
        Route::post('/update/roomno/{id}', 'updateRoomNumber')->name('update.roomno');
        Route::get('/delete/roomno/{id}', 'deleteRoomNumber')->name('delete.roomno');
        Route::get('/delete/room/{id}', 'deleteRoom')->name('delete.room');

    });

    /// Admin Booking All Route
    Route::controller(BookingController::class)->group(function () {

        Route::get('/booking/list', 'bookingList')->name('booking.list');
        Route::get('/edit_booking/{id}', 'editBooking')->name('edit_booking');
        Route::get('/download/invoice/{id}', 'downloadInvoice')->name('download.invoice');


    });

    /// Admin Room List All Route
    Route::controller(RoomListController::class)->group(function () {

        Route::get('/view/room/list', 'viewRoomList')->name('view.room.list');
        Route::get('/add/room/list', 'addRoomList')->name('add.room.list');
        Route::post('/store/roomlist', 'storeRoomList')->name('store.roomlist');


    });

    /// Admin Room List All Route
    Route::controller(SettingController::class)->group(function () {

        Route::get('/smtp/setting', 'smtpSetting')->name('smtp.setting');
        Route::post('/smtp/update', 'smtpUpdate')->name('smtp.update');

    });

    /// Testimonial All Route
    Route::controller(TestimonialController::class)->group(function () {

        Route::get('/all/testimonial', 'allTestimonial')->name('all.testimonial');
        Route::get('/add/testimonial', 'addTestimonial')->name('add.testimonial');
        Route::post('/store/testimonial', 'storeTestimonial')->name('testimonial.store');
        Route::get('/edit/testimonial/{id}', 'editTestimonial')->name('edit.testimonial');
        Route::post('/update/testimonial', 'updateTestimonial')->name('testimonial.update');
        Route::get('/delete/testimonial/{id}', 'deleteTestimonial')->name('delete.testimonial');

    });

    /// Blog Category All Route
    Route::controller(BlogController::class)->group(function () {

        Route::get('/blog/category', 'blogCategory')->name('blog.category');
        Route::post('/store/blog/category', 'storeBlogCategory')->name('store.blog.category');
        Route::get('/edit/blog/category/{id}', 'editBlogCategory');
        Route::post('/update/blog/category', 'updateBlogCategory')->name('update.blog.category');
        Route::get('/delete/blog/category/{id}', 'deleteBlogCategory')->name('delete.blog.category');

    });

    /// Blog Post All Route
    Route::controller(BlogController::class)->group(function () {

        Route::get('/all/blog/post', 'allBlogPost')->name('all.blog.post');
        Route::get('/add/blog/post', 'addBlogPost')->name('add.blog.post');
        Route::post('/store/blog/post', 'storeBlogPost')->name('store.blog.post');
        Route::get('/edit/blog/post/{id}', 'editBlogPost')->name('edit.blog.post');
        Route::post('/update/blog/post', 'updateBlogPost')->name('update.blog.post');
        Route::get('/delete/blog/post/{id}', 'deleteBlogPost')->name('delete.blog.post');


    });

    /// Frontend Comment All Route
    Route::controller(CommentController::class)->group(function () {
        Route::get('/all/comment/', 'allComment')->name('all.comment');
        Route::post('/update/comment/status', 'updateCommentStatus')->name('update.comment.status');

    });

    /// Booking Report All Route
    Route::controller(ReportController::class)->group(function () {
        Route::get('/booking/report/', 'bookingReport')->name('booking.report');
        Route::post('/search-by-date', 'searchByDate')->name('search-by-date');
    });

    /// Site Setting All Route
    Route::controller(SettingController::class)->group(function () {

        Route::get('/site/setting', 'siteSetting')->name('site.setting');
        Route::post('/site/update', 'siteUpdate')->name('site.update');


    });

    /// Gallery All Route
    Route::controller(GalleryController::class)->group(function () {

        Route::get('/all/gallery', 'allGallery')->name('all.gallery');
        Route::get('/add/gallery', 'addGallery')->name('add.gallery');
        Route::post('/store/gallery', 'storeGallery')->name('store.gallery');
        Route::get('/edit/gallery/{id}', 'editGallery')->name('edit.gallery');
        Route::post('/update/gallery', 'updateGallery')->name('update.gallery');
        Route::get('/delete/gallery/{id}', 'deleteGallery')->name('delete.gallery');

        Route::post('/delete/gallery/multiple', 'deleteGalleryMultiple')->name('delete.gallery.multiple');

        // contact message admin view
        Route::get('/contact/message', 'adminContactMessage')->name('contact.message');


    });

    /// Permission All Route
    Route::controller(RoleController::class)->group(function () {

        Route::get('/all/permission', 'allPermission')->name('all.permission');
        Route::get('/add/permission', 'addPermission')->name('add.permission');
        Route::post('/store/permission', 'storePermission')->name('store.permission');
        Route::get('/edit/permission/{id}', 'editPermission')->name('edit.permission');
        Route::post('/update/permission', 'updatePermission')->name('update.permission');
        Route::get('/delete/permission/{id}', 'deletePermission')->name('delete.permission');

        Route::get('/import/permission', 'importPermission')->name('import.permission');
        Route::get('/export', 'export')->name('export');
        Route::post('/import', 'import')->name('import');


    });

    /// Role All Route
    Route::controller(RoleController::class)->group(function () {

        Route::get('/all/roles', 'allRoles')->name('all.roles');
        Route::get('/add/roles', 'addRoles')->name('add.roles');
        Route::post('/store/roles', 'storeRoles')->name('store.roles');
        Route::get('/edit/roles/{id}', 'editRoles')->name('edit.roles');
        Route::post('/update/roles', 'updateRoles')->name('update.roles');
        Route::get('/delete/roles/{id}', 'deleteRoles')->name('delete.roles');
//
//
        Route::get('/add/roles/permission', 'addRolesPermission')->name('add.roles.permission');
        Route::post('/role/permission/store', 'rolePermissionStore')->name('role.permission.store');
        Route::get('/all/roles/permission', 'allRolesPermission')->name('all.roles.permission');
//
        Route::get('/admin/edit/roles/{id}', 'adminEditRoles')->name('admin.edit.roles');
        Route::post('/admin/roles/update/{id}', 'adminRolesUpdate')->name('admin.roles.update');
        Route::get('/admin/delete/roles/{id}', 'adminDeleteRoles')->name('admin.delete.roles');

    });

    Route::controller(AdminController::class)->group(function () {

        Route::get('/all/admin', 'allAdmin')->name('all.admin');
        Route::get('/add/admin', 'addAdmin')->name('add.admin');
        Route::post('/store/admin', 'storeAdmin')->name('store.admin');
        Route::get('/edit/admin/{id}', 'editAdmin')->name('edit.admin');
        Route::post('/update/admin/{id}', 'updateAdmin')->name('update.admin');
        Route::get('/delete/admin/{id}', 'deleteAdmin')->name('delete.admin');

    });

}); //End Admin Group Middleware

// Room All Route
Route::controller(FrontendRoomController::class)->group(function () {

    Route::get('/rooms/', 'allFrontendRoomList')->name('froom.all');
    Route::get('/room/details/{id}', 'roomDetailsPage');

    Route::get('/bookings/', 'bookingSearch')->name('booking.search');
    Route::get('/search/room/details/{id}', 'searchRoomDetails')->name('search_room_details');

    Route::get('/check_room_availability/', 'checkRoomAvailability')->name('check_room_availability');

});

// Auth Middleware User must have login for access this route
Route::middleware(['auth'])->group(function () {

    /// CHECKOUT ALL Route
    Route::controller(BookingController::class)->group(function () {

        Route::get('/checkout/', 'checkout')->name('checkout');
        Route::post('/booking/store/', 'bookingStore')->name('user_booking_store');
        Route::post('/checkout/store/', 'checkoutStore')->name('checkout.store');
        Route::match(['get', 'post'], '/stripe_pay', [BookingController::class, 'stripe_pay'])->name('stripe_pay');
//
        // booking Update
        Route::post('/update/booking/status/{id}', 'updateBookingStatus')->name('update.booking.status');
        Route::post('/update/booking/{id}', 'updateBooking')->name('update.booking');
//
        // Assign Room Route
        Route::get('/assign_room/{id}', 'assignRoom')->name('assign_room');
        Route::get('/assign_room/store/{booking_id}/{room_number_id}', 'assignRoomStore')->name('assign_room_store');
        Route::get('/assign_room_delete/{id}', 'assignRoomDelete')->name('assign_room_delete');
//
//        // User Booking Route
//
        Route::get('/user/booking', 'userBooking')->name('user.booking');
        Route::get('/user/invoice/{id}', 'userInvoice')->name('user.invoice');


    });

}); // End Group Auth Middleware

/// Frontend Blog  All Route
Route::controller(BlogController::class)->group(function () {

    Route::get('/blog/details/{slug}', 'blogDetails');
    Route::get('/blog/cat/list/{id}', 'blogCatList');
    Route::get('/blog', 'blogList')->name('blog.list');
});

/// Frontend Comment All Route
Route::controller(CommentController::class)->group(function () {

    Route::post('/store/comment/', 'storeComment')->name('store.comment');


});

/// Frontend Gallery All Route
Route::controller(GalleryController::class)->group(function () {

    Route::get('/gallery', 'showGallery')->name('show.gallery');

    // Contact All Route
    Route::get('/contact', 'contactUs')->name('contact.us');
    Route::post('/store/contact', 'storeContactUs')->name('store.contact');

});

/// Notification All Route
Route::controller(BookingController::class)->group(function () {

    Route::post('/mark-notification-as-read/{notification}', 'markAsRead');


});




