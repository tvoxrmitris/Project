<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Google OAuth Login</title>
</head>
<body>
    <h2>Đăng nhập bằng Google</h2>
    <!-- Google Sign-In button -->
    <div id="g_id_onload"
         data-client_id="YOUR_CLIENT_ID.apps.googleusercontent.com"
         data-login_uri="http://localhost:3000/login"
         data-auto_prompt="false">
    </div>

    <div class="g_id_signin"
         data-type="standard"
         data-shape="rectangular"
         data-theme="outline"
         data-text="signin_with"
         data-size="large"
         data-logo_alignment="left">
    </div>

    <!-- Include Google Sign-In library -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</body>
</html>
