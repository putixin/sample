<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\User;

use Auth;

use Mail;

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
        //将用户所有微博取出分页
        $statuses = $user->statuses()
                         ->orderBy('create_at','desc')
                         ->paginate(20);
        return view('users.show',compact('user','statuses'));
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

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮件上，请注意查收。');
        return redirect('/');
    }

    // 该方法将用于发送邮件给指定用户。我们会在用户注册成功之后调用该方法来发送激活邮件
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = '52putixin@gmail.com';
        $name = 'Putixin';
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";

        Mail::send($view, $data, function($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }
    //邮箱激活方法
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
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
