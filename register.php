<?php

include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $phone = $_POST["phone"];
    $department = $_POST["department"];
    $role = $_POST["role"];

    // Check whether email already exists
    $check_sql = "SELECT id FROM users WHERE email = ?";

    $check_stmt = $conn->prepare($check_sql);

    $check_stmt->bind_param("s", $email);

    $check_stmt->execute();

    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {

        $message = "⚠️ This email is already registered!";

    } else {

        // Hash password
        $hashed_password = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        // Insert user
        $sql = "INSERT INTO users
                (name, email, password, phone, department, role)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssssss",
            $name,
            $email,
            $hashed_password,
            $phone,
            $department,
            $role
        );

        if ($stmt->execute()) {

            $message = "✅ Registration successful!";

        } else {

            $message = "❌ Registration failed!";

        }

    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>CTS Portal - Registration</title>

    <style>

        /* ================================
           BASIC RESET
        ================================= */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        /* ================================
           BODY
        ================================= */

        body {

            font-family: Arial, Helvetica, sans-serif;

            min-height: 100vh;

            color: white;

            background:
                radial-gradient(
                    circle at 10% 20%,
                    #16459c,
                    transparent 35%
                ),

                radial-gradient(
                    circle at 90% 80%,
                    #57249e,
                    transparent 35%
                ),

                linear-gradient(
                    135deg,
                    #050a20,
                    #0b1638,
                    #080c28
                );

            background-attachment: fixed;
        }


        /* ================================
           NAVBAR
        ================================= */

        nav {

            height: 75px;

            padding: 0 6%;

            display: flex;

            align-items: center;

            justify-content: space-between;

            background:
                rgba(5, 12, 40, 0.90);

            border-bottom:
                1px solid
                rgba(100, 170, 255, 0.25);

            backdrop-filter: blur(15px);

            position: sticky;

            top: 0;

            z-index: 1000;
        }


        nav h2 {

            font-size: 27px;

            letter-spacing: 0.5px;
        }


        nav div {

            display: flex;

            gap: 28px;
        }


        nav a {

            color: white;

            text-decoration: none;

            font-weight: bold;

            font-size: 15px;

            transition: 0.3s;
        }


        nav a:hover {

            color: #61b8ff;

            transform: translateY(-2px);
        }


        /* ================================
           MAIN REGISTRATION AREA
        ================================= */

        .register-page {

            width: 92%;

            max-width: 1250px;

            min-height:
                calc(100vh - 75px);

            margin: auto;

            padding: 45px 0;

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 45px;
        }


        /* ================================
           IMAGE SECTION
        ================================= */

        .register-image {

            width: 55%;

            height: 650px;

            position: relative;

            overflow: hidden;

            border-radius: 28px;

            border:
                1px solid
                rgba(100, 170, 255, 0.3);

            box-shadow:
                0 25px 70px
                rgba(0, 0, 0, 0.55);
        }


        .register-image img {

            width: 100%;

            height: 100%;

            object-fit: cover;

            display: block;

            transition: 0.6s;
        }


        .register-image:hover img {

            transform: scale(1.05);
        }


        /* ================================
           IMAGE DARK OVERLAY
        ================================= */

        .image-overlay {

            position: absolute;

            left: 0;

            right: 0;

            bottom: 0;

            padding:
                80px 35px 35px;

            background:
                linear-gradient(
                    transparent,
                    rgba(0, 0, 0, 0.95)
                );
        }


        .image-overlay h1 {

            font-size: 35px;

            margin-bottom: 12px;
        }


        .image-overlay p {

            font-size: 17px;

            line-height: 1.6;

            color: #d3def3;
        }


        /* ================================
           FORM CONTAINER
        ================================= */

        .form-container {

            width: 430px;

            padding: 38px;

            border-radius: 25px;

            background:
                rgba(25, 35, 65, 0.92);

            border:
                1px solid
                rgba(110, 170, 255, 0.3);

            backdrop-filter: blur(20px);

            box-shadow:
                0 25px 60px
                rgba(0, 0, 0, 0.45);

            animation:
                formAppear 0.7s ease;
        }


        .form-container h1 {

            text-align: center;

            font-size: 30px;

            margin-bottom: 25px;
        }


        /* ================================
           LABELS
        ================================= */

        .form-container label {

            display: block;

            margin-top: 15px;

            margin-bottom: 7px;

            font-size: 14px;

            font-weight: bold;

            color: #d5def2;
        }


        /* ================================
           INPUTS
        ================================= */

        .form-container input,
        .form-container select {

            width: 100%;

            padding: 13px 15px;

            border-radius: 11px;

            border:
                1px solid
                rgba(120, 160, 230, 0.3);

            background: #101a35;

            color: white;

            outline: none;

            font-size: 14px;

            transition: 0.3s;
        }


        .form-container input::placeholder {

            color: #7785a7;
        }


        .form-container input:focus,
        .form-container select:focus {

            border-color: #55aaff;

            box-shadow:
                0 0 0 3px
                rgba(70, 160, 255, 0.10),

                0 0 20px
                rgba(70, 160, 255, 0.15);
        }


        .form-container select {

            cursor: pointer;
        }


        .form-container select option {

            background: #101a35;

            color: white;
        }


        /* ================================
           PASSWORD
        ================================= */

        .password-box {

            position: relative;

            width: 100%;
        }


        .password-box input {

            padding-right: 50px;
        }


        .password-box span {

            position: absolute;

            right: 15px;

            top: 50%;

            transform:
                translateY(-50%);

            cursor: pointer;

            font-size: 19px;

            user-select: none;
        }


        /* ================================
           BUTTON
        ================================= */

        .form-container button {

            width: 100%;

            padding: 14px;

            margin-top: 25px;

            border: none;

            border-radius: 12px;

            color: white;

            font-size: 16px;

            font-weight: bold;

            cursor: pointer;

            background:
                linear-gradient(
                    90deg,
                    #4c55ff,
                    #8a43ff,
                    #229cff
                );

            box-shadow:
                0 10px 25px
                rgba(80, 90, 255, 0.3);

            transition: 0.3s;
        }


        .form-container button:hover {

            transform:
                translateY(-3px);

            box-shadow:
                0 15px 35px
                rgba(80, 100, 255, 0.5);
        }


        .form-container button:active {

            transform: scale(0.98);
        }


        /* ================================
           SUCCESS / ERROR MESSAGE
        ================================= */

        .message {

            text-align: center;

            padding: 12px;

            margin-bottom: 15px;

            border-radius: 10px;

            color: #72ffb2;

            background:
                rgba(0, 255, 130, 0.08);

            border:
                1px solid
                rgba(0, 255, 130, 0.2);
        }


        /* ================================
           ANIMATION
        ================================= */

        @keyframes formAppear {

            from {

                opacity: 0;

                transform:
                    translateX(40px);
            }

            to {

                opacity: 1;

                transform:
                    translateX(0);
            }
        }


        /* ================================
           MOBILE
        ================================= */

        @media (max-width: 950px) {

            .register-page {

                flex-direction: column;

                width: 94%;
            }


            .register-image {

                width: 100%;

                height: 420px;
            }


            .form-container {

                width: 100%;

                max-width: 500px;
            }
        }


        @media (max-width: 600px) {

            nav {

                height: auto;

                padding: 18px;

                flex-direction: column;

                gap: 15px;
            }


            nav div {

                gap: 18px;
            }


            .register-page {

                padding: 25px 0;
            }


            .register-image {

                height: 300px;
            }


            .image-overlay {

                padding: 50px 25px 25px;
            }


            .image-overlay h1 {

                font-size: 25px;
            }


            .form-container {

                padding: 25px 20px;
            }


            .form-container h1 {

                font-size: 25px;
            }
        }

    </style>

