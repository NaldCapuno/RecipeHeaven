<?php
    session_start();
    include "conn.php";

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Not logged in"]);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $recipe_id = intval($_POST['recipe_id']);
        $user_id = intval($_SESSION['user_id']);

        $check_like_query = "SELECT * FROM likes_on_recipes WHERE user_id = $user_id AND recipe_id = $recipe_id";
        $check_like_result = $conn->query($check_like_query);

        if ($check_like_result->num_rows > 0) {
            $delete_like_query = "DELETE FROM likes_on_recipes WHERE user_id = $user_id AND recipe_id = $recipe_id";
            $conn->query($delete_like_query);
            $liked = false;
        } else {
            $insert_like_query = "INSERT INTO likes_on_recipes (user_id, recipe_id) VALUES ($user_id, $recipe_id)";
            $conn->query($insert_like_query);
            $liked = true;
        }

        $like_count_query = "SELECT COUNT(*) AS like_count FROM likes_on_recipes WHERE recipe_id = $recipe_id";
        $like_count_result = $conn->query($like_count_query);
        $like_count = $like_count_result->fetch_assoc()['like_count'];

        echo json_encode(["status" => "success", "liked" => $liked, "like_count" => $like_count]);
        exit();
    }
?>