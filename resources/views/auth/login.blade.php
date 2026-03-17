@extends('app')

@section('title', 'Login - MOOC Platform')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card overflow-hidden">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="h-100 p-4 p-lg-5 text-white" style="background: linear-gradient(135deg, #1c1d1f 0%, #5624d0 100%);">
                        <span class="market-badge bg-white text-dark mb-3">
                            <i class="bi bi-mortarboard-fill"></i> Welcome back
                        </span>
                        <h2 class="fw-bold mb-3">Login ke learning hub Anda.</h2>
                        <p class="mb-4 text-white-50">Lanjutkan course, cek progres, dan kelola pembelajaran Anda dengan tampilan baru yang lebih profesional.</p>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="fw-bold mb-2">Login</h2>
                        <p class="text-muted mb-4">Masuk untuk mengakses dashboard, course, dan manajemen pembelajaran Anda.</p>

                        @if(!empty($selectedCourse))
                            <div class="alert alert-info">
                                <i class="bi bi-book"></i> Anda sedang melanjutkan pendaftaran untuk course <strong>{{ $selectedCourse->title }}</strong>.
                            </div>
                        @endif

                        <form method="POST" action="/login">
                            @csrf
                            @if(!empty($selectedCourse))
                                <input type="hidden" name="course" value="{{ $selectedCourse->id }}">
                            @endif

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input 
                                    type="email" 
                                    class="form-control @error('email') is-invalid @enderror" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email') }}"
                                    required 
                                    autofocus
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input 
                                    type="password" 
                                    class="form-control @error('password') is-invalid @enderror" 
                                    id="password" 
                                    name="password" 
                                    required
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4 form-check">
                                <input 
                                    type="checkbox" 
                                    class="form-check-input" 
                                    id="remember" 
                                    name="remember"
                                >
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </button>
                        </form>

                        <p class="text-center text-muted mb-0">
                            Belum punya akun?
                            <a href="{{ !empty($selectedCourse) ? route('register', ['course' => $selectedCourse->id]) : '/register' }}" class="text-primary text-decoration-none fw-bold">Register di sini</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
