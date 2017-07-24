<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use Auth;

class User extends Model implements AuthenticatableContract,
                                    AuthorizableContract,
                                    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    // creating 用于监听模型被创建之前的事件
    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->activation_token = str_random(30);
        });
    }

    public function gravatar($size = "100")
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    //在用户模型中，指明一个用户拥有多条微博
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    /*该方法将当前用户发布过的所有微博从数据库中取出，并根据创建时间来倒序排序。在后面
      我们为用户增加关注人的功能之后，将使用该方法来获取当前用户关注的人发布过的所有微
      博动态
    */
    public function feed()
    {
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        array_push($user_ids, Auth::user()->id);
        return status::whereIn('user_id', $user_ids)
                               ->with('user')
                               ->orderBy('created_at', 'desc');
    }

    public function followers()
    {
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }

    public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }
    //关注
    public function follow($user_ids)
    {
        if(!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }
    //取消关注
    public function unfollow($user_ids)
    {
        if(!is_array($user_ids)){
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }
    // 判断当前登录的用户 A 是否关注了用户 B，代码实现逻辑很简单，我们只需要判断用户 B 是否包含在用户 A 的关注人列表上即可
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }
}
