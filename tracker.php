<?php
// ==========================================
// НАСТРОЙКИ CORS
// ==========================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ==========================================
// НАСТРОЙКИ TELEGRAM БОТА
// ==========================================
define('TELEGRAM_BOT_TOKEN', '8432248033:AAGrdnXNftKqcrEGzt-wnqSynxwDeQSvMSk');
define('TELEGRAM_CHAT_ID', '1967393288');

// ==========================================
// АНАЛИЗ REFERER
// ==========================================
function analyzeReferer() {
    $referer = $_SERVER['HTTP_REFERER'] ?? null;
    
    if (!$referer || empty($referer)) {
        return [
            'source' => '🔗 Прямой переход',
            'domain' => 'Нет',
            'full_url' => 'Нет',
            'type' => 'direct'
        ];
    }
    
    // Получить домен из URL
    $parsedUrl = parse_url($referer);
    $domain = $parsedUrl['host'] ?? 'Неизвестно';
    
    // Определение социальных сетей
    $socialMedia = [
        'instagram.com' => ['icon' => '📸', 'name' => 'Instagram'],
        'facebook.com' => ['icon' => '📘', 'name' => 'Facebook'],
        'fb.com' => ['icon' => '📘', 'name' => 'Facebook'],
        't.me' => ['icon' => '💬', 'name' => 'Telegram'],
        'telegram.me' => ['icon' => '💬', 'name' => 'Telegram'],
        'whatsapp.com' => ['icon' => '📱', 'name' => 'WhatsApp'],
        'wa.me' => ['icon' => '📱', 'name' => 'WhatsApp'],
        'twitter.com' => ['icon' => '🐦', 'name' => 'Twitter'],
        'x.com' => ['icon' => '🐦', 'name' => 'X (Twitter)'],
        'tiktok.com' => ['icon' => '🎵', 'name' => 'TikTok'],
        'vk.com' => ['icon' => '🔵', 'name' => 'VKontakte'],
        'linkedin.com' => ['icon' => '💼', 'name' => 'LinkedIn'],
        'youtube.com' => ['icon' => '🎥', 'name' => 'YouTube'],
        'pinterest.com' => ['icon' => '📌', 'name' => 'Pinterest'],
        'reddit.com' => ['icon' => '🤖', 'name' => 'Reddit'],
    ];
    
    // Поисковые системы
    $searchEngines = [
        'google.' => ['icon' => '🔍', 'name' => 'Google Search'],
        'yandex.' => ['icon' => '🔍', 'name' => 'Yandex Search'],
        'bing.com' => ['icon' => '🔍', 'name' => 'Bing Search'],
        'yahoo.com' => ['icon' => '🔍', 'name' => 'Yahoo Search'],
        'duckduckgo.com' => ['icon' => '🦆', 'name' => 'DuckDuckGo'],
    ];
    
    // Проверка социальных сетей
    foreach ($socialMedia as $pattern => $info) {
        if (strpos($domain, $pattern) !== false) {
            return [
                'source' => "{$info['icon']} {$info['name']}",
                'domain' => $domain,
                'full_url' => $referer,
                'type' => 'social_media'
            ];
        }
    }
    
    // Проверка поисковых систем
    foreach ($searchEngines as $pattern => $info) {
        if (strpos($domain, $pattern) !== false) {
            // Получить поисковый запрос
            $query = '';
            if (isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $params);
                $query = $params['q'] ?? $params['text'] ?? $params['query'] ?? '';
            }
            
            return [
                'source' => "{$info['icon']} {$info['name']}",
                'domain' => $domain,
                'full_url' => $referer,
                'search_query' => $query,
                'type' => 'search_engine'
            ];
        }
    }
    
    // Обычный сайт
    return [
        'source' => "🌐 $domain",
        'domain' => $domain,
        'full_url' => $referer,
        'type' => 'website'
    ];
}

// ==========================================
// ПРОВЕРКА ПАРАМЕТРОВ URL
// ==========================================
function checkUrlParams() {
    $params = [];
    
    // Параметр ref
    if (isset($_GET['ref'])) {
        $params['ref'] = $_GET['ref'];
    }
    
    // UTM параметры
    $utmParams = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
    foreach ($utmParams as $param) {
        if (isset($_GET[$param])) {
            $params[$param] = $_GET[$param];
        }
    }
    
    return $params;
}

