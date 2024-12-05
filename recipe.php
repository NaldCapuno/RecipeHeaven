<?php
    session_start();
    include "conn.php";

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

    $trendingrecipes = $conn->query($trending);

    if (isset($_GET['recipe_id'])) {
        $recipe_id = $_GET['recipe_id'];

        $recipe_details_query = "
            SELECT recipes.*, 
                users.username, 
                categories.category_name,
                COUNT(likes_on_recipes.like_id) AS like_count
            FROM recipeheaven.recipes
            JOIN recipeheaven.users ON recipes.user_id = users.user_id
            JOIN recipeheaven.categories ON recipes.category_id = categories.category_id
            LEFT JOIN recipeheaven.likes_on_recipes ON recipes.recipe_id = likes_on_recipes.recipe_id
            WHERE recipes.recipe_id = $recipe_id
        ";

        $result = $conn->query($recipe_details_query);
        $recipe = $result->fetch_assoc();
    } else {
        echo "Recipe ID is not provided.";
    }

    $is_liked = false;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $check_like_query = "SELECT * FROM likes_on_recipes WHERE user_id = $user_id AND recipe_id = $recipe_id";
        $check_like_result = $conn->query($check_like_query);
        if ($check_like_result->num_rows > 0) {
            $is_liked = true;
        }
    }

    if (isset($_POST['like_recipe']) && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $recipe_id = $_POST['recipe_id'];

        $check_like_query = "SELECT * FROM likes_on_recipes WHERE user_id = $user_id AND recipe_id = $recipe_id";
        $check_like_result = $conn->query($check_like_query);

        if ($check_like_result->num_rows == 0) {
            $insert_like_query = "INSERT INTO likes_on_recipes (user_id, recipe_id) VALUES ($user_id, $recipe_id)";
            $conn->query($insert_like_query);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/recipe.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
    <title><?php echo $recipe['recipe_name'] . " | " . $recipe['username']?></title>
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
        <div class="recipe-detail">
            <h1><?php echo $recipe['recipe_name']?></h1>
            <p>by: <?php echo $recipe['username']?></p>
            <p style="font-weight: bold; color: grey;"><?php echo $recipe['category_name']?></p>
            <hr>
            <p><?php echo $recipe['description']?></p>
            <h3>Ingredients:</h3>
            <ul>
                <?php 
                    $ingredients = explode(',', $recipe['ingredients']); 
                    foreach ($ingredients as $ingredient): 
                ?>
                    <li><?php echo "- ".trim($ingredient); ?></li>
                <?php endforeach; ?>
            </ul>
            <h3>Instructions:</h3>
            <?php
                if (preg_match('/\d+(\.)/', $recipe['instructions'])) {
                    $steps = preg_split('/(?=\d+\.)/', $recipe['instructions']);
                    echo '<ol>';
                    foreach ($steps as $step) {
                        if (trim($step) != '') {
                            echo '<li>' . trim($step) . '</li>';
                        }
                    }
                    echo '</ol>';
                } else {
                    echo '<p>' . nl2br($recipe['instructions']) . '</p>';
                }
            ?>
            <form action="" method="POST" class="like-form">
                <input type="hidden" name="recipe_id" value="<?php echo $recipe['recipe_id']; ?>">
                <button type="submit" name="like_recipe" class="like-btn <?php echo $is_liked ? 'liked' : ''; ?>">
                    <i class="fa-solid fa-thumbs-up"></i>
                    <span><?php echo $recipe['like_count']; ?></span>
                    <?php if ($is_liked): ?>
                        <span class="liked-text">Liked</span>
                    <?php endif; ?>
                </button>
            </form>
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
    <script>
        document.querySelector(".like-form").addEventListener("submit", function (event) {
            event.preventDefault();

            const form = event.target;
            const recipeId = form.querySelector('input[name="recipe_id"]').value;
            const likeButton = form.querySelector(".like-btn");

            fetch("like_recipe.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `recipe_id=${recipeId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    likeButton.classList.toggle("liked", data.liked);

                    likeButton.querySelector("span").textContent = data.like_count;

                    if (data.liked) {
                        if (!likeButton.querySelector(".liked-text")) {
                            const likedText = document.createElement("span");
                            likedText.className = "liked-text";
                            likedText.textContent = "Liked";
                            likeButton.appendChild(likedText);
                        }
                    } else {
                        const likedText = likeButton.querySelector(".liked-text");
                        if (likedText) likedText.remove();
                    }
                } else {
                    alert(data.message || "An error occurred.");
                }
            })
            .catch(error => console.error("Error:", error));
        });
    </script>
</body>
</html>
