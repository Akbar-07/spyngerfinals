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
define('TELEGRAM_CHAT_ID', '-1003272121162');

// ==========================================
// АНАЛИЗ REFERER
// ==========================================
function analyzeReferer() {
    // СНАЧАЛА ПРОВЕРЯЕМ REF ПАРАМЕТР (ИЗ POST ИЛИ GET)
    $refParam = '';
    if (isset($_POST['ref']) && !empty($_POST['ref'])) {
        $refParam = strtolower(trim($_POST['ref']));
    } elseif (isset($_GET['ref']) && !empty($_GET['ref'])) {
        $refParam = strtolower(trim($_GET['ref']));
    }
    
    if (!empty($refParam)) {
        // Маппинг ref параметров на источники
        $refMapping = [
            'instagram' => ['icon' => '📸', 'name' => 'Instagram'],
            'ig' => ['icon' => '📸', 'name' => 'Instagram'],
            'facebook' => ['icon' => '📘', 'name' => 'Facebook'],
            'fb' => ['icon' => '📘', 'name' => 'Facebook'],
            'telegram' => ['icon' => '💬', 'name' => 'Telegram'],
            'tg' => ['icon' => '💬', 'name' => 'Telegram'],
            'whatsapp' => ['icon' => '📱', 'name' => 'WhatsApp'],
            'wa' => ['icon' => '📱', 'name' => 'WhatsApp'],
            'twitter' => ['icon' => '🐦', 'name' => 'Twitter'],
            'x' => ['icon' => '🐦', 'name' => 'X (Twitter)'],
            'tiktok' => ['icon' => '🎵', 'name' => 'TikTok'],
            'vk' => ['icon' => '🔵', 'name' => 'VKontakte'],
            'linkedin' => ['icon' => '💼', 'name' => 'LinkedIn'],
            'youtube' => ['icon' => '🎥', 'name' => 'YouTube'],
            'pinterest' => ['icon' => '📌', 'name' => 'Pinterest'],
            'reddit' => ['icon' => '🤖', 'name' => 'Reddit'],
            'google' => ['icon' => '🔍', 'name' => 'Google'],
            'yandex' => ['icon' => '🔍', 'name' => 'Yandex'],
        ];
        
        if (isset($refMapping[$refParam])) {
            $info = $refMapping[$refParam];
            return [
                'source' => "{$info['icon']} {$info['name']}",
                'domain' => $refParam,
                'full_url' => "Переход по ссылке с ref=$refParam",
                'type' => 'ref_param'
            ];
        } else {
            return [
                'source' => "🔗 " . ucfirst($refParam),
                'domain' => $refParam,
                'full_url' => "Переход по ссылке с ref=$refParam",
                'type' => 'ref_param_custom'
            ];
        }
    }
    
    // ЕСЛИ REF НЕТ - АНАЛИЗИРУЕМ HTTP_REFERER
    $referer = isset($_SERVER['HTTP_REFERER']) ? trim($_SERVER['HTTP_REFERER']) : '';
    
    if (empty($referer)) {
        return [
            'source' => '🔗 Прямой переход',
            'domain' => 'Прямой заход',
            'full_url' => 'Ссылка введена вручную или из закладок',
            'type' => 'direct'
        ];
    }
    
    // Получить домен из URL
    $parsedUrl = parse_url($referer);
    $domain = isset($parsedUrl['host']) ? $parsedUrl['host'] : 'Неизвестный домен';
    
    // Проверка на собственный домен
    $currentDomain = $_SERVER['HTTP_HOST'] ?? '';
    if ($domain === $currentDomain || strpos($domain, 'ch810755.tw1.ru') !== false || strpos($domain, 'localhost') !== false) {
        return [
            'source' => '🏠 Внутренний переход',
            'domain' => $domain,
            'full_url' => $referer,
            'type' => 'internal'
        ];
    }
    
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
        'youtu.be' => ['icon' => '🎥', 'name' => 'YouTube'],
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
        'source' => "🌐 " . $domain,
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
    
    if (isset($_GET['ref'])) {
        $params['ref'] = $_GET['ref'];
    }
    
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
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $isMobile = preg_match('/(android|iphone|ipad|mobile)/i', $userAgent);
    $device = $isMobile ? '📱 Мобильный' : '💻 Десктоп';
    
    $browser = 'Неизвестно';
    if (strpos($userAgent, 'Firefox') !== false) $browser = '🦊 Firefox';
    elseif (strpos($userAgent, 'Edg') !== false) $browser = '🌊 Edge';
    elseif (strpos($userAgent, 'Chrome') !== false) $browser = '🌐 Chrome';
    elseif (strpos($userAgent, 'Safari') !== false) $browser = '🧭 Safari';
    elseif (strpos($userAgent, 'Opera') !== false) $browser = '🎭 Opera';
    
    $os = 'Неизвестно';
    if (strpos($userAgent, 'Windows') !== false) $os = '🪟 Windows';
    elseif (strpos($userAgent, 'Mac') !== false) $os = '🍎 MacOS';
    elseif (strpos($userAgent, 'Linux') !== false) $os = '🐧 Linux';
    elseif (strpos($userAgent, 'Android') !== false) $os = '🤖 Android';
    elseif (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) $os = '📱 iOS';
    
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
    
    // Получить текущую страницу
    $currentPage = isset($_POST['page']) ? $_POST['page'] : (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI']) ? 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] : 'Неизвестная страница');
    
    // JavaScript detection
    $detectedSource = isset($_POST['detected_source']) ? $_POST['detected_source'] : null;
    
    if ($detectedSource && $detectedSource !== 'unknown') {
        $sourceMapping = [
            'instagram_app' => ['icon' => '📸', 'name' => 'Instagram'],
            'facebook_app' => ['icon' => '📘', 'name' => 'Facebook'],
            'tiktok_app' => ['icon' => '🎵', 'name' => 'TikTok'],
            'telegram_app' => ['icon' => '💬', 'name' => 'Telegram'],
        ];
        
        if (isset($sourceMapping[$detectedSource])) {
            $refererInfo['source'] = "{$sourceMapping[$detectedSource]['icon']} {$sourceMapping[$detectedSource]['name']}";
            $refererInfo['type'] = 'detected_app';
        }
    }
    
    // Подготовка сообщения
    $message = "🔔 <b>НОВОЕ СОБЫТИЕ</b>\n\n";
    $message .= "📌 <b>Действие:</b> $action\n";
    $message .= "⏰ <b>Время:</b> " . date('d.m.Y') . "\n\n";
    
    $isPageLoad = (strpos($action, 'ОТКРЫТА СТРАНИЦА') !== false);
    
    if ($isPageLoad) {
        // ДЛЯ ЗАГРУЗКИ СТРАНИЦЫ
        $message .= "🔗 <b>ОТКУДА ПРИШЕЛ:</b>\n";
        $message .= "🌐 URL: $currentPage\n";
        $message .= "🎯 Источник: {$refererInfo['source']}\n\n";
    } else {
        // ДЛЯ КНОПКИ СКАЧАТЬ
        $message .= "🌐 <b>URL:</b> $currentPage\n\n";
    }
    
    // URL параметры (только для загрузки страницы)
    if ($isPageLoad && !empty($urlParams)) {
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
    
    $message .= "🌍 <b>IP АДРЕС:</b> {$_SERVER['REMOTE_ADDR']}";
    
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
            'content' => http_build_query($data),
            'timeout' => 10
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

if (isset($_POST['action']) && $_POST['action'] === 'page_load') {
    if (!isset($_SESSION['page_tracked'])) {
        $_SESSION['page_tracked'] = true;
        sendToTelegram('🏠 ОТКРЫТА СТРАНИЦА');
    }
    echo json_encode(['success' => true, 'message' => 'Page load tracked']);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'download') {
    sendToTelegram('⬇️ НАЖАТА КНОПКА СКАЧАТЬ');
    echo json_encode(['success' => true, 'message' => 'Download tracked']);
    exit;
}

// ОБРАБОТКА AJAX ЗАПРОСОВ da mavjud koddan keyin qo'shing:

if (isset($_POST['action']) && $_POST['action'] === 'continue') {
    sendToTelegram('✅ НАЖАТА КНОПКА CONTINUE');
    echo json_encode(['success' => true, 'message' => 'Continue tracked']);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'cancel') {
    sendToTelegram('❌ НАЖАТА КНОПКА CANCEL');
    echo json_encode(['success' => true, 'message' => 'Cancel tracked']);
    exit;
}

// Неверный запрос
http_response_code(400);
echo json_encode(['error' => 'Invalid request']);
?>