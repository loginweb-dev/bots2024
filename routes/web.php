<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
	return redirect('/admin');
});

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();

    Route::get('/clear', function () {
        App\Evento::where('user_id', Auth::user()->id)->truncate();
        App\Contacto::where('user_id', Auth::user()->id)->truncate();
        App\Grupo::where('user_id', Auth::user()->id)->truncate();       
        // App\Whatsapp::where('user_id', Auth::user()->id)->truncate();
        App\Descarga::where('user_id', Auth::user()->id)->truncate();
        App\Plantilla::where('user_id', Auth::user()->id)->truncate();

        $miuser = App\Models\User::find(Auth::user()->id);
        Storage::disk('public')->deleteDirectory($miuser->name);

        return redirect('/admin/whatsapps');
    });
});
