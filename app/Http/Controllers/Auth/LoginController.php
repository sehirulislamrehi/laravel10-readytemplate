<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserModule\SuperAdmin;
use App\Models\UserModule\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login_show()
    {
        if (auth('super_admin')->check() || auth('web')->check()) {
            return redirect()->route("dashboard");
        } else {
            return view('auth.login');
        }
    }

    public function do_login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required|min:6',
        ]);


        $superadmin = SuperAdmin::where('email', $request->email)->first();
        $user = User::where('email', $request->email)->first();

        if ($superadmin) {
            if (auth('super_admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
                $dashboard = route('dashboard');
                return response()->json(['login' => $dashboard], 200);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid Credentials',
                    'data' => null
                ], 200);
            }
        } 
        elseif ($user) {
            if( $user->is_active == true ){
                $dashboard = route('dashboard');
                Auth::guard('web')->login($user, true);
                return response()->json(['login' => $dashboard], 200);
            }
            else{
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your Account is temporary disabled. Please contact with system administrator',
                    'data' => null
                ], 200);
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Credentials',
                'data' => null
            ], 200);
        }
    }
}
