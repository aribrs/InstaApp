<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Posting;
use Session;
use App\Models\Like;

class PostingController extends Controller
{
    //
    private $img = 'image-not-found.svg';
    private $directory;

    public function __construct()
    {
        $this->directory = public_path() . '/uploads/';
    }

    public function self_posting()
    {
        $data_posting = Posting::all()->where('id_user', auth()->user()->id)->sortByDesc('created_at');
        $container = [];
        foreach ($data_posting as $item_value) {
            $container[] = $this->container($item_value);
        }
        return response()->json(array('data' => $container));
    }

    public function show_posting()
    {
        $data_posting = Posting::all()->sortByDesc('created_at');
        $container = [];
        foreach ($data_posting as $item_value) {
            $container[] = $this->container($item_value);
        }
        return response()->json(array('data' => $container));
    }

    public function edit($id)
    {
        $model = Posting::where('id_user', auth()->user()->id)->findOrFail($id);
        return response()->json($model);
    }

    public function store(Request $req)
    {
        $this->validate($req, [
            'status' => 'required'
        ]);

        if (!empty($req->file)) {
            $initial_img = $req->file;
            $this->img = $initial_img->getClientOriginalName();
            $array['img'] = $this->img;
        }
        $array['status'] = $req->status;
        $array['id_user'] = auth()->user()->id;
        $model = new Posting($array);
        if ($model->save()) {
            if (!empty($req->file)) {
                $initial_img->move($this->directory, $this->img);
            }
            return redirect()->back()->with('message_success', 'Succesfully posted');
        } else {
            return redirect()->back()->with('message_error', 'Error when posting');
        }
    }

    public function update(Request $req, $id)
    {
        $this->validate($req, [
            'status' => 'required'
        ]);

        if (!empty($req->file)) {
            $initial_img = $req->file;
            $this->img = $initial_img->getClientOriginalName();
            $array['img'] = $this->img;
        }
        $array['status'] = $req->status;
        $array['id_user'] = auth()->user()->id;
        $model = Posting::where('id_user', auth()->user()->id)->find($id);
        if ($model->update($array)) {
            if (!empty($req->file)) {
                $initial_img->move($this->directory, $this->img);
            }
            return redirect()->back()->with('message_success', 'Succesfully updated');
        } else {
            return redirect()->back()->with('message_error', 'Error when updating');
        }
    }

    public function destroy(Request $req, $id)
    {
        $model = Posting::where('id_user', auth()->user()->id)->find($id);
        if ($model->delete()) {
            return response()->json(array('message' => 'Succesfully deleted'));
        } else {
            return response()->json(array('message' => 'Error when deleting'));
        }
    }

    public function posting_like(Request $req)
    {
        $this->validate($req, [
            'id_posting'
        ]);
        $likes = Like::where('id_user', auth()->user()->id)->where('id_posting', $req->id_posting);
        if (!empty($likes->first())) {
            $likes->delete();
        } else {
            Like::create(['id_user' => auth()->user()->id, 'id_posting' => $req->id_posting]);
        }
        return response()->json(array('message' => 'process success', 'total_like' => Posting::find($req->id_posting)->likes->count('id')));
    }

    private function container($model)
    {
        $img = "";
        $like_count = '';
        $comment = '';
        $button_edit = '';
        $button_delete = '';

        if (!empty($model->img)) {
            $img = ' <div class="form-group border-b border-gray-200 mb-1">
                       <img src="' . asset('/uploads/' . $model->img) . '" class="m-auto" style="max-width: 700px; max-height: 500px">
                    </div>';
        }

        if ($model->likes->count('id') != 0) {
            $like_count = '<span class="text-gray-500 text-sm like_count_' . $model->id . '" id="like_count_' . $model->id . '">' . $model->likes->count('id') . ' Likes </span>';
        } else {
            $like_count = '<span class="text-gray-500 text-sm like_count_' . $model->id . '" id="like_count_' . $model->id . '"></span>';
        }

        if (auth()->user()->id == $model->id_user) {
            $button_edit = '<a href="#" onclick="edit_status(' . $model->id . ')" class="text-gray-500 hover:text-yellow-600 mr-2 no-underline"><i class="fa fa-pen text-xl"></i> Edit</a>';
            $button_delete = '<a href="#" onclick="delete_status(' . $model->id . ')" class="text-gray-500 hover:text-red-600 mr-3 no-underline"><i class="fa fa-trash text-xl"></i> Delete</a>';
        }

        if ($model->comments->count('id') != 0) {
            $comment .= '   <div class="card-header">Comments</div>
                            <div class="card-body mx-2" style="height: 200px; overflow-y: auto;">
                            <div class="row"> ';
            foreach ($model->comments as $item_comment) {
                $btn_delete_comment = '';
                $btn_update_comment = '';

                if ($item_comment->id_user == auth()->user()->id) {
                    $btn_delete_comment = '<a href="#"  onclick="edit_comment(' . $item_comment->id . ')" class="m-1 text-gray-500" style="font-size: small"><i class="fa fa-pen"></i></a>';
                    $btn_update_comment = '<a href="#" onclick="delete_comment(' . $item_comment->id . ')" class="m-1 text-gray-500" style="font-size: small"><i class="fa fa-trash" ></i></a>';
                }

                $comment .= '<div class="col-md-12">
                              <label class="mb-0" style="font-weight: bold">' . $item_comment->user->name . '</label> ' . $item_comment->comment . '<br>
                              <small style="font-size: x-small;color: gray">' . date('d-m-Y', strtotime($item_comment->created_at)) . '</small>
                                   ' . $btn_delete_comment . $btn_update_comment . '
                              <hr>
                           </div>';
            }
            $comment .= '</div></div>';
        } else {
            $comment = "";
        }
        $html = '<div class="col-md-12">
                    <div class="card mt-3">

                        <div class="card-body pt-0">
                           <div class="row">
                                <div class="col-md-12">
                                    ' . $img . '
                                </div>
                                <div class="col-md-12 mx-2 mb-2">
                                    <div class="row ">
                                        <div class="col-md-6">
                                            <button onclick="like(' . $model->id . ')" class="mr-2 text-gray-500 hover:text-red-400"><i class="fa fa-heart text-xl"></i> Like</button>
                                            <button type="button" class="mr-2 text-gray-500 hover:text-blue-400" onclick="comments(' . $model->id . ')"><i class="fa fa-comments text-xl"></i> Comment</button>
                                        </div>
                                        <div class="col-md-6 text-right pr-4">
                                            ' . $button_edit . $button_delete . '
                                        </div>
                                    </div>
                                    <div class="mb-2">' . $like_count . '</div>
                                    <div>
                                        <label style="font-weight: bold">' . $model->user->name . '</label> ' . $model->status . '
                                    </div>
                                </div>
                                ' . $comment . '
                            </div>
                        </div>

                    </div>
                 </div>';
        return $html;
    }
}
