<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\User;

use Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth',[
            'only' => ['edit','update','destory']
        ]);

        //只让未登录用户访问注册页面
        $this->middleware('guest',[
            'only' => ['create']
        ]);
    }

    public function create()
    {
        return view('users.create');
    }

    public function show($id){
        $user = User::findOrFail($id);
        return view('users.show',compact('user'));
    }

    //将用户添加入库
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        Auth::login($user);
        session()->flash('success','欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show',[$user]);
    }

    //编辑用户
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update',$user); //authorize 方法接收两个参数，第一个为授权策略的名称，第二个为进行授权验证的数据。
        return view('users.edit',compact('user'));
    }

    public function update($id, Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:50',
            'password' => 'confirmed|min:6',//将required规则去掉
        ]);

        $user = User::findOrFail($id);
        $this->authorize('update',$user);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show',$id);
    }

    public function index(){
        $users = User::paginate(5);
        return view('users.index',compact('users'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('destroy',$user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }
}
