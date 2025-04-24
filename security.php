<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Browser UA check
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$allowed_browsers = ['Chrome', 'Firefox', 'Safari', 'Edge', 'Opera', 'MSIE', 'Trident', 'Mozilla'];
$is_browser = false;
foreach ($allowed_browsers as $b) {
    if (stripos($user_agent, $b) !== false) {
        $is_browser = true;
        break;
    }
}

// 2. Referer check (optional, can be relaxed if needed)
$referer_ok = true;
if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    $allowed_sources = ['awi.cuhk.edu.cn', '10.26.4.101'];
    $referer_ok = false;
    foreach ($allowed_sources as $src) {
        if (strpos($referer, $src) !== false) {
            $referer_ok = true;
            break;
        }
    }
}

// 3. Check if JS cookie is set
$js_cookie_set = isset($_COOKIE['js_check']) && $_COOKIE['js_check'] === '1';

// === Non-browser access, immediate 403 ===
if (!$is_browser) {
    http_response_code(403);
    echo "Access denied. Please use a real web browser.";
    exit;
}

// === Browser but cookie not set (first visit) → Set cookie, return 412 + JS reload ===
if (!$js_cookie_set) {
    header("X-Protect: JS-Check");
    http_response_code(412);  // Precondition Failed
    echo '<script>document.cookie="js_check=1; path=/"; window.location.reload();</script>';
    exit;
}

// === Invalid referer, also denied ===
if (!$referer_ok) {
    http_response_code(403);
    echo "Access denied. Invalid referer.";
    exit;
}
?>
