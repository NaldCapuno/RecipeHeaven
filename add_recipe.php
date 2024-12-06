<?php
    session_start();
    include "conn.php";

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $recipe_name = $_POST['recipe_name'];
        $category_id = $_POST['category_id'];
        $ingredients = $_POST['ingredients'];
        $instructions = $_POST['instructions'];
        $user_id = $_SESSION['user_id'];

        $insert_recipe = "
            INSERT INTO recipes (user_id, recipe_name, category_id, ingredients, instructions, created_at)
            VALUES ('$user_id', '$recipe_name', '$category_id', '$ingredients', '$instructions', NOW())";

        if ($conn->query($insert_recipe)) {
            header("Location: index.php");
        } else {
            $error_message = "Error adding recipe: " . $conn->error;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/add_recipe.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <title>Add Recipe</title>
</head>
<body>
    <div class="navbar">
        <ul>
            <li class="home"><a href="index.php">RecipeHeaven</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="log-in-out"><a href="logout.php">Logout</a></li>
            <?php else: ?>
                <li class="log-in-out"><a href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="container">
        <div class="add-recipe">
            <h1>Add Your Own Recipe</h1>
            <hr>
            <?php if (isset($error_message)): ?>
                <p class="error"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form action="add_recipe.php" method="POST">
                <label for="recipe_name">Recipe Name:</label>
                <input type="text" id="recipe_name" name="recipe_name" required>
                
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <option value="" disabled selected>Select a category</option>
                    <?php
                    $categories_query = "SELECT * FROM categories";
                    $categories_result = $conn->query($categories_query);
                    while ($category = $categories_result->fetch_assoc()) {
                        echo "<option value='" . $category['category_id'] . "'>" . $category['category_name'] . "</option>";
                    }
                    ?>
                </select>
                
                <label for="ingredients">Ingredients (comma-separated):</label>
                <textarea id="ingredients" name="ingredients" rows="4" required></textarea>
                
                <label for="instructions">Instructions:</label>
                <textarea id="instructions" name="instructions" rows="6" required></textarea>
                
                <button type="submit">Add Recipe</button>
            </form>
        </div>
    </div>
</body>
</html>
