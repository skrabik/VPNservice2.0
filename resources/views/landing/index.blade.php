<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quantum Shield VPN - Защищенный доступ к интернету</title>
    <meta name="description" content="Quantum Shield VPN - быстрый, безопасный и надежный VPN сервис. Защитите свою конфиденциальность и получите доступ к заблокированным сайтам.">
    <meta name="keywords" content="VPN, защита, конфиденциальность, безопасность, интернет">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .hero-pattern {
            background-image: radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 0%, transparent 50%);
        }
        
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes pulse-glow {
            from { box-shadow: 0 0 20px rgba(102, 126, 234, 0.4); }
            to { box-shadow: 0 0 30px rgba(102, 126, 234, 0.8); }
        }
    </style>
</head>
<body class="bg-gray-50">
    <header class="fixed w-full top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-200">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shield-alt text-white text-xl"></i>
                    </div>
                    <span class="text-2xl font-bold gradient-text">Quantum Shield</span>
                </div>
                
                <nav class="hidden md:flex items-center space-x-8">
                    <a href="#features" class="text-gray-600 hover:text-blue-600 transition-colors">Возможности</a>
                    <a href="#pricing" class="text-gray-600 hover:text-blue-600 transition-colors">Тарифы</a>
                    <a href="#support" class="text-gray-600 hover:text-blue-600 transition-colors">Поддержка</a>
                </nav>
                
                <div class="flex items-center space-x-4">
                    <a href="https://t.me/quantum_shield_bot" target="_blank" class="hidden sm:inline-flex btn-primary text-white px-6 py-2 rounded-lg font-medium">
                        <i class="fab fa-telegram mr-2"></i>Начать
                    </a>
                    
                    <button class="md:hidden mobile-menu-toggle text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <div class="md:hidden mobile-menu hidden bg-white border-t border-gray-200">
            <div class="px-4 py-6 space-y-4">
                <a href="#features" class="block text-gray-600 hover:text-blue-600 transition-colors py-2">Возможности</a>
                <a href="#pricing" class="block text-gray-600 hover:text-blue-600 transition-colors py-2">Тарифы</a>
                <a href="#support" class="block text-gray-600 hover:text-blue-600 transition-colors py-2">Поддержка</a>
                <a href="https://t.me/quantum_shield_bot" target="_blank" class="btn-primary text-white px-6 py-3 rounded-lg font-medium block text-center">
                    <i class="fab fa-telegram mr-2"></i>Подключиться к VPN
                </a>
            </div>
        </div>
    </header>

    <section class="gradient-bg hero-pattern min-h-screen flex items-center relative overflow-hidden">
        <div class="absolute inset-0 bg-black/30"></div>
        <div class="container mx-auto px-4 py-20 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div class="text-white animate-fade-in-left">
                    <h1 class="text-5xl lg:text-6xl font-bold mb-6 leading-tight drop-shadow-lg">
                        Защищенный доступ к 
                        <span class="text-yellow-300 drop-shadow-lg">интернету</span>
                    </h1>
                    <p class="text-xl mb-8 text-white leading-relaxed drop-shadow-md">
                        Quantum Shield VPN обеспечивает максимальную защиту вашей конфиденциальности, 
                        высокую скорость соединения и доступ к заблокированному контенту по всему миру.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="https://t.me/quantum_shield_bot" target="_blank" class="btn-primary text-white px-8 py-4 rounded-lg font-semibold text-lg flex items-center justify-center pulse-glow">
                            <i class="fab fa-telegram mr-3 text-xl"></i>
                            Подключиться к VPN
                        </a>
                        <a href="#features" class="bg-white/20 backdrop-blur-md text-white px-8 py-4 rounded-lg font-semibold text-lg flex items-center justify-center hover:bg-white/30 transition-all">
                            <i class="fas fa-play mr-3"></i>
                            Узнать больше
                        </a>
                    </div>
                    
                    <div class="mt-12 grid grid-cols-3 gap-8">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-300 drop-shadow-lg">99.9%</div>
                            <div class="text-sm text-white drop-shadow-md">Время работы</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-300 drop-shadow-lg">14</div>
                            <div class="text-sm text-white drop-shadow-md">Серверов</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-yellow-300 drop-shadow-lg">10K+</div>
                            <div class="text-sm text-white drop-shadow-md">Клиентов</div>
                        </div>
                    </div>
                </div>
                
                <div class="relative animate-fade-in-right">
                    <div class="floating">
                        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                    <span class="text-white font-medium">Quantum Shield VPN</span>
                                </div>
                                <div class="text-white text-sm">Защищено</div>
                            </div>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-300">Скорость:</span>
                                    <span class="text-white font-semibold">1 Гбит/с</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-300">Сервер:</span>
                                    <span class="text-white font-semibold">Автоматически</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-300">Протокол:</span>
                                    <span class="text-white font-semibold">OpenVPN</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Почему выбирают Quantum Shield?</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Мы предлагаем лучшие технологии защиты и максимальную скорость соединения для вашего комфорта
                </p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="feature-card bg-gray-50 p-8 rounded-2xl text-center animate-fade-in-up">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shield-alt text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Максимальная защита</h3>
                    <p class="text-gray-600">
                        Военное шифрование AES-256 и протоколы OpenVPN обеспечивают полную защиту ваших данных
                    </p>
                </div>
                
                <div class="feature-card bg-gray-50 p-8 rounded-2xl text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-bolt text-green-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Высокая скорость</h3>
                    <p class="text-gray-600">
                        Оптимизированные серверы обеспечивают скорость до 1 Гбит/с без ограничений
                    </p>
                </div>
                
                <div class="feature-card bg-gray-50 p-8 rounded-2xl text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-globe text-purple-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">14 стран</h3>
                    <p class="text-gray-600">
                        Серверы в 14 странах мира для доступа к любому контенту и обхода блокировок
                    </p>
                </div>
                
                <div class="feature-card bg-gray-50 p-8 rounded-2xl text-center">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-user-secret text-red-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Нет логов</h3>
                    <p class="text-gray-600">
                        Мы не ведем логи вашей активности. Ваша конфиденциальность - наш приоритет
                    </p>
                </div>
                
                <div class="feature-card bg-gray-50 p-8 rounded-2xl text-center">
                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-mobile-alt text-yellow-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Все устройства</h3>
                    <p class="text-gray-600">
                        Поддержка всех популярных платформ: Windows, macOS, Linux, iOS, Android
                    </p>
                </div>
                
                <div class="feature-card bg-gray-50 p-8 rounded-2xl text-center">
                    <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-headset text-indigo-600 text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">24/7 поддержка</h3>
                    <p class="text-gray-600">
                        Наша команда поддержки готова помочь вам в любое время через Telegram
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="pricing" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Выберите свой план</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Гибкие тарифные планы для любых потребностей. Все планы включают полный функционал
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <div class="bg-white p-8 rounded-2xl shadow-lg border-2 border-gray-200 pricing-card">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Базовый</h3>
                        <div class="text-4xl font-bold text-blue-600 mb-2">299₽</div>
                        <div class="text-gray-600">в месяц</div>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Скорость до 100 Мбит/с</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Все серверы</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>2 устройства</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>24/7 поддержка</span>
                        </li>
                    </ul>
                    <a href="https://t.me/quantum_shield_bot" target="_blank" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-center block hover:bg-blue-700 transition-colors">
                        Выбрать план
                    </a>
                </div>
                
                <div class="bg-white p-8 rounded-2xl shadow-lg border-2 border-blue-500 relative pricing-card">
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-blue-500 text-white px-4 py-1 rounded-full text-sm font-semibold">Популярный</span>
                    </div>
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Стандарт</h3>
                        <div class="text-4xl font-bold text-blue-600 mb-2">499₽</div>
                        <div class="text-gray-600">в месяц</div>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Скорость до 500 Мбит/с</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Все серверы</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>5 устройств</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Приоритетная поддержка</span>
                        </li>
                    </ul>
                    <a href="https://t.me/quantum_shield_bot" target="_blank" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-center block hover:bg-blue-700 transition-colors">
                        Выбрать план
                    </a>
                </div>
                
                <div class="bg-white p-8 rounded-2xl shadow-lg border-2 border-gray-200 pricing-card">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Премиум</h3>
                        <div class="text-4xl font-bold text-blue-600 mb-2">799₽</div>
                        <div class="text-gray-600">в месяц</div>
                    </div>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Скорость до 1 Гбит/с</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Все серверы</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>Неограниченно устройств</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-3"></i>
                            <span>VIP поддержка</span>
                        </li>
                    </ul>
                    <a href="https://t.me/quantum_shield_bot" target="_blank" class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold text-center block hover:bg-blue-700 transition-colors">
                        Выбрать план
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 gradient-bg">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold text-white mb-6 drop-shadow-lg">Готовы начать?</h2>
            <p class="text-xl text-white mb-8 max-w-2xl mx-auto drop-shadow-md">
                Присоединяйтесь к тысячам пользователей, которые уже доверяют Quantum Shield VPN
            </p>
            <a href="https://t.me/quantum_shield_bot" target="_blank" class="bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg inline-flex items-center hover:bg-gray-100 transition-colors">
                <i class="fab fa-telegram mr-3 text-xl"></i>
                Подключиться сейчас
            </a>
        </div>
    </section>

    <footer id="support" class="bg-gray-900 text-white py-16">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-2 mb-6">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shield-alt text-white text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold">Quantum Shield</span>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md">
                        Quantum Shield VPN - ваш надежный партнер в мире безопасного интернета. 
                        Мы обеспечиваем максимальную защиту и скорость для вашего комфорта.
                    </p>
                    <div class="flex space-x-4">
                        <a href="https://t.me/quantum_shield_bot" target="_blank" class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors">
                            <i class="fab fa-telegram text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-6">Продукт</h3>
                    <ul class="space-y-3">
                        <li><a href="#features" class="text-gray-400 hover:text-white transition-colors">Возможности</a></li>
                        <li><a href="#pricing" class="text-gray-400 hover:text-white transition-colors">Тарифы</a></li>
                        <li><a href="https://t.me/quantum_shield_bot" target="_blank" class="text-gray-400 hover:text-white transition-colors">Подключиться</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-6">Поддержка</h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="https://t.me/skrabik0" target="_blank" class="text-gray-400 hover:text-white transition-colors flex items-center">
                                <i class="fab fa-telegram mr-2"></i>
                                Техподдержка
                            </a>
                        </li>
                        <li>
                            <a href="https://t.me/quantum_shield_bot" target="_blank" class="text-gray-400 hover:text-white transition-colors flex items-center">
                                <i class="fab fa-telegram mr-2"></i>
                                Telegram бот
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-12 pt-8 text-center">
                <p class="text-gray-400">
                    © 2024 Quantum Shield VPN. Все права защищены.
                </p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/landing.js') }}"></script>
</body>
</html> 