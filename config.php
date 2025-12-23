<?php
// CONFIGURATION
define('DB_HOST', 'Your_DB_Host');
define('DB_USER', 'Your_UserNmae');
define('DB_PASS', 'DB_Passwd');
define('DB_NAME', 'DB_Name'); // CHECK THIS in your Panel!

// GLOBAL STYLES (Hacker Theme)
$CSS_GLOBAL = "
<link href='https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap' rel='stylesheet'>
<style>
    :root { --neon: #00ff41; --bg: #050505; --panel: rgba(10, 15, 10, 0.95); }
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Share Tech Mono', monospace; }
    body {
        background-color: var(--bg);
        background-image: linear-gradient(0deg, transparent 24%, rgba(0, 255, 65, .03) 25%, rgba(0, 255, 65, .03) 26%, transparent 27%, transparent 74%, rgba(0, 255, 65, .03) 75%, rgba(0, 255, 65, .03) 76%, transparent 77%, transparent), linear-gradient(90deg, transparent 24%, rgba(0, 255, 65, .03) 25%, rgba(0, 255, 65, .03) 26%, transparent 27%, transparent 74%, rgba(0, 255, 65, .03) 75%, rgba(0, 255, 65, .03) 76%, transparent 77%, transparent);
        background-size: 50px 50px;
        color: var(--neon);
        min-height: 100vh;
    }
    /* Black Overlay */
    body::before { content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: -1; }
    
    a { text-decoration: none; color: inherit; transition: 0.3s; }
    .btn { background: rgba(0, 255, 65, 0.1); border: 1px solid var(--neon); color: var(--neon); padding: 10px 20px; cursor: pointer; text-transform: uppercase; }
    .btn:hover { background: var(--neon); color: black; box-shadow: 0 0 15px var(--neon); }
    
    nav { display: flex; justify-content: space-between; padding: 20px; border-bottom: 2px solid #111; background: var(--panel); }
    .logo { font-size: 1.5rem; border-left: 4px solid var(--neon); padding-left: 10px; }
    .nav-links a { margin-left: 20px; }
    .nav-links a:hover { color: white; text-shadow: 0 0 5px var(--neon); }
    
    .container { max-width: 1000px; margin: 40px auto; padding: 20px; }
    .panel { background: var(--panel); border: 1px solid #333; padding: 30px; border-radius: 5px; box-shadow: 0 0 20px rgba(0,255,65,0.05); }
    input { width: 100%; padding: 12px; background: #000; border: 1px solid #333; color: var(--neon); margin-bottom: 15px; outline: none; }
    input:focus { border-color: var(--neon); }
</style>
";

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("<h1 style='color:red; text-align:center;'>DATABASE CONNECTION FAILED</h1><p style='text-align:center;'>Check config.php credentials.<br>Error: ".$e->getMessage()."</p>");
}
?>