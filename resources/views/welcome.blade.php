<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Serena Care - Sistema de Gestión de Eventos</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=poppins:300,400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .float-animation {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .fade-in-up {
            animation: fadeInUp 0.8s ease-out forwards;
        }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
        .delay-400 { animation-delay: 0.4s; }
    </style>
</head>
<body class="h-full antialiased">
    <!-- Navigation -->
    <nav class="absolute top-0 z-50 w-full">
        <div class="container px-6 py-6 mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="https://serenacare.net/wp-content/uploads/2023/09/serena-nursing-home-01.webp" alt="Serena Care" class="h-12">
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-6 py-2 text-sm font-medium text-white transition rounded-lg glass-effect hover:bg-white/20">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-2 text-sm font-medium text-white transition rounded-lg glass-effect hover:bg-white/20">
                            Iniciar Sesión
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative flex items-center justify-center min-h-screen overflow-hidden gradient-bg">
        <!-- Animated Background Shapes -->
        <div class="absolute inset-0 overflow-hidden opacity-20">
            <div class="absolute w-96 h-96 bg-white rounded-full blur-3xl -top-20 -left-20 float-animation"></div>
            <div class="absolute bg-white rounded-full w-96 h-96 blur-3xl -bottom-20 -right-20 float-animation" style="animation-delay: 2s;"></div>
        </div>

        <div class="container relative z-10 px-6 mx-auto">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="mb-6 text-5xl font-bold text-white opacity-0 md:text-7xl fade-in-up">
                    Gestión de Eventos
                    <span class="block mt-2">Serena Care</span>
                </h1>
                <p class="mb-8 text-xl text-white/90 opacity-0 fade-in-up delay-100">
                    Sistema profesional de gestión de eventos para casas de huéspedes.
                    Muestra tus eventos en tiempo real con elegancia y simplicidad.
                </p>
                <div class="flex flex-col justify-center gap-4 opacity-0 sm:flex-row fade-in-up delay-200">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-8 py-4 text-lg font-semibold text-purple-600 transition bg-white rounded-lg shadow-xl hover:shadow-2xl hover:scale-105">
                            Ir al Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-8 py-4 text-lg font-semibold text-purple-600 transition bg-white rounded-lg shadow-xl hover:shadow-2xl hover:scale-105">
                            Comenzar
                        </a>
                    @endauth
                    <a href="#houses" class="px-8 py-4 text-lg font-semibold text-white transition border-2 border-white rounded-lg glass-effect hover:bg-white/20">
                        Ver Casas
                    </a>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-10">
            <svg class="w-6 h-6 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </div>
    </section>

    <!-- Houses Section -->
    <section id="houses" class="py-24 bg-white">
        <div class="container px-6 mx-auto">
            <div class="mb-16 text-center">
                <h2 class="mb-4 text-4xl font-bold text-gray-900 md:text-5xl">
                    Nuestras <span class="gradient-text">Casas</span>
                </h2>
                <p class="text-xl text-gray-600">
                    Gestiona eventos para cada una de tus propiedades
                </p>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                @php
                    $houses = \App\Models\House::all();
                @endphp

                @forelse($houses as $house)
                    <div class="overflow-hidden transition bg-white shadow-lg rounded-2xl hover:shadow-2xl hover:scale-105">
                        @if($house->default_image_path)
                            <div class="h-64 overflow-hidden">
                                <img src="{{ Storage::url($house->default_image_path) }}" alt="{{ $house->name }}" class="object-cover w-full h-full transition hover:scale-110">
                            </div>
                        @else
                            <div class="flex items-center justify-center h-64 gradient-bg">
                                <svg class="w-24 h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </div>
                        @endif
                        <div class="p-6">
                            <h3 class="mb-2 text-2xl font-bold text-gray-900">{{ $house->name }}</h3>
                            <p class="text-gray-600">{{ $house->location }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-3 text-center text-gray-500">
                        <p>No hay casas configuradas aún.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-24 overflow-hidden gradient-bg">
        <div class="absolute inset-0 overflow-hidden opacity-10">
            <div class="absolute w-96 h-96 bg-white rounded-full blur-3xl -top-20 -right-20 float-animation"></div>
        </div>

        <div class="container relative z-10 px-6 mx-auto text-center">
            <h2 class="mb-6 text-4xl font-bold text-white md:text-5xl">
                ¿Listo para comenzar?
            </h2>
            <p class="mb-8 text-xl text-white/90">
                Accede al panel de administración y comienza a gestionar tus eventos hoy mismo.
            </p>
            @auth
                <a href="{{ url('/dashboard') }}" class="inline-block px-8 py-4 text-lg font-semibold text-purple-600 transition bg-white rounded-lg shadow-xl hover:shadow-2xl hover:scale-105">
                    Ir al Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-block px-8 py-4 text-lg font-semibold text-purple-600 transition bg-white rounded-lg shadow-xl hover:shadow-2xl hover:scale-105">
                    Iniciar Sesión
                </a>
            @endauth
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-gray-900">
        <div class="container px-6 mx-auto">
            <div class="flex flex-col items-center justify-between md:flex-row">
                <div class="flex items-center gap-3 mb-4 md:mb-0">
                    <img src="https://serenacare.net/wp-content/uploads/2023/09/serena-nursing-home-01.webp" alt="Serena Care" class="h-10">
                </div>
                <div class="text-gray-400">
                    <p>&copy; {{ date('Y') }} Serena Care. Sistema de Gestión de Eventos.</p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
