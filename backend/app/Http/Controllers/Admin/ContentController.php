<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index()
    {
        $contents = Content::orderBy('key')->orderBy('locale')->get();

        return view('admin.contents', ['contents' => $contents]);
    }

    public function update(Request $request)
    {
        $data = $request->input('contents', []);

        foreach ($data as $id => $text) {
            $item = Content::find($id);
            if ($item) {
                $item->content = $text;
                $item->save();
            }
        }

        return redirect()->route('admin.contents.index')->with('success', 'Contents updated');
    }
}
