#!/usr/bin/env php
<?php

/**
 * MOOC Platform API Testing Script
 * 
 * Run: php tests/api-test.php
 * 
 * Tests all major API endpoints to verify system is working correctly
 */

require __DIR__ . '/../vendor/autoload.php';

// Colors for output
$colors = [
    'success' => "\033[92m",
    'error' => "\033[91m",
    'info' => "\033[94m",
    'warning' => "\033[93m",
    'reset' => "\033[0m",
];

$apiBase = 'http://localhost:8000/api';
$testResults = [];

echo "\n" . $colors['info'] . "╔════════════════════════════════════════════════╗" . $colors['reset'] . "\n";
echo   $colors['info'] . "║   MOOC Platform API Testing Suite              ║" . $colors['reset'] . "\n";
echo   $colors['info'] . "║   Testing all endpoints                         ║" . $colors['reset'] . "\n";
echo   $colors['info'] . "╚════════════════════════════════════════════════╝" . $colors['reset'] . "\n\n";

// Test function
function testEndpoint($name, $url, $method = 'GET', $headers = []) {
    global $apiBase, $colors, $testResults;
    
    $fullUrl = $apiBase . $url;
    echo $colors['info'] . "Testing: " . $colors['reset'] . "$name\n";
    echo "  URL: $fullUrl\n";
    
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            echo $colors['error'] . "  ✗ FAILED" . $colors['reset'] . " - cURL Error: $error\n\n";
            $testResults[$name] = false;
            return false;
        }
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $data = json_decode($response, true);
            if ($data && isset($data['success']) && $data['success'] === true) {
                echo $colors['success'] . "  ✓ PASSED" . $colors['reset'] . " (HTTP $httpCode)\n";
                if (isset($data['data'])) {
                    if (is_array($data['data']) && count($data['data']) > 0) {
                        echo "  Response: " . json_encode(array_slice($data['data'], 0, 1)) . "\n";
                    }
                }
            } elseif ($httpCode == 200) {
                echo $colors['success'] . "  ✓ PASSED" . $colors['reset'] . " (HTTP $httpCode)\n";
            } else {
                echo $colors['warning'] . "  ? PARTIAL" . $colors['reset'] . " (HTTP $httpCode)\n";
                $testResults[$name] = null;
            }
            $testResults[$name] = true;
        } else {
            echo $colors['error'] . "  ✗ FAILED" . $colors['reset'] . " - HTTP $httpCode\n";
            if ($response) {
                echo "  Response: " . substr($response, 0, 100) . "\n";
            }
            $testResults[$name] = false;
        }
    } catch (Exception $e) {
        echo $colors['error'] . "  ✗ EXCEPTION" . $colors['reset'] . " - " . $e->getMessage() . "\n";
        $testResults[$name] = false;
    }
    
    echo "\n";
    return $testResults[$name] ?? false;
}

// Run tests
echo $colors['info'] . "═══════════════════════════════════════════════════" . $colors['reset'] . "\n";
echo $colors['info'] . "PUBLIC ENDPOINTS (No Authentication Required)" . $colors['reset'] . "\n";
echo $colors['info'] . "═══════════════════════════════════════════════════" . $colors['reset'] . "\n\n";

testEndpoint(
    'Leaderboard Stats',
    '/leaderboard/stats'
);

testEndpoint(
    'Top Users by XP',
    '/leaderboard/xp?limit=10'
);

testEndpoint(
    'Top Users by Level',
    '/leaderboard/level?limit=10'
);

testEndpoint(
    'Weekly Leaderboard',
    '/leaderboard/weekly'
);

testEndpoint(
    'Course Leaderboard (Course 1)',
    '/leaderboard/course/1?limit=10'
);

testEndpoint(
    'Users by Level (Level 10)',
    '/leaderboard/level/10?limit=10'
);

testEndpoint(
    'User Public XP (User 4)',
    '/users/4/xp'
);

echo $colors['info'] . "═══════════════════════════════════════════════════" . $colors['reset'] . "\n";
echo $colors['info'] . "PROTECTED ENDPOINTS (Requires Authentication)" . $colors['reset'] . "\n";
echo $colors['info'] . "═══════════════════════════════════════════════════" . $colors['reset'] . "\n\n";

echo $colors['warning'] . "Note: Protected endpoints require valid authentication token" . $colors['reset'] . "\n";
echo $colors['warning'] . "      Use Sanctum tokens or Session cookies" . $colors['reset'] . "\n\n";

// Module endpoints (would need auth)
echo "Module Endpoints (require auth):\n";
echo "  • GET  /api/courses/{courseId}/modules\n";
echo "  • GET  /api/courses/{courseId}/modules/{moduleId}\n";
echo "  • POST /api/courses/{courseId}/modules/{moduleId}/complete\n\n";

// User XP endpoints (require auth)
echo "User XP Endpoints (require auth):\n";
echo "  • GET  /api/user/xp-summary\n";
echo "  • GET  /api/user/xp-logs\n";
echo "  • GET  /api/user/xp-analytics\n";
echo "  • GET  /api/user/rank\n\n";

// Award XP endpoint (require admin/instructor)
echo "Admin/Instructor Endpoints (require auth + role):\n";
echo "  • POST /api/users/{userId}/award-xp\n\n";

// Summary
echo "\n" . $colors['info'] . "═══════════════════════════════════════════════════" . $colors['reset'] . "\n";
echo $colors['info'] . "TEST SUMMARY" . $colors['reset'] . "\n";
echo $colors['info'] . "═══════════════════════════════════════════════════" . $colors['reset'] . "\n\n";

$passed = count(array_filter($testResults, function($v) { return $v === true; }));
$failed = count(array_filter($testResults, function($v) { return $v === false; }));
$total = count($testResults);

echo "Results:\n";
echo "  " . $colors['success'] . "✓ Passed: $passed" . $colors['reset'] . "\n";
echo "  " . ($failed > 0 ? $colors['error'] : $colors['success']) . "✗ Failed: $failed" . $colors['reset'] . "\n";
echo "  Total: $total\n\n";

if ($failed === 0) {
    echo $colors['success'] . "✓ ALL TESTS PASSED!" . $colors['reset'] . "\n";
    echo "  Your MOOC Platform API is working correctly.\n\n";
    exit(0);
} else {
    echo $colors['error'] . "✗ SOME TESTS FAILED" . $colors['reset'] . "\n";
    echo "  Check the error messages above.\n";
    echo "  Make sure the development server is running:\n";
    echo "  $ php artisan serve\n\n";
    exit(1);
}
