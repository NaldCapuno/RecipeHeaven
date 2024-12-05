<?php
    session_start();
    include "conn.php";

    if (!isset($_GET['user_id'])) {
        header("Location: index.php");
    }

    $user_id = $_GET['user_id'];

    $user_details_query = "SELECT username FROM recipeheaven.users WHERE user_id = $user_id";
    $user_details_result = $conn->query($user_details_query);
    $user_details = $user_details_result->fetch_assoc();

    $user_recipes_query = "
        SELECT recipes.*, 
            categories.category_name, 
            DATE(recipes.created_at) AS created_date,
            COUNT(likes_on_recipes.like_id) AS like_count
        FROM recipeheaven.recipes
        JOIN recipeheaven.categories ON recipes.category_id = categories.category_id
        LEFT JOIN recipeheaven.likes_on_recipes ON recipes.recipe_id = likes_on_recipes.recipe_id
        WHERE recipes.user_id = $user_id
        GROUP BY recipes.recipe_id
        ORDER BY recipes.created_at DESC";

    $result = $conn->query($user_recipes_query);

    $popular_recipes_query = "
        SELECT recipes.recipe_id, recipes.recipe_name, 
            COUNT(likes_on_recipes.like_id) AS like_count
        FROM recipeheaven.recipes
        LEFT JOIN recipeheaven.likes_on_recipes ON recipes.recipe_id = likes_on_recipes.recipe_id
        WHERE recipes.user_id = $user_id
        GROUP BY recipes.recipe_id
        ORDER BY like_count DESC
        LIMIT 3";

    $popular_result = $conn->query($popular_recipes_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/account.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
    <title><?php echo $user_details['username'] . "'s Recipes"?></title>
</head>
<body>
    <div class="navbar">
        <ul>
            <li class="home"><a href="index.php">RecipeHeaven</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="log-in-out">
                    <a href="logout.php">Logout</a>
                </li>
            <?php else: ?>
                <li class="log-in-out">
                    <a href="login.php">Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="container">
        <div class="user-recipe">
            <h1><?php echo $user_details['username']?>'s Recipes</h1>
            <hr>
            <br>
            <center><h2>Popular Recipes</h2></center>
            <br>
            <ul class="popular-recipes">
                <?php if ($popular_result->num_rows > 0): ?>
                    <?php while ($popular_row = $popular_result->fetch_assoc()): ?>
                        <li class="popular-recipe-item">
                            <a href="recipe.php?recipe_id=<?php echo $popular_row['recipe_id']; ?>">
                                <?php echo htmlspecialchars($popular_row['recipe_name']); ?>
                            </a>
                            <p><i class="fa-solid fa-thumbs-up"></i> <?php echo $popular_row['like_count']; ?> Likes</p>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No popular recipes available.</p>
                <?php endif; ?>
            </ul>
            <br>
            <br>
            <hr>
            <br>
            <ul class="recipe-list">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <li class="recipe-item">
                            <div class="recipe-details">
                                <h3><?php echo $row['recipe_name']?></h3>
                                <p style="font-weight: bold; color: grey;" class="category"><?php echo $row['category_name']?></p>
                                <p class="date"><?php echo $row['created_date']?></p>
                                <p class="like-count"><i class="fa-solid fa-thumbs-up"></i> <?php echo $row['like_count']?> Likes</p>
                                <a href="recipe.php?recipe_id=<?php echo $row['recipe_id']?>" class="view-recipe">View Recipe</a>
                            </div>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="no-recipes">This user has not uploaded any recipes yet.</p>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>
