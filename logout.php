<?php
    session_start();

    if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
        session_destroy();
        header("Location: index.php");
        exit;
    }

    echo "<script>
            var result = confirm('Are you sure you want to log out?');
            if (result) {
                window.location.href = 'logout.php?confirm=yes';
            } else {
                window.location.href = 'index.php';
            }
          </script>";
?>