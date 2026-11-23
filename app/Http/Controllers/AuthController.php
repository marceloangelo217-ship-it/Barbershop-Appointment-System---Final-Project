<?php

namespace App\Http\Controllers;

use App\Services\JsonStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(private JsonStorageService $store) {}

    public function showRegister() { return view('auth.register'); }
    public function showLogin() { return view('auth.login'); }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:80'],
            'email' => ['required','email','max:120'],
            'phone' => ['required','regex:/^(09|\+639)\d{9}$/'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        try {
            $user = $this->store->mutate('users.json', function (&$users) use ($data) {
                $email = strtolower(trim($data['email']));
                foreach ($users as $u) {
                    if (($u['email'] ?? '') === $email) {
                        throw new \RuntimeException('This email is already registered.');
                    }
                }
                $id = 1;
                foreach ($users as $u) { $id = max($id, ((int)($u['id'] ?? 0)) + 1); }
                $user = [
                    'id' => $id,
                    'name' => trim($data['name']),
                    'email' => $email,
                    'phone' => $data['phone'],
                    'password' => Hash::make($data['password']),
                    'role' => 'customer',
                ];
                $users[] = $user;
                return $user;
            });
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['email' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Unable to save account. Please try again.');
        }

        $request->session()->put('user', $this->publicUser($user));
        return redirect()->route('book')->with('success', 'Welcome, ' . $user['name'] . '!');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);
        $users = $this->store->read('users.json');
        $email = strtolower(trim($data['email']));
        $found = null;
        foreach ($users as $u) if (($u['email'] ?? '') === $email) { $found = $u; break; }
        if (!$found || !Hash::check($data['password'], $found['password'] ?? '')) {
            return back()->withInput()->with('error', 'Invalid email or password.');
        }
        $request->session()->put('user', $this->publicUser($found));
        $dest = ($found['role'] ?? '') === 'admin' ? 'admin.index' : 'bookings.mine';
        return redirect()->route($dest)->with('success', 'You are logged in.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'You have been logged out.');
    }

    private function publicUser(array $u): array
    {
        return ['id'=>$u['id'], 'name'=>$u['name'], 'email'=>$u['email'], 'phone'=>$u['phone'], 'role'=>$u['role']];
    }
}
