<x-guest-layout>
    <!-- Futuristic Registration Form -->
    <div class="futuristic-register-container">
        <!-- Logo -->
        <div class="logo">
            <img src="{{ asset('storage/sys_images/logo_image.png') }}" alt="logo">
        </div>

        <!-- Welcome Message -->
        <span class="welcome-message">Create Your Account</span>

        <!-- Registration Form -->
        <form method="POST" action="{{ route('register') }}" class="futuristic-form">
            @csrf

            <!-- Name -->
            <div class="form-group">
                <x-input-label for="name" :value="__('Name')" class="futuristic-label" />
                <x-text-input id="name" class="futuristic-input" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="futuristic-error" />
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <x-input-label for="email" :value="__('Email')" class="futuristic-label" />
                <x-text-input id="email" class="futuristic-input" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="futuristic-error" />
            </div>

            <!-- Password -->
            <div class="form-group">
                <x-input-label for="password" :value="__('Password')" class="futuristic-label" />
                <x-text-input id="password" class="futuristic-input" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="futuristic-error" />
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="futuristic-label" />
                <x-text-input id="password_confirmation" class="futuristic-input" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="futuristic-error" />
            </div>

            <!-- Submit Button -->
            <div class="form-group">
                <x-primary-button class="futuristic-button">
                    {{ __('Register') }}
                </x-primary-button>
            </div>

            <!-- Already Registered Link -->
            <div class="form-group">
                <a class="futuristic-link" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Custom Futuristic Styles -->
    <style>
        /* Main Styling */
        body {
            background: linear-gradient(135deg, #1e1e1e, #121212);
            font-family: 'Orbitron', sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 30px;
        }

        /* Container for the Form */
        .futuristic-register-container {
            background: rgba(0, 0, 0, 0.7);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(255, 165, 0, 0.8);
            text-align: center;
            width: 100%;
            max-width: 450px;
        }

        /* Logo Styling */
        .logo img {
            max-width: 180px;
            margin-bottom: 20px;
            border-radius: 50%;
        }

        /* Welcome Message */
        .welcome-message {
            font-size: 1.5em;
            color: #FFA500;
            margin-bottom: 30px;
        }

        /* Form Styling */
        .futuristic-form {
            display: flex;
            flex-direction: column;
            padding-right: 20px
        }

        .form-group {
            margin-bottom: 20px;
        }

        /* Label Styling */
        .futuristic-label {
            display: block;
            text-transform: uppercase;
            color: #FFA500;
            margin-bottom: 10px;
            font-size: 0.9em;
        }

        /* Input Styling */
        .futuristic-input {
            background-color: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: #fff;
            padding: 12px;
            width: 100%;
            font-size: 1em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .futuristic-input:focus {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-color: #FFA500;
            box-shadow: 0 0 10px #FFA500;
            outline: none;
        }

        /* Error Message Styling */
        .futuristic-error {
            color: #ff4c4c;
            font-size: 0.8em;
            margin-top: 5px;
        }

        /* Button Styling */
        .futuristic-button {
            background-color: #FFA500;
            color: black;
            padding: 12px 20px;
            font-size: 1em;
            text-transform: uppercase;
            border: none;
            border-radius: 10px;
            transition: background-color 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .futuristic-button:hover {
            background-color: #FFD500;
            box-shadow: 0 0 20px #FFA500;
        }

        /* Link Styling */
        .futuristic-link {
            color: #FFA500;
            text-decoration: none;
            font-size: 0.9em;
            transition: color 0.3s;
        }

        .futuristic-link:hover {
            color: #FFD500;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .futuristic-register-container {
                padding: 20px;
                width: 90%;
            }

            .futuristic-label, .futuristic-link {
                font-size: 0.8em;
            }

            .futuristic-button {
                padding: 10px;
                font-size: 0.9em;
            }
        }

        @media (max-width: 480px) {
            .welcome-message {
                font-size: 1.2em;
            }

            .futuristic-input {
                font-size: 0.9em;
            }

            .futuristic-button {
                padding: 8px;
                font-size: 0.8em;
            }

            .futuristic-link {
                font-size: 0.8em;
            }
        }
    </style>
</x-guest-layout>
