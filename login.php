<?php

session_start();

include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $role = $_POST["role"];

    $sql = "SELECT * FROM users
            WHERE email = ? AND role = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {

            session_regenerate_id(true);

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            if ($role == "admin") {

                header("Location: admin_dashboard.php");
                exit();

            } else {

                header("Location: employee_dashboard.php");
                exit();

            }

        } else {

            $message = "Invalid password!";

        }

    } else {

        $message = "Invalid email or role!";

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login - CTS Portal</title>

    <link rel="stylesheet" href="style.css">

    <style>

        /* =====================================================
           LOGIN PAGE - CENTER
           ===================================================== */

        .login-page {

            min-height: calc(100vh - 75px);

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 20px;
        }

        .login-page .form-container {

            width: 430px;

            max-width: 100%;
        }

    </style>

</head>

<body>

<!-- =====================================================
     NAVIGATION
     ===================================================== -->

<nav>

    <h2>🏢 CTS Portal</h2>

    <div>

        <a href="index.php">
            Home
        </a>

        <a href="register.php">
            Register
        </a>

        <a href="login.php">
            Login
        </a>

    </div>

</nav>


<!-- =====================================================
     LOGIN PAGE
     ===================================================== -->

<div class="login-page">

    <div class="form-container">

        <h1>
            🔐 Login
        </h1>


        <?php

        if ($message != "") {

            echo
            "<p class='error'>"
            . htmlspecialchars($message)
            . "</p>";

        }

        ?>


        <form method="POST" autocomplete="off">


            <!-- EMAIL -->

            <label>
                Email
            </label>

            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                autocomplete="off"
                required
            >


            <!-- PASSWORD -->

            <label>
                Password
            </label>

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                autocomplete="new-password"
                required
            >


            <!-- ROLE -->

            <label>
                Login As
            </label>

            <select
                name="role"
                required
            >

                <option value="">
                    Select Role
                </option>

                <option value="employee">
                    Employee
                </option>

                <option value="admin">
                    Admin
                </option>

            </select>


            <!-- LOGIN BUTTON -->

            <button type="submit">
                🔐 Login
            </button>

        </form>

    </div>

</div>

</body>

</html>