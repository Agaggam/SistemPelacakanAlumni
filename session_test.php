<?php
// session_test.php
session_start();
if (!isset($_SESSION['test_count'])) {
    $_SESSION['test_count'] = 0;
}
$_SESSION['test_count']++;

header('Content-Type: text/plain');
echo "Session Test:\n";
echo "Count: " . $_SESSION['test_count'] . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Cookie: " . ($_COOKIE[session_name()] ?? 'NOT SET') . "\n";
echo "PHP Version: " . phpversion() . "\n";
?>
