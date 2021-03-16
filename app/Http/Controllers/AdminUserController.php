<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    //thật toán tìm kiếm vè phân trang khi không dùng đến xác định tài khoản có bị xóa chưa vĩnh viễn hay không
    // function list(Request $request){
    //     $keyword = "";
    //     if($request->input('keyword')){
    //         $keyword = $request->input('keyword');
    //     }
    //     //lấy thông tin theo điề kiện
    //     $users =  User::where('name','LIKE',"%{$keyword}%")->paginate(2);
    //     return view('admin.user.list', compact('users'));
    // }
    function __construct(){
        $this->middleware(function($request, $next){
            session(['module_active'=>'user']);
            return $next($request);
        });
    }
    function list(Request $request){
        $status = $request->input('status');
        $list_act=['delete'=>'xóa tạm thời'];

        if($status == 'trash'){
            $list_act=['restore'=>'khôi phục',
            'forceDelete'=>'xóa vĩnh viễn'];
            $users =User::onlyTrashed()->paginate(2);
        }
        else{
        $keyword = "";
        if($request->input('keyword')){
            $keyword = $request->input('keyword');
        }
        //lấy thông tin theo điề kiện
        $users =  User::where('name','LIKE',"%{$keyword}%")->paginate(2);
    }
    $count_user_active = User::count();
    $count_user_trash = User::onlyTrashed()->count();
    $count = [$count_user_active, $count_user_trash];

        return view('admin.user.list', compact('users','count','list_act'));
    }

    function add(){
        return view('admin.user.add');
    }
    function store(Request $request){

            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ],
            [
                'required'=>':attribute không được để trống',
                'min'=>':attribute có độ dại ít nhất :min ký tự',
                'max'=>':attribute có độ dài tối đa :max ký tự' ,
                'confirmed'=>'xác nhận mật khẩu không thành công',
            ]
            );
            User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);
            return redirect('admin/user/list')->with('status','đã thêm thành viên thanh công');

    }
    function delete($id){
        if(Auth::id()!=$id){
            $user = User::find($id);
            $user->delete();
            return redirect('admin/user/list')->with('status','xóa thành viên thành công');
        }
        else{
            return redirect('admin/user/list')->with('status','bạn không thể xoas mình ra khỏi hệ thống');
        }
    }
    function action(Request $request){
        $list_check = $request->input('list_check');
        if($list_check){
            foreach($list_check as $k=>$id){
                if(Auth::id()==$id){
                    unset($list_check[$k]); //loại phần tử trùng id ra khỏi mảng
                }
            }
            if(!empty($list_check)){
                $act = $request->input('act');
                if($act == 'delete'){
                    User::destroy($list_check);
                    return redirect('admin/user/list')->with('status','bạn đã xóa thành công');
                }
                if($act == 'restore'){
                    User::withTrashed()
                    ->whereIn('id',$list_check)
                    ->restore();
                    return redirect('admin/user/list')->with('status','bạn đã khôi phục thành công');
                }
                if($act == 'forceDelete'){
                    User::withTrashed()
                    ->whereIn('id',$list_check)
                    ->forceDelete();
                    return redirect('admin/user/list')->with('status','bạn đã xõa vĩnh viễn tài khoản thành công');
                }
            }
            return redirect('admin/user/list')->with('status','bạn không thể thao tác trên tài khoản của bạn');
        }else{
            return redirect('admin/user/list')->with('status','bạn cần chọn phần tử để thucwcj hiện');
        }
    }
    //là phần xóa và khôi phục user
    function edit($id){
        $user = User::find($id);
        return view('admin.user.edit',compact('user'));
    }
    function update(Request $request ,$id){
        $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ],
        [
            'required'=>':attribute không được để trống',
            'min'=>':attribute có độ dại ít nhất :min ký tự',
            'max'=>':attribute có độ dài tối đa :max ký tự' ,
            'confirmed'=>'xác nhận mật khẩu không thành công',
        ]
        );

        User::where('id', $id)->update([
            'name'=>$request->input('name'),
            'password'=>Hash::make($request->input('password')),
        ]);
        return redirect('admin/user/list')->with('status','đã cập nhật thông tin thành công');

    }

}