</head>


<body>


<!-- ================================
     NAVIGATION
================================= -->

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


<!-- ================================
     REGISTRATION PAGE
================================= -->

<div class="register-page">


    <!-- IMAGE -->

    <div class="register-image">

        <img
            src="cts.jpg"
            alt="CTS Portal Registration"
        >

        <div class="image-overlay">

            <h1>
                Welcome to CTS Portal
            </h1>

            <p>
                Create your account and
                connect with your company.
            </p>

        </div>

    </div>


    <!-- FORM -->

    <div class="form-container">

        <h1>
            📝 Registration Form
        </h1>


        <?php

        if ($message != "") {

            echo
            "<p class='message'>
                $message
            </p>";

        }

        ?>


        <form
            method="POST"
            onsubmit="return validateForm()"
            autocomplete="off"
        >


            <!-- NAME -->

            <label>
                Name
            </label>

            <input
                type="text"
                name="name"
                id="name"
                placeholder="Enter your name"
                autocomplete="off"
                required
            >


            <!-- EMAIL -->

            <label>
                Email
            </label>

            <input
                type="email"
                name="email"
                id="email"
                placeholder="Enter your email"
                autocomplete="off"
                required
            >


            <!-- PASSWORD -->

            <label>
                Password
            </label>

            <div class="password-box">

                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Create password"
                    autocomplete="new-password"
                    required
                >

                <span onclick="togglePassword()">
                    👁️
                </span>

            </div>


            <!-- PHONE -->

            <label>
                Phone
            </label>

            <input
                type="text"
                name="phone"
                placeholder="Enter phone number"
                autocomplete="off"
                required
            >


            <!-- DEPARTMENT -->

            <label>
                Department
            </label>

            <select
                name="department"
                required
            >

                <option value="">
                    Select Department
                </option>

                <option value="Computer Science">
                    Computer Science
                </option>

                <option value="Human Resources">
                    Human Resources
                </option>

                <option value="Finance">
                    Finance
                </option>

                <option value="Marketing">
                    Marketing
                </option>

            </select>


            <!-- ROLE -->

            <label>
                Register As
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


            <!-- BUTTON -->

            <button type="submit">
                🚀 Register
            </button>


        </form>

    </div>

</div>


<!-- ================================
     JAVASCRIPT
================================= -->

<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    if (password.type === "password") {

        password.type = "text";

    } else {

        password.type = "password";

    }

}


function validateForm() {

    const name =
        document.getElementById("name")
        .value
        .trim();

    const password =
        document.getElementById("password")
        .value;


    if (name.length < 3) {

        alert(
            "⚠️ Name must contain at least 3 characters."
        );

        return false;
    }


    if (password.length < 6) {

        alert(
            "🔐 Password must contain at least 6 characters."
        );

        return false;
    }


    return true;

}

</script>


</body>

</html>