// ==========================================
// ОПРЕДЕЛЕНИЕ УСТРОЙСТВА И БРАУЗЕРА
// ==========================================
function getDeviceInfo() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Тип устройства
    $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);
    $device = $isMobile ? '📱 Мобильный' : '💻 Десктоп';
    
    // Определение браузера
    $browser = 'Неизвестно';
    if (strpos($userAgent, 'Firefox') !== false) $browser = '🦊 Firefox';
    elseif (strpos($userAgent, 'Chrome') !== false) $browser = '🌐 Chrome';
    elseif (strpos($userAgent, 'Safari') !== false) $browser = '🧭 Safari';
    elseif (strpos($userAgent, 'Edge') !== false) $browser = '🌊 Edge';
    elseif (strpos($userAgent, 'Opera') !== false) $browser = '🎭 Opera';
    
    // Операционная система
    $os = 'Неизвестно';
    if (strpos($userAgent, 'Windows') !== false) $os = '🪟 Windows';
    elseif (strpos($userAgent, 'Mac') !== false) $os = '🍎 MacOS';
    elseif (strpos($userAgent, 'Linux') !== false) $os = '🐧 Linux';
    elseif (strpos($userAgent, 'Android') !== false) $os = '🤖 Android';
    elseif (strpos($userAgent, 'iOS') !== false) $os = '📱 iOS';
    
    return [
        'device' => $device,
        'browser' => $browser,
        'os' => $os
    ];
}

// ==========================================
// ОТПРАВКА В TELEGRAM
// ==========================================
function sendToTelegram($action) {
    $refererInfo = analyzeReferer();
    $urlParams = checkUrlParams();
    $deviceInfo = getDeviceInfo();
    
    // Подготовка сообщения (русский)
    $message = "🔔 <b>НОВОЕ СОБЫТИЕ</b>\n\n";
    $message .= "📌 <b>Действие:</b> $action\n";
    $message .= "⏰ <b>Время:</b> " . date('d.m.Y H:i:s') . "\n\n";
    
    // URL параметры
    if (!empty($urlParams)) {
        $message .= "🔗 <b>URL ПАРАМЕТРЫ:</b>\n";
        foreach ($urlParams as $key => $value) {
            $message .= "├ $key: $value\n";
        }
        $message .= "\n";
    }
    
    // Информация об устройстве
    $message .= "📱 <b>УСТРОЙСТВО:</b>\n";
    $message .= "├ Тип: {$deviceInfo['device']}\n";
    $message .= "└ Браузер: {$deviceInfo['browser']}\n\n";
    
    // IP и текущая страница
    $message .= "🌍 <b>ДОПОЛНИТЕЛЬНО:</b>\n";
    $message .= "├ IP: {$_SERVER['REMOTE_ADDR']}\n";
    $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $message .= "└ Страница: $currentUrl";
    
    // Отправка в Telegram
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";
    
    $data = [
        'chat_id' => TELEGRAM_CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $options = [
        'http' => [
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = @file_get_contents($url, false, $context);
    
    return $result !== false;
}

// ==========================================
// ОБРАБОТКА AJAX ЗАПРОСОВ
// ==========================================
session_start();

// При загрузке страницы
if (isset($_POST['action']) && $_POST['action'] === 'page_load') {
    if (!isset($_SESSION['page_tracked'])) {
        $_SESSION['page_tracked'] = true;
        sendToTelegram('🏠 ОТКРЫТА СТРАНИЦА');
    }
    echo json_encode(['success' => true, 'message' => 'Tracked']);
    exit;
}

// Кнопка скачивания
if (isset($_POST['action']) && $_POST['action'] === 'download') {
    sendToTelegram('⬇️ НАЖАТА КНОПКА СКАЧАТЬ');
    echo json_encode(['success' => true, 'message' => 'Download tracked']);
    exit;
}

// Неверный запрос
http_response_code(403);
echo json_encode(['error' => 'Access denied']);
?>