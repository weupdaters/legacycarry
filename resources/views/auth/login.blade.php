@extends('auth.layouts.main')
@section('title', 'Login')
@section('content')


<div class="account-page">
   <div class="container-fluid p-0">
       <div class="row align-items-center g-0 px-3 py-3 vh-100">

           <div class="col-xl-5">
               <div class="row">
                   <div class="col-md-8 mx-auto">
                       <div class="card">
                           <div class="card-body">
                               <div class="mb-0 p-0 p-lg-3">
                                   <div class="mb-0 border-0 p-md-4 p-lg-0">
                                       <div class="mb-4 p-0 text-lg-start text-center">
                                           <div class="auth-brand">
                                               <a href="index.html" class="logo logo-light">
                                                   <span class="logo-lg">
                                                       <img src="{{ asset('assets/images/logo-light-3.png') }}" alt="" height="24">
                                                   </span>
                                               </a>
                                               <a href="index.html" class="logo logo-dark">
                                                   <span class="logo-lg">
                                                       <img src="{{ asset('assets/images/logo-dark-3.png') }}" alt="" height="24">
                                                   </span>
                                               </a>
                                           </div>
                                       </div>

                                       <!-- Laravel Plain HTML Form -->
                                       @if (session('status'))
                                       <div class="alert alert-success">
                                          {{ session('status') }}
                                       </div>
                                    @endif

                                    @if ($errors->any())
                                       <div class="alert alert-danger">
                                          <ul class="mb-0">
                                             @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                             @endforeach
                                          </ul>
                                       </div>
                                    @endif

                                       <div class="pt-0">
                                          <form method="POST" action="{{ route('login.submit') }}" class="my-4">
                                             @csrf
                                         
                                             <!-- Email -->
                                             <div class="form-group mb-3">
                                                 <label for="emailaddress" class="form-label">Email address</label>
                                                 <input 
                                                     class="form-control @error('email') is-invalid @enderror" 
                                                     type="email" 
                                                     name="email" 
                                                     id="emailaddress" 
                                                     value="{{ old('email') }}" 
                                                     required 
                                                     autofocus 
                                                     placeholder="Enter your email">
                                                 @error('email')
                                                     <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                 @enderror
                                             </div>
                                         
                                             <!-- Password -->
                                             <div class="form-group mb-3">
                                                 <label for="password" class="form-label">Password</label>
                                                 <input 
                                                     class="form-control @error('password') is-invalid @enderror" 
                                                     type="password" 
                                                     name="password" 
                                                     id="password" 
                                                     required 
                                                     placeholder="Enter your password">
                                                 @error('password')
                                                     <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                                 @enderror
                                             </div>
                                         
                                             <!-- Remember + Forgot -->
                                             <div class="form-group d-flex mb-3">
                                                 <div class="col-sm-6">
                                                     <div class="form-check">
                                                         <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                                         <label class="form-check-label" for="remember">Remember me</label>
                                                     </div>
                                                 </div>
                                                 <div class="col-sm-6 text-end">
                                                     @if (Route::has('password.request'))
                                                         <a class="text-muted fs-14" href="{{ route('password.request') }}">Forgot password?</a>
                                                     @endif
                                                 </div>
                                             </div>
                                         
                                             <!-- Submit -->
                                             <div class="form-group mb-0 row">
                                                 <div class="col-12">
                                                     <div class="d-grid">
                                                         <button class="btn btn-primary fw-semibold" type="submit"> Log In </button>
                                                     </div>
                                                 </div>
                                             </div>
                                         </form>                                         
                                           {{-- <div class="text-center text-muted">
                                               <p class="mb-0">Don't have an account ?<a class='text-primary ms-2 fw-medium' href='auth-register.html'>Sing up</a></p>
                                           </div> --}}
                                           
                                       </div>
                                   </div>
                               </div> 
                           </div>
                       </div>
                       
                   </div>
               </div>
           </div>
           
           <div class="col-xl-7 d-none d-xl-inline-block">
               <div class="account-page-bg rounded-4">
                   <div class="auth-user-review text-center">
                       <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
                           <div class="carousel-inner">

                               <div class="carousel-item active">
                                   <p class="prelead mb-2">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"><path fill="#ffffff" d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621c.537-.278 1.24-.375 1.929-.311c1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5a3.87 3.87 0 0 1-2.748-1.179m10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621c.537-.278 1.24-.375 1.929-.311c1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5a3.87 3.87 0 0 1-2.748-1.179"/></svg> 
                                           With Untitled, your support process can be as enjoyable as your product. With it's this easy, customers keep coming back.
                                       <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"><path fill="#ffffff" d="M19.417 6.679C20.447 7.773 21 9 21 10.989c0 3.5-2.456 6.637-6.03 8.188l-.893-1.378c3.335-1.804 3.987-4.145 4.248-5.621c-.537.278-1.24.375-1.93.311c-1.804-.167-3.226-1.648-3.226-3.489a3.5 3.5 0 0 1 3.5-3.5c1.073 0 2.1.49 2.748 1.179m-10 0C10.447 7.773 11 9 11 10.989c0 3.5-2.456 6.637-6.03 8.188l-.893-1.378c3.335-1.804 3.987-4.145 4.247-5.621c-.537.278-1.24.375-1.929.311C4.591 12.323 3.17 10.842 3.17 9a3.5 3.5 0 0 1 3.5-3.5c1.073 0 2.1.49 2.748 1.179"/></svg>
                                   </p>
                                   <h4 class="mb-1">Camilla Johnson</h4>
                                   <p class="mb-0">Software Developer</p>
                               </div>

                               <div class="carousel-item">
                                   <p class="prelead mb-2">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"><path fill="#ffffff" d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621c.537-.278 1.24-.375 1.929-.311c1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5a3.87 3.87 0 0 1-2.748-1.179m10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621c.537-.278 1.24-.375 1.929-.311c1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5a3.87 3.87 0 0 1-2.748-1.179"/></svg> 
                                           Pretty nice theme, hoping you guys could add more features to this. Keep up the good work.
                                       <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"><path fill="#ffffff" d="M19.417 6.679C20.447 7.773 21 9 21 10.989c0 3.5-2.456 6.637-6.03 8.188l-.893-1.378c3.335-1.804 3.987-4.145 4.248-5.621c-.537.278-1.24.375-1.93.311c-1.804-.167-3.226-1.648-3.226-3.489a3.5 3.5 0 0 1 3.5-3.5c1.073 0 2.1.49 2.748 1.179m-10 0C10.447 7.773 11 9 11 10.989c0 3.5-2.456 6.637-6.03 8.188l-.893-1.378c3.335-1.804 3.987-4.145 4.247-5.621c-.537.278-1.24.375-1.929.311C4.591 12.323 3.17 10.842 3.17 9a3.5 3.5 0 0 1 3.5-3.5c1.073 0 2.1.49 2.748 1.179"/></svg>
                                   </p>
                                   <h4 class="mb-1">Palak Awoo</h4>
                                   <p class="mb-0">Lead Designer</p>
                               </div>

                               <div class="carousel-item">
                                   <p class="prelead mb-2">
                                       <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"><path fill="#ffffff" d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621c.537-.278 1.24-.375 1.929-.311c1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5a3.87 3.87 0 0 1-2.748-1.179m10 0C13.553 16.227 13 15 13 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621c.537-.278 1.24-.375 1.929-.311c1.804.167 3.226 1.648 3.226 3.489a3.5 3.5 0 0 1-3.5 3.5a3.87 3.87 0 0 1-2.748-1.179"/></svg> 
                                           This is a great product, helped us a lot and very quick to work with and implement. 
                                       <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 24 24"><path fill="#ffffff" d="M19.417 6.679C20.447 7.773 21 9 21 10.989c0 3.5-2.456 6.637-6.03 8.188l-.893-1.378c3.335-1.804 3.987-4.145 4.248-5.621c-.537.278-1.24.375-1.93.311c-1.804-.167-3.226-1.648-3.226-3.489a3.5 3.5 0 0 1 3.5-3.5c1.073 0 2.1.49 2.748 1.179m-10 0C10.447 7.773 11 9 11 10.989c0 3.5-2.456 6.637-6.03 8.188l-.893-1.378c3.335-1.804 3.987-4.145 4.247-5.621c-.537.278-1.24.375-1.929.311C4.591 12.323 3.17 10.842 3.17 9a3.5 3.5 0 0 1 3.5-3.5c1.073 0 2.1.49 2.748 1.179"/></svg>
                                   </p>
                                   <h4 class="mb-1">Laurent Smith</h4>
                                   <p class="mb-0">Product designer</p>
                               </div>

                           </div>
                       </div>
                   </div>
               </div>
           </div>

       </div>
   </div>
</div>

 @endsection