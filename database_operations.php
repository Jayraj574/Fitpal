<?php
session_start();
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$dbname = "fitnesspal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Register user
if (isset($_POST['register'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $full_name, $email, $hashed_password);

        if ($stmt->execute()) {
            echo "Registration successful!";
            // Redirect back to the login form (index page) after successful registration
            header("Location: index.html");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Passwords do not match!";
    }
}

// Login user
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $user_id;
            
            // Check if the user already has fitness data
            $stmt_fitness = $conn->prepare("SELECT COUNT(*) FROM fitness_data WHERE user_id = ?");
            $stmt_fitness->bind_param("i", $user_id);
            $stmt_fitness->execute();
            $stmt_fitness->bind_result($fitness_data_count);
            $stmt_fitness->fetch();
            $stmt_fitness->close();

            // If fitness data exists, redirect to home.php, otherwise to main.html
            if ($fitness_data_count > 0) {
                header("Location: home.php");
            } else {
                header("Location: main.html");
            }
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found with that email!";
    }
}

// Insert fitness data
if (isset($_POST['submit_fitness_data'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.html");
        exit();
    }
    // Fitness form data
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $goal = $_POST['goal'];

    $user_id = $_SESSION['user_id'];  // Get user ID from session

    // Insert fitness data into the database
    $stmt = $conn->prepare("INSERT INTO fitness_data (user_id, age, gender, weight, height, goal) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisdds", $user_id, $age, $gender, $weight, $height, $goal);

    if ($stmt->execute()) {
        // Redirect to home.php after successful submission
        header("Location: home.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

$conn->close();
?>
