<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CKEditorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        //
    }

    public function upload(Request $request)
    {
        // 1) تحقق صارم: صورة فقط، وامتداد من قائمة بيضاء، وحد أقصى للحجم
        $request->validate([
            'upload' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:4096'],
        ]);

        // 2) اسم عشوائي بالكامل — لا نثق باسم الملف الأصلي إطلاقاً
        $extension = strtolower($request->file('upload')->getClientOriginalExtension());
        $filenametostore = \Illuminate\Support\Str::random(40).'_'.time().'.'.$extension;

        // 3) خزّن الملف
        $request->file('upload')->storeAs('public/uploads', $filenametostore);

        // 4) رقم الدالة رقمي فقط (منع حقن JS)
        $CKEditorFuncNum = (int) $request->input('CKEditorFuncNum');
        $url = asset('storage/uploads/'.$filenametostore);
        $msg = 'Image successfully uploaded';
        $re = "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '".e($url)."', '".e($msg)."')</script>";

        @header('Content-type: text/html; charset=utf-8');
        echo $re;
    }
}
