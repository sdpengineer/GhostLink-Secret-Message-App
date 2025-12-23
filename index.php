<?php
require 'config.php';
$page = $_GET['page'] ?? 'home';

// --- ULTIMATE SEO LOGIC (Targets: SDP Page, Deva Prasad, GhostLink) ---
$seo = [
    'home' => [
        'title' => 'SDP Page // GhostLink Message App',
        'desc'  => 'GhostLink is a secure secret message app developed by Deva Prasad (SDP). End-to-end encryption for private communication.'
    ],
    'about' => [
        'title' => 'About Deva Prasad (SDP) // GhostLink Developer',
        'desc'  => 'Contact Deva Prasad (SDP). Phone: +91 9703771276. Email: sdp.page+site@gmail.com. Developer of the GhostLink Secure Terminal.'
    ],
    'usage' => [
        'title' => 'GhostLink Usage // Secure File Transfer',
        'desc'  => 'How to use GhostLink Message App. Learn about the Secret Key, Auto-Destruct files, and secure data purging.'
    ],
    'login' => [
        'title' => 'Login // SDP GhostLink',
        'desc'  => 'Access the GhostLink Terminal. Secure login for the secret message app by SDP.'
    ],
    'register' => [
        'title' => 'Register // GhostLink Identity',
        'desc'  => 'Create a new anonymous identity for GhostLink.'
    ]
];

