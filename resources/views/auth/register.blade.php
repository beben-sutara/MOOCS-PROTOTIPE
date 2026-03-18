@extends('app')

@section('title', 'Register - MoocsPangarti')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card overflow-hidden">
            <div class="row g-0">
                <div class="col-lg-5">
                    <div class="h-100 p-4 p-lg-5 text-white" style="background: linear-gradient(135deg, #a435f0 0%, #5624d0 100%);">
                        <span class="market-badge bg-white text-dark mb-3">
                            <i class="bi bi-person-plus-fill"></i> Join now
                        </span>
                        <h2 class="fw-bold mb-3">Buat akun dan mulai belajar.</h2>
                        <p class="mb-4 text-white-50">Nikmati pengalaman belajar yang lebih modern, kelola progres, dan akses course dari tampilan yang lebih premium.</p>
                        <div class="surface-muted p-3 bg-white bg-opacity-10 border-0 text-white">
                            <div class="fw-semibold mb-2">Apa yang Anda dapat?</div>
                            <small class="d-block mb-2">Akses ke course dan modul terstruktur</small>
                            <small class="d-block mb-2">Progress tracking dan XP system</small>
                            <small class="d-block">Dashboard student dan instructor yang lebih rapi</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card-body p-4 p-lg-5">
                        <h2 class="fw-bold mb-2">Register</h2>
                        <p class="text-muted mb-4">Isi data berikut untuk membuat akun baru.</p>

                        @if(!empty($selectedCourse))
                            <div class="alert alert-info">
                                <i class="bi bi-book"></i> Setelah akun dibuat, Anda akan langsung terdaftar ke course <strong>{{ $selectedCourse->title }}</strong>.
                            </div>
                        @endif

                        <form method="POST" action="/register">
                            @csrf
                            @if(!empty($selectedCourse))
                                <input type="hidden" name="course" value="{{ $selectedCourse->id }}">
                            @endif

                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input 
                                    type="text" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    id="name" 
                                    name="name" 
                                    value="{{ old('name') }}"
                                    required 
                                    autofocus
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input 
                                    type="email" 
                                    class="form-control @error('email') is-invalid @enderror" 
                                    id="email" 
                                    name="email" 
                                    value="{{ old('email') }}"
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number (Optional)</label>
                                <input 
                                    type="tel" 
                                    class="form-control @error('phone') is-invalid @enderror" 
                                    id="phone" 
                                    name="phone" 
                                    value="{{ old('phone') }}"
                                >
                                @error('phone')
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
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input 
                                    type="password" 
                                    class="form-control @error('password_confirmation') is-invalid @enderror" 
                                    id="password_confirmation" 
                                    name="password_confirmation" 
                                    required
                                >
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="bi bi-person-plus"></i> Create Account
                            </button>
                        </form>

                        <p class="text-center text-muted mb-0">
                            Sudah punya akun?
                            <a href="{{ !empty($selectedCourse) ? route('login', ['course' => $selectedCourse->id]) : '/login' }}" class="text-primary text-decoration-none fw-bold">Login di sini</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
