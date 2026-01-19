<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::grouped();
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except(['_token', 'remove_site_logo', 'remove_site_favicon', 
                                 'remove_site_logo_dark', 'remove_site_logo_small']);
        
        foreach ($data as $key => $value) {
            // Handle file uploads
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                
                // Validate image
                $request->validate([
                    $key => 'image|mimes:png,jpg,jpeg,gif,svg,ico|max:2048'
                ], [
                    $key . '.image' => 'The ' . $key . ' must be an image.',
                    $key . '.mimes' => 'The ' . $key . ' must be a file of type: png, jpg, jpeg, gif, svg, ico.',
                    $key . '.max' => 'The ' . $key . ' must not be greater than 2MB.'
                ]);
                
                // Delete old file if exists
                $oldFile = Setting::getValue($key);
                if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
                
                // Store new file
                $path = $file->store('settings/logos', 'public');
                $value = $path;
            }
            
            // Handle checkbox values
            if ($value === '1' || $value === '0') {
                $value = (bool)$value;
            }
            
            Setting::setValue($key, $value);
        }
        
        // Handle removal requests
        $this->handleLogoRemovals($request);
        
        return back()->with('success', 'Settings updated successfully!');
    }
    
    /**
     * Handle logo and favicon removal
     */
    private function handleLogoRemovals(Request $request)
    {
        $removals = [
            'remove_site_logo' => 'site_logo',
            'remove_site_favicon' => 'site_favicon',
            'remove_site_logo_dark' => 'site_logo_dark',
            'remove_site_logo_small' => 'site_logo_small',
        ];
        
        foreach ($removals as $removeFlag => $settingKey) {
            if ($request->has($removeFlag) && $request->input($removeFlag) == '1') {
                $oldFile = Setting::getValue($settingKey);
                if ($oldFile && Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
                Setting::setValue($settingKey, null);
            }
        }
    }
    
    /**
     * Get logo URL
     */
    public static function getLogoUrl()
    {
        $logo = Setting::getValue('site_logo');
        return $logo ? asset('storage/' . $logo) : asset('images/default-logo.png');
    }
    
    /**
     * Get favicon URL
     */
    public static function getFaviconUrl()
    {
        $favicon = Setting::getValue('site_favicon');
        return $favicon ? asset('storage/' . $favicon) : asset('images/default-favicon.ico');
    }


    // public function updateProfile(Request $request)
    // {
    //     $user = auth()->user();
        
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:users,email,' . $user->id,
    //         'current_password' => 'nullable|required_with:new_password',
    //         'new_password' => 'nullable|min:8|confirmed',
    //     ]);
        
    //     $user->name = $request->name;
    //     $user->email = $request->email;
        
    //     if ($request->new_password) {
    //         if (!\Hash::check($request->current_password, $user->password)) {
    //             return back()->withErrors(['current_password' => 'Current password is incorrect']);
    //         }
    //         $user->password = \Hash::make($request->new_password);
    //     }
        
    //     $user->save();
        
    //     return back()->with('success', 'Profile updated successfully!');
    // }

    public function clearCache()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        
        return back()->with('success', 'Cache cleared successfully!');
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:15|regex:/^[0-9]{10}$/',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
        ], [
            'new_password.regex' => 'Password must contain at least one uppercase, one lowercase, one number and one special character.',
            'phone.regex' => 'Phone number must be 10 digits.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update basic info
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        
        // Update password if provided
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect'])->withInput();
            }
            $user->password = Hash::make($request->new_password);
        }
        
        // Update two-factor authentication
       // $user->two_factor_enabled = $request->boolean('two_factor_enabled');
        
        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update admin avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();
        
        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        return back()->with('success', 'Profile picture updated successfully!');
    }

    /**
     * Remove admin avatar
     */
    public function removeAvatar(Request $request)
    {
        $user = auth()->user();
        
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
        
        $user->avatar = null;
        $user->save();

        return back()->with('success', 'Profile picture removed successfully!');
    }
}