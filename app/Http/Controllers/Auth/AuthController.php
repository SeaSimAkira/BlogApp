<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function index(Request $request)
    {
        $usersCount = User::count();
        $activeCount = User::where('is_active', true)->count();
        $adminCount = User::where('role', 'admin')->count();
        $newUsersCount = User::whereMonth('created_at', now()->month)->count();

        $users = User::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->when($request->has('status'), fn($q) => $q->where('is_active', $request->status))
            ->latest()
            ->paginate(10);

        return view('auth.index', compact('users', 'usersCount', 'activeCount', 'adminCount', 'newUsersCount'));
    }
    public function show(User $user)
    {
        return view('auth.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('auth.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
{
    $validated = $request->validate([
        'name'            => 'required|string|max:255',
        'email'           => 'required|email|unique:users,email,' . $user->id,
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'role'            => 'required|in:admin,user,editor,viewer,contributor',

        // ✅ password optional
        'password' => 'nullable|min:6|confirmed',
    ]);

    // ✅ checkbox fix (important)
    $validated['is_active'] = $request->has('is_active');

    // Upload new image
    if ($request->hasFile('profile_picture')) {

        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $validated['profile_picture'] =
            $request->file('profile_picture')->store('users', 'public');
    }

    // ✅ Update password ONLY if filled
    if ($request->filled('password')) {
        $validated['password'] = Hash::make($request->password);
    } else {
        unset($validated['password']);
    }

    $user->update($validated);

    return redirect()->route('users.index')
        ->with('success', 'User updated successfully.');
}

    public function destroy(User $user): RedirectResponse
    {
        if ($user->profile_picture) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    public function register_form(){
        return view('auth.register');
    }
    public function register(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'name'            => 'required|string|max:255',
        'email'           => 'required|email|unique:users,email',
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'password'        => 'required|string|min:6|confirmed',
        'is_active'       => 'required|boolean',
        'role'            => 'required|in:admin,user,editor,viewer,contributor',
    ]);

    // Upload image
    if ($request->hasFile('profile_picture')) {
        $validated['profile_picture'] =
            $request->file('profile_picture')->store('users', 'public');
    }

    // Hash password
    $validated['password'] = Hash::make($validated['password']);

    // Create user
    $user = User::create($validated);

    // ✅ AUTO LOGIN AFTER REGISTER
    Auth::login($user);

    // ✅ ROLE REDIRECT
    if ($user->role === 'admin') {
        return redirect()->route('users.index')
            ->with('success', 'Admin account created!');
    }

    return redirect()->route('posts.index')
        ->with('success', 'Account created successfully!');
}
    //     public function register(Request $request): RedirectResponse
    // {
    //     $validated = $request->validate([
    //         'name'            => 'required|string|max:255',
    //         'email'           => 'required|email|unique:users,email',
    //         'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'password'        => 'required|string|min:6|confirmed',
    //         'is_active'       => 'required|boolean',
    //         'role'            => 'required|in:admin,user,editor,viewer,contributor',
    //     ]);

    //     // Handle File Upload
    //     if ($request->hasFile('profile_picture')) {
    //         $file = $request->file('profile_picture');
    //         $filename = time() . '_' . $file->getClientOriginalName();
    //         $path = $file->storeAs('users', $filename, 'public');

    //         // Add the path to the validated data array
    //         $validated['profile_picture'] = $path;
    //     }

    //     // Hash the password before saving
    //     $validated['password'] = Hash::make($validated['password']);

    //     // Mass assign and save
    //     // User::create($validated);
    //     // Create user
    //     $user = User::create($validated);

    //     // ✅ AUTO LOGIN AFTER REGISTER
    //     Auth::login($user);

    //     // ✅ ROLE REDIRECT
    //     if ($user->role === 'admin') {
    //         return redirect()->route('dashboard')
    //             ->with('success', 'Admin account created!');
    //     }

    //     return redirect()->route('users.index')
    //         ->with('success', 'User registered successfully.');
    // }

    public function login_form()
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember'))) {

        $request->session()->regenerate();

        $user = Auth::user();

        // ✅ ROLE REDIRECT
        if ($user->role === 'admin') {
            return redirect()->route('dashboard')
                ->with('success', 'Welcome Admin!');
        }

        return redirect()->route('posts.index')
            ->with('success', 'Welcome back!');
    }

    return back()->withErrors([
        'email' => 'Invalid credentials.',
    ])->onlyInput('email');
}


    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully.');
    }

}





















// namespace App\Http\Controllers\Auth;

// use App\Http\Controllers\Controller;
// use App\Models\User;
// use Illuminate\Http\RedirectResponse;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Hash;

// class AuthController extends Controller
// {
//     public function index(Request $request){
//         $usersCount = User::count(); //SELECT COUNT(*) FROM users
//         $activeCount = User::where('is_active', true)->count(); //select count(*) from users where is_active = true
//         $adminCount = User::where('role', 'admin')->count(); //select count(*) from users where role = 'admin'
//         $newUsersCount = User::whereMonth('created_at', now()->month)->count(); //select count(*) from users where MONTH(created_at) = MONTH(CURRENT_DATE())
//         $users = User::query()
//             ->when($request->search, fn($q) =>
//             $q->where('name', 'like', "%{$request->search}%"))
//             ->when($request->role, fn($q) =>
//             $q->where('role', $request->role))
//             ->when($request->has('status'), fn($q) =>
//             $q->where('is_active', $request->status))
//             ->paginate(10);

//         return view('auth.index', compact('users', 'usersCount', 'activeCount', 'adminCount', 'newUsersCount'));

//     }

//     public function show(User $user){

//     }
//     public function edit(User $user){
//         return view('auth.edit', compact('user'));
//     }
//     public function update(Request $request, User $user){

//     }
//     public function destroy(User $user){

//     }



//     public function login_form(){
//         return view('auth.login');
//     }
//     public function login(Request $request){

//     }
//     public function logout(){

//     }
// }