// Fallback if page not defined above
$current_title = $seo[$page]['title'] ?? 'GhostLink // SDP Page';
$current_desc  = $seo[$page]['desc']  ?? 'Secure message app by Deva Prasad.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $current_title; ?></title>
    <meta name="description" content="<?php echo $current_desc; ?>">
    
    <meta name="keywords" content="SDP page, Ghostlink sdp, ghostlink message app, Deva Prasad, S Deva Prasad, 9703771276, sdp.page.gd, secret message app">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="sdpGhostlink.png" type="image/png">
    
    <?php echo isset($CSS_GLOBAL) ? $CSS_GLOBAL : ''; ?>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime&display=swap" rel="stylesheet">
    
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "SoftwareApplication",
          "name": "GhostLink Message App",
          "operatingSystem": "Web Browser",
          "applicationCategory": "CommunicationApplication",
          "url": "https://sdp.page.gd",
          "author": {
              "@type": "Person",
              "name": "S Deva Prasad (SDP)",
              "url": "https://sdp.page.gd?page=about"
          }
        },
        {
          "@type": "Person",
          "name": "S Deva Prasad",
          "alternateName": "SDP",
          "jobTitle": "Developer",
          "telephone": "+91-9703771276",
          "email": "sdp.page+site@gmail.com",
          "url": "https://sdp.page.gd"
        }
      ]
    }
    </script>

    <style>
        /* GLOBAL CYBER THEME */
        body { background-color: #050505; color: #00ff41; font-family: 'Courier Prime', monospace; margin:0; }
        
        main { display: block; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        
        /* NAVIGATION */
        nav { display: flex; justify-content: space-between; padding: 20px; border-bottom: 1px solid #003300; background: #020202; }
        .logo { font-weight: bold; font-size: 1.2rem; letter-spacing: 2px; }
        
        /* FIXED CONTRAST */
        .nav-links a { color: #aaa; text-decoration: none; margin-left: 20px; font-size: 0.9rem; transition: 0.3s; }
        .nav-links a:hover { color: #00ff41; }
        .active-link { color: #00ff41 !important; text-decoration: underline; }

        /* BUTTONS & PANELS */
        .btn { display: inline-block; background: #003300; color: #00ff41; padding: 10px 20px; border: 1px solid #00ff41; text-decoration: none; font-weight: bold; cursor: pointer; transition: 0.3s; }
        .btn:hover { background: #00ff41; color: #000; box-shadow: 0 0 10px #00ff41; }
        
        .panel { border: 1px solid #003300; padding: 30px; background: #0a0a0a; box-shadow: 0 0 20px rgba(0,50,0,0.5); }
        
        /* FORMS */
        input { width: 100%; padding: 10px; margin: 5px 0 20px; background: #111; border: 1px solid #333; color: white; box-sizing: border-box; }
        input:focus { border-color: #00ff41; outline: none; }
        label { font-size: 0.8rem; color: #aaa; display: block; margin-bottom: 5px; }

        /* MANUAL & ABOUT STYLES */
        .manual-section { margin-bottom: 30px; border-left: 2px solid #00ffff; padding-left: 15px; }
        .manual-title { color: #fff; font-weight: bold; margin-bottom: 5px; }
        .manual-text { color: #bbb; font-size: 0.9rem; line-height: 1.5; }
        .key-highlight { color: #00ffff; border: 1px solid #00ffff; padding: 2px 6px; font-size: 0.8rem; border-radius: 4px; }
        
        h3 { margin-top: 25px; color: #fff; border-bottom: 1px solid #333; padding-bottom: 5px; font-size: 1.1rem; }
        ul { color: #bbb; line-height: 1.6; padding-left: 20px; }
        li { margin-bottom: 10px; }

        /* TOAST NOTIFICATION */
        #toast {
            visibility: hidden; min-width: 250px; background-color: #0d0d0d; color: #00ff41;
            text-align: center; border: 1px solid #00ff41; border-radius: 4px; padding: 15px;
            position: fixed; z-index: 10000; left: 50%; bottom: 30px; transform: translateX(-50%);
            box-shadow: 0 0 15px rgba(0, 255, 65, 0.3); font-weight: bold; font-size: 14px;
        }
        #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
        @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
        @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }
    </style>
</head>
<body>

<div id="toast"></div>

<nav>
    <div class="logo">SDP<span style="color:white">PAGE</span></div>
    <div class="nav-links">
        <a href="?page=home" class="<?php echo $page=='home'?'active-link':''; ?>">HOME</a>
        <a href="?page=usage" class="<?php echo $page=='usage'?'active-link':''; ?>">USAGE</a>
        <a href="?page=about" class="<?php echo $page=='about'?'active-link':''; ?>">ABOUT</a>
        <a href="?page=login" style="color:#00ff41;">[ ACCESS ]</a>
    </div>
</nav>

<main class="container">
    
    <?php if($page == 'home'): ?>
    <div style="text-align:center; padding:80px 20px;">
        <img src="sdpGhostlink.png" alt="GhostLink Message App Logo by SDP" width="100" height="100" style="margin-bottom:20px; opacity:0.8;">
        
        <h1 style="font-size:3rem; margin-bottom:10px;">GHOSTLINK</h1>
        <h2 style="font-size:1rem; color:#00ffff; font-weight:bold; letter-spacing:1px; margin-bottom:20px;">
            // SECURE MESSAGE APP BY SDP
        </h2>
        
        <p style="color:#00ff41; max-width:600px; margin:0 auto 40px; font-family: monospace;">
            ENCRYPTED UPLINK ESTABLISHED<br>
            <span style="color:#888; font-size: 0.9rem;">The "SDP Page" Secure Terminal.</span>
        </p>
        <a href="?page=login" class="btn">INITIALIZE SESSION</a>
        <br><br>
        <a href="?page=usage" style="color:#aaa; font-size:0.8rem; text-decoration:none;">[ READ MANUAL ]</a>
    </div>

    <?php elseif($page == 'usage'): ?>
    <div class="panel" style="max-width:700px; margin:20px auto;">
        <h2 style="border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:20px;">// OPERATIONAL MANUAL</h2>
        <div class="manual-section">
            <div class="manual-title">1. SECURE FILE TRANSFER</div>
            <div class="manual-text">GhostLink supports PDF, DOC, ZIP, VIDEO, AUDIO, IMAGES with <b>5-minute Auto-Destruct</b>.</div>
        </div>
        <div class="manual-section">
            <div class="manual-title">2. THE SECRET KEY</div>
            <div class="manual-text">Enter the same Secret Key as your partner to decrypt messages.</div>
        </div>
        <div style="text-align:center; margin-top:30px;">
            <a href="?page=login" class="btn" style="width:100%">START MESSAGING</a>
        </div>
    </div>
    
    <?php elseif($page == 'about'): ?>
    <div class="panel" style="max-width:800px; margin:20px auto;">
        <h2 style="border-bottom: 2px solid #00ffff; color:#00ffff; padding-bottom: 10px; margin-bottom: 20px;">// ABOUT SDP GHOSTLINK</h2>
        
        <h3>/// DEVELOPER PROFILE</h3>
        <p style="color:#bbb;">
            GhostLink is a secret message app developed by <b>S Deva Prasad (SDP)</b>.
            <br><br>
            It is designed as a secure communication terminal on the <b>SDP Page</b> network.
        </p>
        
        <h3>/// CONTACT INFO</h3>
        <ul style="list-style:none; padding:0;">
            <li><strong>Developer:</strong> Deva Prasad</li>
            <li><strong>Email:</strong> sdp.page+site@gmail.com</li>
            <li><strong>Phone:</strong> +91 9703771276</li>
        </ul>

        <h3>/// SECURITY</h3>
        <p style="color:#bbb;">Features End-to-End Encryption and Zero-Knowledge architecture.</p>
    </div>
    
    <?php elseif($page == 'login'): ?>
    <div class="panel" style="max-width:400px; margin:50px auto;">
        <h2 style="text-align:center; margin-bottom:20px;">// SYSTEM ACCESS</h2>
        <form action="authform.php" method="POST">
            <label>IDENTITY</label>
            <input type="text" name="login_input" placeholder="Username or Phone" required>
            <label>PASSPHRASE</label>
            <input type="password" name="password" required>
            <button type="submit" name="login" class="btn" style="width:100%">AUTHENTICATE</button>
        </form>
        <div style="text-align:center; margin-top:20px;">
            <a href="?page=register" style="font-size:0.8rem; color:#aaa;">[ CREATE NEW IDENTITY ]</a><br>
            <a href="?page=forgot" style="font-size:0.8rem; color:#888;">[ LOST CREDENTIALS ]</a>
        </div>
    </div>

    <?php elseif($page == 'register'): ?>
    <div class="panel" style="max-width:400px; margin:50px auto;">
        <h2 style="text-align:center; margin-bottom:20px;">// NEW IDENTITY</h2>
        <form action="authform.php" method="POST">
            <label>CONTACT UPLINK</label>
            <input type="number" name="phone" placeholder="Phone Number" required>
            <label>ALIAS</label>
            <input type="text" name="username" placeholder="Username" required>
            <label>VERIFICATION DATE</label>
            <input type="text" name="dob" placeholder="DOB (DDMMYYYY)" required>
            <label>SECRET KEY</label>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="register" class="btn" style="width:100%">INITIALIZE</button>
        </form>
        <div style="text-align:center; margin-top:20px;">
            <a href="?page=login" style="font-size:0.8rem; color:#aaa;">[ RETURN TO LOGIN ]</a>
        </div>
    </div>

    <?php elseif($page == 'forgot'): ?>
    <div class="panel" style="max-width:400px; margin:50px auto;">
        <h2 style="text-align:center; margin-bottom:20px;">// RECOVERY</h2>
        <form action="authform.php" method="POST">
            <label>VERIFY PHONE</label>
            <input type="number" name="phone" placeholder="Phone Number" required>
            <label>VERIFY DOB</label>
            <input type="text" name="dob" placeholder="DOB (DDMMYYYY)" required>
            <button type="submit" name="forgot" class="btn" style="width:100%">RECOVER PASSWORD</button>
        </form>
        <div style="text-align:center; margin-top:20px;">
            <a href="?page=login" style="font-size:0.8rem; color:#aaa;">[ CANCEL ]</a>
        </div>
    </div>
    <?php endif; ?>

</main>

<footer style="text-align:center; margin-top:50px; color:#555; border-top:1px solid #111; padding:20px; font-size: 0.8rem;">
    GHOSTLINK MESSAGE APP v2.2<br>
    DEVELOPED BY: <span style="color:#00ff41;">S Deva Prasad (SDP)</span><br><br>
    EMAIL: <a href="mailto:sdp.page+site@gmail.com" style="color:#00ff41; text-decoration:none;">sdp.page+site@gmail.com</a> <br>
    PHONE: <a href="tel:+919703771276" style="color:#00ff41; text-decoration:none;">+91 9703771276</a>
</footer>

<script>
function showToast(text) {
    var x = document.getElementById("toast");
    x.innerText = text;
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 4000);
    window.history.replaceState({}, document.title, window.location.pathname + window.location.search.split('&')[0]);
}
window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('error')) { showToast("ERROR: " + urlParams.get('error')); }
    if(urlParams.has('msg')) { showToast(urlParams.get('msg')); }
};
</script>

</body>
</html>