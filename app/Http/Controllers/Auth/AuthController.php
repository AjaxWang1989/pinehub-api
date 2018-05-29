<?php

namespace App\Http\Controllers\Auth;

use App\Entities\AuthSecretKey;
use App\Entities\User;
use App\Http\Response\UpdateResponse;
use App\Repositories\UserRepositoryEloquent;
use App\Transformers\Api\UpdateResponseTransformer;
use App\Transformers\AuthenticateTransformer;
use App\Transformers\AuthPublicKeyTransformer;
use \Dingo\Api\Http\Request;
use App\Http\Controllers\Controller;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Auth;
use Prettus\Validator\Contracts\ValidatorInterface;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //
    const RULES = [
        'mobile' => ['required', 'regex:'.MOBILE_PATTERN],
        'password' => ['required', 'regex:'.PASSWORD_PATTERN]
    ];
    const MESSAGES = [
        'mobile.required' => '缺少手机号码',
        'password.required' => '未提交密码',
        'mobile.regex' => '手机号码格式错误',
        'password.regex' => '密码格式错误'
    ];

    protected $userModel = null;

    /**
     * User controller construct function
     * @param UserRepositoryEloquent $userRepository
     * */
    public function __construct(UserRepositoryEloquent $userRepository)
    {
        $this->userModel = $userRepository;
    }

    public function getPublicKey()
    {
        $item = new AuthSecretKey();
        return $this->response()->item($item, new AuthPublicKeyTransformer());
    }

    /**
     * 登录验证
     * @param Request $request
     * @return Response|null
     * @throws
     * */
    public function authenticate(Request $request)
    {
        if($this->auth()->check()){
            $token = Auth::refresh();
            return $this->response()->item($this->user(), new AuthenticateTransformer)->addMeta('token' , $token);
        }else{
            if($input = $this->validate($request, self::RULES,self::MESSAGES)){
                if(!($token = Auth::attempt($input))){;
                    $this->response()->error('登录密码与手机不匹配无法登录！', HTTP_STATUS_NOT_FOUND);
                }
                $user = JWTAuth::toUser($token);
                if(toUserModel($user)->status === User::FREEZE_ACCOUNT){
                    $this->response()->error('该用户已经冻结账号无法登录', HTTP_STATUS_FORBIDDEN);
                }else if(toUserModel($user)->status === User::WAIT_AUTH_ACCOUNT){
                    $this->response()->error('该用户账号尚未激活无法登录', HTTP_STATUS_FORBIDDEN);
                }
                $user->lastLoginAt = date('Y-m-d h:m:s');
                $user->save();
                return $this->response()->item($user, new AuthenticateTransformer)->addMeta('token', $token);
            }
        }
        return null;
    }

    /**
     * 退出
     * @return Response|null
     * @throws
     * */
    public function logout()
    {
        try{
            if(Auth::logout(false)){
                return $this->response()->item(new UpdateResponse('退出成功'), new UpdateResponseTransformer());
            }else{
                $this->response()->error('退出失败', HTTP_STATUS_OK);
            }
        }catch (\Exception $exception){

        }
        return null;
    }

    /**
     * 注册
     * @return Response|null
     * @throws
     * */
    public function register( )
    {
        /** @var array $user */
        if(($user = $this->userModel->makeValidator()->passesOrFail(ValidatorInterface::RULE_CREATE))){
            $user['mobile_company'] = mobileCompany($user['mobile']);
            $user['password'] = password($user['password'], true);
            $model = $this->userModel->create($user);
            if(!$model){
                $this->response()->error('注册失败', HTTP_STATUS_INTERNAL_SERVER_ERROR);
            }else{
                return $this->response()->item($model, new AuthenticateTransformer);
            }
        }
        return null;
    }
}
