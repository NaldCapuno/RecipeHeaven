<?php
    session_start();
    include "conn.php";

    $recipes = "
        SELECT recipes.*, 
            users.username, 
            categories.category_name,
            COUNT(likes_on_recipes.like_id) AS like_count,
            DATE(recipes.created_at) AS created_date
        FROM recipeheaven.recipes
        JOIN recipeheaven.users ON recipes.user_id = users.user_id
        JOIN recipeheaven.categories ON recipes.category_id = categories.category_id
        LEFT JOIN recipeheaven.likes_on_recipes ON recipes.recipe_id = likes_on_recipes.recipe_id
        GROUP BY recipes.recipe_id
        ORDER BY recipes.recipe_id ASC";

    $trending = "
        SELECT recipes.*, 
            users.username, 
            categories.category_name,
            COUNT(likes_on_recipes.like_id) AS like_count
        FROM recipeheaven.recipes
        JOIN recipeheaven.users ON recipes.user_id = users.user_id
        JOIN recipeheaven.categories ON recipes.category_id = categories.category_id
        LEFT JOIN recipeheaven.likes_on_recipes ON recipes.recipe_id = likes_on_recipes.recipe_id
        GROUP BY recipes.recipe_id
        ORDER BY like_count DESC
        LIMIT 10";

    $result = $conn->query($recipes);
    $trendingrecipes = $conn->query($trending);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
    <title>RecipeHeaven</title>
</head>
<body>
    <div class="navbar">
        <ul>
            <li class="home"><a href="index.php">RecipeHeaven</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="user-info">
                    <span>Welcome, <a href="account.php?user_id=<?php echo $_SESSION['user_id']; ?>"><?php echo $_SESSION['username']; ?></a></span>
                </li>
            <?php endif; ?>
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
        <a href="add_recipe.php" class="add-btn"><i class="fa-solid fa-plus"></i></a>
        <div class="recipes">
            <ul>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="recipe">
                        <li>
                            <div class="user-recipe">
                                <div class="user-info">
                                    <i class="fa-regular fa-circle-user"></i>
                                    <div>
                                        <a href="account.php?user_id=<?php echo $row['user_id']; ?>"><h3><?php echo $row['username']; ?></h3></a>
                                        <p><?php echo $row['created_date']?></p>
                                    </div>
                                </div>
                            </div>
                            <h4><?php echo $row['category_name']?></h4>
                            <a href="recipe.php?recipe_id=<?php echo $row['recipe_id']; ?>"><h2><?php echo $row['recipe_name']?></h2></a>
                            <i class="fa-solid fa-thumbs-up"></i><p><?php echo $row['like_count']?></p>
                        </li>
                    </div>
                <?php endwhile; ?>
            </ul>
        </div>
        <div class="trending">
            <h2>Trending Recipes</h2>
            <ul>
                <?php while ($row = $trendingrecipes->fetch_assoc()): ?>
                    <div class="trending-recipe">
                        <li>
                            <h5 style="color: grey;"><?php echo $row['category_name']?></h5>
                            <a href="recipe.php?recipe_id=<?php echo $row['recipe_id']; ?>"><h3><?php echo $row['recipe_name']?></h3></a>
                            <div class="like-count">
                            <i class="fa-solid fa-thumbs-up"></i><p><?php echo $row['like_count']?></p>
                            </div>
                        </li>
                    </div>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</body>
</html>