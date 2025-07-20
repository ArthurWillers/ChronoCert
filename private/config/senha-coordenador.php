<?php
if ($argc > 1 && isset($argv[1])) {
    $password = $argv[1];
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo $hash . "\n";
    exit;
}

if ($_POST['password'] ?? false) {
    $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    echo $hash;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Password Hash Generator</title>
</head>
<body>
    <h2>Generate Password Hash</h2>
    <form method="POST">
        <label for="password">Password:</label>
        <input type="text" name="password" id="password" required>
        <button type="submit">Generate Hash</button>
    </form>
</body>
</html>