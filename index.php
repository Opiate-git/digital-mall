<?php require_once __DIR__ . '/helpers.php'; start_secure_session(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Mall - Portal</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

    <div id="mainPage" class="container">
        <h2>Digital Mall Login</h2>
        <?php
        $messages = [
            'login'  => 'Wrong email or password.',
            'signup' => 'Please fill all fields correctly (password: min. 6 characters).',
            'exists' => 'This email is already registered.',
            'shop'   => 'This shop name is already taken.',
        ];
        $err = $_GET['error'] ?? '';
        if (isset($messages[$err])) {
            echo '<p style="color:#ff6b6b; font-weight:bold;">' . $messages[$err] . '</p>';
        } elseif (isset($_GET['pending'])) {
            echo '<p style="color:#ffc107; font-weight:bold;">Your manager account is waiting for admin approval.</p>';
        } elseif (isset($_GET['signup'])) {
            echo '<p style="color:#28a745; font-weight:bold;">Account created. You can log in now.</p>';
        } else {
            echo '<br><br><br>';
        }
        ?>
        <form action="login.php" method="POST"><?php echo csrf_field(); ?>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <div class="btn-group">
                <button type="submit">Login</button>
                <button type="button" onclick="showPage('signUpPage')">Sign Up</button>
                <button type="button" onclick="showPage('managerPage')">Register (Manager)</button>
            </div>
            <div class="demo-box">
                <strong>Try the demo</strong>
                <div class="btn-group">
                    <button type="button" onclick="demoLogin('customer@demo.com')">Demo Customer</button>
                    <button type="button" onclick="demoLogin('manager@demo.com')">Demo Shop Manager</button>
                </div>
                <small>Password: demo1234 &middot; Some actions are disabled on demo accounts.<br>This is a demo project: no real orders, payments or deliveries.</small>
            </div>
        </form>
    </div>

    <div id="signUpPage" class="container hidden">
        <h2>Customer Sign Up</h2>
        <form action="signup.php" method="POST"><?php echo csrf_field(); ?>
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="address" placeholder="Address">
            <div class="btn-group">
                <button type="submit">Create Account</button>
                <span class="nav-link" onclick="showPage('mainPage')" style="cursor:pointer;">← Back to Login</span>
            </div>
        </form>
    </div>

    <div id="managerPage" class="container hidden">
        <div style="background: var(--accent); color: white; padding: 10px; margin-bottom: 20px; border-radius: 4px; font-weight: bold;">
            MANAGER REGISTRATION
        </div>
        <form action="manager_signup.php" method="POST"><?php echo csrf_field(); ?>
            <input type="text" name="fullname" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="address" placeholder="Shop Name" required>
            <div class="btn-group">
                <button type="submit">Register</button>
                <span class="nav-link" onclick="showPage('mainPage')" style="cursor:pointer;">← Back to Login</span>
    </div>
</form>
    </div>

    <script>
        function demoLogin(email) {
            const f = document.querySelector("#mainPage form");
            f.email.value = email;
            f.password.value = "demo1234";
            f.submit();
        }

        function showPage(pageId) {
            document.getElementById('mainPage').classList.add('hidden');
            document.getElementById('signUpPage').classList.add('hidden');
            document.getElementById('managerPage').classList.add('hidden');
            
            document.getElementById(pageId).classList.remove('hidden');
        }
    </script>
</body>
</html>