<?php
/**
 * Security Headers for Pi-Star Dashboard
 * Provides XSS protection while maintaining embeddability for certain components
 * Handles both HTTP and HTTPS deployments
 */

/**
 * Detect if the current request is over HTTPS
 * @return bool True if HTTPS, false if HTTP
 */
function isHttps() {
    // Check standard HTTPS indicators
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
        return true;
    }
    // Check for proxy/load balancer headers
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        return true;
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
        return true;
    }
    return false;
}

/**
 * Set full security headers for non-embeddable pages
 * Use for: Admin pages, configuration editors, main entry points
 */
function setSecurityHeaders() {
    // Only set headers if they haven't been sent yet
    if (!headers_sent()) {
        $isHttps = isHttps();

        header("X-Frame-Options: SAMEORIGIN");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

        // Build CSP based on protocol
        // Allow external images via both http: and https: since we can't control external links
        $imgSrc = $isHttps ? "'self' data: https:" : "'self' data: http: https:";

        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src {$imgSrc}; " .
               "connect-src 'self'; " .
               "frame-ancestors 'self'";

        header("Content-Security-Policy: " . $csp);

        // Only add HSTS if served over HTTPS
        if ($isHttps) {
            // HSTS: Force HTTPS for 1 year, but don't include subdomains (might be on local network)
            header("Strict-Transport-Security: max-age=31536000");
        }
    }
}

/**
 * Set embeddable security headers for display components
 * Use for: Status displays, last heard lists, info panels meant to be embeddable
 */
function setEmbeddableSecurityHeaders() {
    // Only set headers if they haven't been sent yet
    if (!headers_sent()) {
        $isHttps = isHttps();

        // Note: X-Frame-Options omitted to allow embedding
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

        // Build CSP based on protocol
        $imgSrc = $isHttps ? "'self' data: https:" : "'self' data: http: https:";

        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src {$imgSrc}; " .
               "connect-src 'self'";

        header("Content-Security-Policy: " . $csp);

        // Only add HSTS if served over HTTPS
        if ($isHttps) {
            header("Strict-Transport-Security: max-age=31536000");
        }
    }
}

/**
 * Set security headers for pages that embed content from different ports on same host
 *
 * This allows iframes from the same hostname but different ports
 */
function setSecurityHeadersAllowDifferentPorts() {
    // Only set headers if they haven't been sent yet
    if (!headers_sent()) {
        $isHttps = isHttps();

        header("X-Frame-Options: SAMEORIGIN");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

        // Get current hostname for frame-src
        $hostname = $_SERVER['HTTP_HOST'];
        // Remove port if present to get just the hostname
        $hostnameOnly = preg_replace('/:\d+$/', '', $hostname);

        // Build CSP that allows frames from same hostname on any port
        $imgSrc = $isHttps ? "'self' data: https:" : "'self' data: http: https:";
        $protocol = $isHttps ? "https:" : "http:";

        // Allow frames from same hostname with any port (for shellinabox, etc.)
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src {$imgSrc}; " .
               "connect-src 'self'; " .
               "frame-src 'self' {$protocol}//{$hostnameOnly}:*; " .
               "frame-ancestors 'self'";

        header("Content-Security-Policy: " . $csp);

        // Only add HSTS if served over HTTPS
        if ($isHttps) {
            header("Strict-Transport-Security: max-age=31536000");
        }
    }
}
?>
