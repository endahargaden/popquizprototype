@extends('layouts.app')

@section('title', 'Login - Quiz System')

@section('styles')
<style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .auth-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        text-align: center;
    }

    .auth-header {
        margin-bottom: 30px;
    }

    .auth-header h1 {
        color: #333;
        margin: 0 0 10px 0;
        font-size: 2.5em;
        font-weight: 300;
    }

    .auth-header i {
        font-size: 3em;
        color: #667eea;
        margin-bottom: 20px;
    }

    .auth-header p {
        color: #666;
        margin: 0;
        font-size: 1.1em;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 500;
    }

    .form-group input {
        width: 100%;
        padding: 15px;
        border: 2px solid #e1e5e9;
        border-radius: 10px;
        font-size: 16px;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .form-group input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .btn {
        width: 100%;
        padding: 15px;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }

    .auth-footer {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid #e1e5e9;
    }

    .auth-footer a {
        color: #667eea;
        text-decoration: none;
        font-weight: 500;
    }

    .auth-footer a:hover {
        text-decoration: underline;
    }

    .error-message {
        background: #fee;
        color: #c33;
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #c33;
    }

    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .alert-danger {
        background: #fee;
        color: #c33;
        border-left: 4px solid #c33;
    }

    .alert-success {
        background: #efe;
        color: #363;
        border-left: 4px solid #363;
    }
</style>
@endsection

@section('content')
<div class="auth-container">
    <div class="auth-header">
        <i class="fas fa-graduation-cap"></i>
        <h1>Welcome Back</h1>
        <p>Sign in to your account</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        
        <div class="form-group">
            <label for="username">
                <i class="fas fa-user"></i>
                Username
            </label>
            <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">
                <i class="fas fa-lock"></i>
                Password
            </label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-sign-in-alt"></i>
            Sign In
        </button>
    </form>

    <div class="auth-footer">
        <p>Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
    </div>
</div>
@endsection 