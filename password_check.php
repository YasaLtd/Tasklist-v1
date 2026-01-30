<?php
/**
 * Password Hash Check
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('YASA_TASKLIST', true);
require_once __DIR__ . '/config/database.php';

$wpdb = Database::getWPDB();

echo "<h1>WordPress Password Hash Analysis</h1>";

// Hae admin käyttäjä
$sql = "SELECT ID, user_login, user_pass, user_email 
        FROM pyx_users 
        WHERE ID = 1 
        LIMIT 1";
$stmt = $wpdb->query($sql);
$user = $stmt->fetch();

echo "<h2>User: " . htmlspecialchars($user['user_login']) . "</h2>";
echo "<p><strong>Email:</strong> " . htmlspecialchars($user['user_email']) . "</p>";
echo "<p><strong>Password Hash:</strong></p>";
echo "<pre style='background:#f0f0f0; padding:10px; word-break:break-all;'>" . htmlspecialchars($user['user_pass']) . "</pre>";

$hash = $user['user_pass'];

echo "<h2>Hash Analysis:</h2>";
echo "<ul>";
echo "<li><strong>Length:</strong> " . strlen($hash) . " characters</li>";
echo "<li><strong>Starts with:</strong> " . htmlspecialchars(substr($hash, 0, 4)) . "</li>";

// Detect hash type
if (strlen($hash) === 32 && ctype_xdigit($hash)) {
    echo "<li style='color:orange;'><strong>Type:</strong> Looks like plain MD5!</li>";
    $hashType = 'md5';
} elseif (substr($hash, 0, 4) === '$P$B' || substr($hash, 0, 4) === '$P$C') {
    echo "<li style='color:green;'><strong>Type:</strong> WordPress phpass (portable)</li>";
    $hashType = 'phpass';
} elseif (substr($hash, 0, 3) === '$H$') {
    echo "<li style='color:green;'><strong>Type:</strong> WordPress phpass (H variant)</li>";
    $hashType = 'phpass';
} elseif (substr($hash, 0, 4) === '$2y$' || substr($hash, 0, 4) === '$2a$') {
    echo "<li style='color:blue;'><strong>Type:</strong> bcrypt</li>";
    $hashType = 'bcrypt';
} elseif (substr($hash, 0, 7) === '$argon2') {
    echo "<li style='color:blue;'><strong>Type:</strong> Argon2</li>";
    $hashType = 'argon2';
} else {
    echo "<li style='color:red;'><strong>Type:</strong> Unknown format</li>";
    $hashType = 'unknown';
}
echo "</ul>";

// Test password
echo "<h2>Test Password</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['test_password'])) {
    $testPassword = $_POST['test_password'];
    
    echo "<p>Testing password: <code>" . htmlspecialchars($testPassword) . "</code></p>";
    
    $results = [];
    
    // Test 1: phpass
    require_once __DIR__ . '/includes/class-phpass.php';
    $hasher = new PasswordHash(8, true);
    $phpassResult = $hasher->CheckPassword($testPassword, $hash);
    $results['phpass'] = $phpassResult;
    
    // Test 2: Plain MD5
    $md5Result = (md5($testPassword) === $hash);
    $results['md5'] = $md5Result;
    
    // Test 3: MD5 with salt variations
    $md5SaltedResult = (md5($testPassword . $hash) === $hash) || (md5($hash . $testPassword) === $hash);
    $results['md5_salted'] = $md5SaltedResult;
    
    // Test 4: password_verify (PHP native)
    $nativeResult = @password_verify($testPassword, $hash);
    $results['password_verify'] = $nativeResult;
    
    // Test 5: Double MD5
    $doubleMd5Result = (md5(md5($testPassword)) === $hash);
    $results['double_md5'] = $doubleMd5Result;
    
    echo "<h3>Results:</h3>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Method</th><th>Result</th></tr>";
    foreach ($results as $method => $result) {
        $color = $result ? 'green' : 'red';
        $text = $result ? '✓ MATCH!' : '✗ No match';
        echo "<tr><td>{$method}</td><td style='color:{$color};'>{$text}</td></tr>";
    }
    echo "</table>";
    
    // Jos MD5 toimii, näytä ratkaisu
    if ($md5Result) {
        echo "<div style='background:#d4edda; border:1px solid #c3e6cb; padding:20px; margin-top:20px; border-radius:8px;'>";
        echo "<h3 style='color:#155724;'>✓ Löytyi! Käytössä on plain MD5</h3>";
        echo "<p>Sinun täytyy päivittää auth.php käyttämään MD5-tarkistusta.</p>";
        echo "</div>";
    }
    
    if ($phpassResult) {
        echo "<div style='background:#d4edda; border:1px solid #c3e6cb; padding:20px; margin-top:20px; border-radius:8px;'>";
        echo "<h3 style='color:#155724;'>✓ Löytyi! phpass toimii</h3>";
        echo "<p>Ongelma on jossain muualla auth.php:ssä.</p>";
        echo "</div>";
    }
}
?>

<form method="POST" style="margin-top:20px; padding:20px; background:#f5f5f5; border-radius:8px;">
    <p>
        <label><strong>Enter password to test:</strong></label><br>
        <input type="text" name="test_password" style="width:300px; padding:10px; font-size:16px;" 
               placeholder="Enter the actual password" value="<?php echo htmlspecialchars($_POST['test_password'] ?? ''); ?>">
    </p>
    <p>
        <button type="submit" style="padding:10px 30px; background:#1C2930; color:white; border:none; cursor:pointer; font-size:16px;">
            Test Password
        </button>
    </p>
</form>

<hr style="margin-top:30px;">

<h2>All Users Password Hash Types:</h2>
<?php
$stmt = $wpdb->query("SELECT ID, user_login, user_pass FROM pyx_users LIMIT 20");
$users = $stmt->fetchAll();

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Username</th><th>Hash Start</th><th>Hash Type</th></tr>";

foreach ($users as $u) {
    $h = $u['user_pass'];
    if (strlen($h) === 32 && ctype_xdigit($h)) {
        $type = '<span style="color:orange;">MD5</span>';
    } elseif (substr($h, 0, 3) === '$P$' || substr($h, 0, 3) === '$H$') {
        $type = '<span style="color:green;">phpass</span>';
    } elseif (substr($h, 0, 4) === '$2y$') {
        $type = '<span style="color:blue;">bcrypt</span>';
    } else {
        $type = '<span style="color:gray;">Unknown</span>';
    }
    
    echo "<tr>";
    echo "<td>" . $u['ID'] . "</td>";
    echo "<td>" . htmlspecialchars($u['user_login']) . "</td>";
    echo "<td><code>" . htmlspecialchars(substr($h, 0, 12)) . "...</code></td>";
    echo "<td>" . $type . "</td>";
    echo "</tr>";
}
echo "</table>";
?>