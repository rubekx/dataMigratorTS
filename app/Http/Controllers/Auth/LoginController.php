<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\User;
use Illuminate\Validation\ValidationException;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**joy
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }



    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);
        $user = NULL;
        if( filter_var($request->email, FILTER_VALIDATE_EMAIL))
            $user = User::where('email', '=', $request->email)->get()->first();
        else{
            $user = User::join('people', 'users.person_id', '=', 'people.id')
                        ->where('people.cpf', '=', $request->email)->get()->first();
            if($user == NULL)
                return $this->sendInvalidCpfResponse($request);
            $user = User::where('email', '=', $user->email)->get()->first();
        }

        if($user && $user->profile->status_id == 35)
        {
            return $this->sendLockedAccountResponse($request);
        }


        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Get the failed login response instance when user has blocked status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendLockedAccountResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.locked')],
        ]);
    }

    /**
     * Get the failed login response instance when invalid or non-existing cpg is input
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendInvalidCpfResponse(Request $request)
    {
        throw ValidationException::withMessages([
            $this->username() => [trans('auth.invalidCpf')],
        ]);
    }

    /**
     * Test if username is email or cpf
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function credentials(Request $request)
    {
        $login = request()->input('email'); 

        // Check whether cpf or email is being used
        if(!filter_var($request->email, FILTER_VALIDATE_EMAIL)){
            $user = User::join('people', 'users.person_id', '=', 'people.id')
                        ->where('people.cpf', '=', $request->email)->get()->first();
            $login = $user->email;
        }

        return [ 
            'email'    => $login, 
            'password' => $request->password, 
        ]; 
    }
}
