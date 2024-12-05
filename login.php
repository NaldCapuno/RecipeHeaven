<?php
    session_start();
    include "conn.php";

    $error_message = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (!empty($username) && !empty($password)) {
            $query = "SELECT * FROM recipeheaven.users WHERE username = '$username'";
            $result = $conn->query($query);

            if ($result == false) {
                die("Query Error: " . $conn->error);
            }

            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();

                if ($password == $user['password']) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    echo "<script type='text/javascript'>
                            alert('Successfully logged in!');
                            window.location.href = 'index.php';
                          </script>";
                    exit;
                } else {
                    $error_message = "Invalid username or password.";
                }
            } else {
                $error_message = "User not found.";
            }
        } else {
            $error_message = "Please fill in all fields.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <title>Login | RecipeHeaven</title>
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        <p class="register-link">Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</body>
</html>
