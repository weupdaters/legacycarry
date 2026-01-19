@extends('auth.layouts.main')
@section('title', 'Login')
@section('content')

<div class="wrapper">
    <section class="login-content">
       <div class="container h-100">
          <div class="row justify-content-center align-items-center height-self-center">
             <div class="col-md-5 col-sm-12 col-12 align-self-center">
                <div class="sign-user_card">
                   <img src="{{ Storage::url(setting('logo_path')) }}" class="img-fluid rounded-normal light-logo logo" alt="logo">
                   
                   <h3 class="mb-3">Sign Up</h3>
                   <p>Create your account.</p>
 
                   <!-- Show validation errors -->
                   @if ($errors->any())
                      <div class="alert alert-danger">
                         <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                               <li>{{ $error }}</li>
                            @endforeach
                         </ul>
                      </div>
                   @endif
 
                   <form method="POST" action="{{ route('register') }}">
                      @csrf
                      <div class="row">
                         <!-- Name -->
                         <div class="col-lg-6">
                            <div class="floating-label form-group">
                               <input class="floating-input form-control" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder=" ">
                               <label>Full Name</label>
                            </div>
                         </div>
 
                         <!-- Last Name (Optional) -->
                         <div class="col-lg-6">
                            <div class="floating-label form-group">
                               <input class="floating-input form-control" type="text" placeholder=" ">
                               <label>Last Name</label>
                            </div>
                         </div>
 
                         <!-- Email -->
                         <div class="col-lg-12">
                            <div class="floating-label form-group">
                               <input class="floating-input form-control" type="email" name="email" value="{{ old('email') }}" required placeholder=" ">
                               <label>Email</label>
                            </div>
                         </div>
 
                         <!-- Password -->
                         <div class="col-lg-6">
                            <div class="floating-label form-group">
                               <input class="floating-input form-control" type="password" name="password" required placeholder=" ">
                               <label>Password</label>
                            </div>
                         </div>
 
                         <!-- Confirm Password -->
                         <div class="col-lg-6">
                            <div class="floating-label form-group">
                               <input class="floating-input form-control" type="password" name="password_confirmation" required placeholder=" ">
                               <label>Confirm Password</label>
                            </div>
                         </div>
 
                         <!-- Terms Checkbox -->
                         <div class="col-lg-12">
                            <div class="custom-control custom-checkbox mb-3 text-left">
                               <input type="checkbox" class="custom-control-input" id="customCheck1" required>
                               <label class="custom-control-label" for="customCheck1">I agree with the terms of use</label>
                            </div>
                         </div>
                      </div>
 
                      <!-- Submit Button -->
                      <button type="submit" class="btn btn-primary">Sign Up</button>
 
                      <!-- Sign In Link -->
                      <p class="mt-3">
                         Already have an Account?
                         <a href="{{ route('login') }}" class="text-primary">Sign In</a>
                      </p>
                   </form>
                </div>
             </div>
          </div>
       </div>
    </section>
 </div>
 @endsection