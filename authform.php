<?php
session_start();
require 'config.php';

// REGISTER
if (isset($_POST['register'])) {
    $key = md5(uniqid($_POST['username'], true)); 
    $sql = "INSERT INTO users (username, phone, dob, password, api_key) VALUES (?, ?, ?, ?, ?)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_POST['username'], $_POST['phone'], $_POST['dob'], $_POST['password'], $key]);
        // Redirect with Success Message
        header("Location: index.php?page=login&msg=Identity Created. Please Login.");
    } catch(PDOException $e) {
        // Redirect with Error
        header("Location: index.php?page=register&error=Username or Phone already exists");
    }
    exit;
}

// LOGIN
if (isset($_POST['login'])) {
    $input = $_POST['login_input'];
    $pass = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username=? OR phone=?) AND password=?");
    $stmt->execute([$input, $input, $pass]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['api_key'] = $user['api_key'];
        // SUCCESS: Go to the Chat Interface
        header("Location: dashboard.php");
    } else {
        // FAIL: Go back to login
        header("Location: index.php?page=login&error=Invalid Credentials");
    }
    exit;
}

// FORGOT PASSWORD
if (isset($_POST['forgot'])) {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE phone=? AND dob=?");
    $stmt->execute([$_POST['phone'], $_POST['dob']]);
    $res = $stmt->fetch();
    
    if ($res) {
        // We use a clean die page for security display
        echo "<body style='background:#000; color:#00ff41; font-family:monospace; text-align:center; padding-top:100px;'>";
        echo "<h1>// RECOVERY SUCCESS //</h1>";
        echo "<p>YOUR PASSPHRASE IS:</p>";
        echo "<h2 style='font-size:2rem; border:1px solid #00ff41; display:inline-block; padding:20px;'>".$res['password']."</h2>";
        echo "<br><br><a href='index.php?page=login' style='color:#fff;'>[ RETURN TO LOGIN ]</a>";
        echo "</body>";
        exit;
    } else {
        header("Location: index.php?page=forgot&error=Verification Failed");
        exit;
    }
}
?>