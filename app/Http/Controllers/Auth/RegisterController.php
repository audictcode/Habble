<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\User\UserBan;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('habboacademy.auth.register', [
            'habboHotels' => $this->getAvailableHabboHotels(),
        ]);
    }

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $usesCaptcha = config('academy.site.register.captchaActivated', false);
        
        $this->checkActivatedRegister();
        $this->checkActivatedMaintenance();

        $this->userHasBeenIpBanned();
        $this->checkAccountsByIP();

        $validations = [
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users', 'regex:/^([À-üA-Za-z\.:_\-0-9\!]+)$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'habbo_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[A-Za-z0-9\-\._]+$/',
                Rule::unique('users', 'habbo_name')->where(function ($query) use ($data) {
                    return $query->where('habbo_hotel', $data['habbo_hotel'] ?? null);
                }),
            ],
            'habbo_hotel' => ['required', Rule::in($this->getAvailableHabboHotels())],
        ];

        if($usesCaptcha) {
            $validations['g-recaptcha-response'] = ['recaptcha'];
        }

        return Validator::make($data, $validations);
    }

    /**
     * @return void
     */
    public function checkActivatedRegister(): void
    {
        $registerActivated = config('academy.site.register.activated', true);

        if(!$registerActivated) {
            throw ValidationException::withMessages([
                'username' => 'El registro de nuevas cuentas fue deshabilitado por el administrador.',
            ]);
        }
    }

    /**
     * @return void
     */
    public function checkActivatedMaintenance(): void
    {
        $maintenance = config('academy.site.maintenance', false);

        if($maintenance) {
            throw ValidationException::withMessages([
                'username' => 'El sitio está en mantenimiento y el registro de cuentas fue desactivado.',
            ]);
        }
    }

    /**
     * @return void
     */
    public function checkAccountsByIP(): void
    {
        $accountLimits = config('academy.site.register.accountsPerIp', 10);
        $accounts = User::where('ip_register', \Request::ip())->count();

        if($accounts >= $accountLimits) {
            throw ValidationException::withMessages([
                'username' => 'Has superado el total de cuentas permitidas por IP.',
            ]);
        }
    }

    /**
     * @return void
     */
    public function userHasBeenIpBanned(): void
    {
        $userBannedByIp = UserBan::userHasBeenIpBanned();

        if($userBannedByIp) {
            throw ValidationException::withMessages([
                'username' => 'Estás baneado por IP y no puedes crear nuevas cuentas.',
            ]);
        }
    }
    
    /**
     * @return void
     */
    public function checkBlockedUsernames(?string $username): void
    {
        $blockedUsernames = config('academy.site.register.blockedUsernames', []);

        if(in_array($username, $blockedUsernames)) {
            throw ValidationException::withMessages([
                'username' => 'No es posible crear una cuenta con ese nombre de usuario.',
            ]);
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $this->checkBlockedUsernames($data['username']);

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'habbo_name' => $data['habbo_name'],
            'habbo_hotel' => $data['habbo_hotel'],
            'ip_register' => \Request::ip(),
            'ip_last_login' => \Request::ip(),
            'last_login' => Carbon::now(),
            'password' => Hash::make($data['password']),
        ]);

        $user->update([
            'habbo_verification_code' => $this->generateHabboVerificationCode((int) $user->id),
        ]);

        $this->appendUserToSqlDump($user);

        return $user;
    }

    protected function registered(Request $request, $user)
    {
        return redirect()
            ->route('web.users.habbo-verification.show')
            ->with('success', 'Tu cuenta fue creada. Ahora verifica tu misión de Habbo para vincular el perfil.');
    }

    private function getAvailableHabboHotels(): array
    {
        return config('habbo.hotels', ['es', 'com', 'com.br']);
    }

    private function generateHabboVerificationCode(int $userId): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomBlock = '';

        for ($index = 0; $index < 4; $index++) {
            $randomBlock .= $characters[random_int(0, strlen($characters) - 1)];
        }

        $prefix = strtoupper((string) config('habbo.verification_prefix', 'HLE'));

        return sprintf('%s-%s-%d', $prefix, $randomBlock, $userId);
    }

    private function appendUserToSqlDump(User $user): void
    {
        $dumpPath = base_path('database/db.sql');
        if (!is_file($dumpPath) || !is_writable($dumpPath)) {
            return;
        }

        $createdAt = optional($user->created_at)->format('Y-m-d H:i:s');
        $updatedAt = optional($user->updated_at)->format('Y-m-d H:i:s');
        $lastLogin = optional($user->last_login)->format('Y-m-d H:i:s');

        $row = [
            (int) $user->id,
            $user->username,
            $user->password,
            $user->email,
            null,
            null,
            $createdAt,
            $updatedAt,
            $user->profile_image_path ?? 'profiles/default.png',
            $user->name,
            0,
            (int) ($user->disabled ?? 0),
            $user->ip_register,
            $user->ip_last_login,
            $lastLogin,
            $user->forum_signature,
            $user->habbo_name,
            $user->habbo_hotel,
            $user->habbo_verification_code,
            optional($user->habbo_verified_at)->format('Y-m-d H:i:s'),
        ];

        $sqlValues = array_map(function ($value) {
            if ($value === null) {
                return 'NULL';
            }
            if (is_int($value) || is_float($value)) {
                return (string) $value;
            }
            return "'" . str_replace(["\\", "'"], ["\\\\", "\\'"], (string) $value) . "'";
        }, $row);

        $sql = "\nINSERT INTO users VALUES(" . implode(',', $sqlValues) . ");";
        @file_put_contents($dumpPath, $sql, FILE_APPEND | LOCK_EX);
    }
}
