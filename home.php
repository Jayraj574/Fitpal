<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitnessPal - Home</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <div class="overlay"></div>
    <div class="container">
        <h1>Welcome to FitnessPal</h1>
        
        <?php
        session_start();
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "fitnesspal";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            
            // Get user's general data
            $sql_user = "SELECT full_name, email FROM users WHERE id = ?";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $stmt_user->bind_result($full_name, $email);
            $stmt_user->fetch();
            $stmt_user->close();
            
            // Get user's fitness data
            $sql_fitness = "SELECT age, gender, weight, height, goal FROM fitness_data WHERE user_id = ?";
            $stmt_fitness = $conn->prepare($sql_fitness);
            $stmt_fitness->bind_param("i", $user_id);
            $stmt_fitness->execute();
            $stmt_fitness->bind_result($age, $gender, $weight, $height, $goal);
            $stmt_fitness->fetch();
            $stmt_fitness->close();

            echo "<div class='dashboard-grid'>";
            
            // Profile Section
            echo "<div class='section'>";
            echo "<h2>Profile Information</h2>";
            echo "<div class='profile-info'>";
            echo "<p><strong>Name:</strong> $full_name</p>";
            echo "<p><strong>Email:</strong> $email</p>";
            echo "</div>";
            
            if (isset($age)) {
                echo "<div class='fitness-data'>";
                echo "<p><strong>Age:</strong> $age years</p>";
                echo "<p><strong>Gender:</strong> $gender</p>";
                echo "<p><strong>Weight:</strong> $weight kg</p>";
                echo "<p><strong>Height:</strong> $height cm</p>";
                echo "<p><strong>Goal:</strong> " . ucwords(str_replace('_', ' ', $goal)) . "</p>";
                echo "</div>";
            }
            echo "</div>";

            // Nutrition Plan Section
            $sql_nutrition = "SELECT * FROM nutrition_plans WHERE user_id = ?";
            $stmt_nutrition = $conn->prepare($sql_nutrition);
            $stmt_nutrition->bind_param("i", $user_id);
            $stmt_nutrition->execute();
            $result_nutrition = $stmt_nutrition->get_result();
            $nutrition_plan = $result_nutrition->fetch_assoc();
            
            if ($nutrition_plan) {
                echo "<div class='section'>";
                echo "<h2>Your Nutrition Plan</h2>";
                echo "<div class='nutrition-plan'>";
                
                // Macro nutrients display
                echo "<h3>Daily Targets</h3>";
                echo "<div class='macro-nutrients'>";
                echo "<div class='macro-box'>";
                echo "<h3>Calories</h3>";
                echo "<p>{$nutrition_plan['daily_calories']}</p>";
                echo "</div>";
                
                echo "<div class='macro-box'>";
                echo "<h3>Protein</h3>";
                echo "<p>{$nutrition_plan['protein_g']}g</p>";
                echo "</div>";
                
                echo "<div class='macro-box'>";
                echo "<h3>Carbs</h3>";
                echo "<p>{$nutrition_plan['carbs_g']}g</p>";
                echo "</div>";
                
                echo "<div class='macro-box'>";
                echo "<h3>Fats</h3>";
                echo "<p>{$nutrition_plan['fats_g']}g</p>";
                echo "</div>";
                echo "</div>";

                // Meal plan display
                echo "<div class='meal-plan'>";
                echo "<h3>Daily Meal Plan</h3>";
                
                echo "<div class='meal-section'>";
                echo "<h4>Breakfast</h4>";
                echo "<p>{$nutrition_plan['breakfast']}</p>";
                echo "</div>";
                
                echo "<div class='meal-section'>";
                echo "<h4>Lunch</h4>";
                echo "<p>{$nutrition_plan['lunch']}</p>";
                echo "</div>";
                
                echo "<div class='meal-section'>";
                echo "<h4>Dinner</h4>";
                echo "<p>{$nutrition_plan['dinner']}</p>";
                echo "</div>";
                
                echo "<div class='meal-section'>";
                echo "<h4>Snacks</h4>";
                echo "<p>{$nutrition_plan['snacks']}</p>";
                echo "</div>";
                
                echo "</div>"; // End meal-plan
                echo "</div>"; // End nutrition-plan
                echo "</div>"; // End section
            }
            
            echo "</div>"; // End dashboard-grid
            
        } else {
            echo "<p>Please log in to view your profile.</p>";
        }

        $conn->close();
        ?>
        
        <a href="index.html" class="logout-button">Logout</a>
    </div>
</body>
</html>