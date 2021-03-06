<?php

require 'config/config.php';
include("includes/classes/User.php");
include("includes/classes/Post.php");
include("includes/classes/Message.php");
include("includes/classes/Quiz.php");

if (isset($_SESSION['username'])) {
    //If user is logged in, it contains the username
    $loggedUser = $_SESSION['username'];
    $firstName = $_SESSION['firstName'];
    $lastName = $_SESSION['lastName'];

    $user_details_query = mysqli_query($con, "SELECT * FROM regUser WHERE username='$loggedUser'");

    $user = mysqli_fetch_array($user_details_query);
    //Has an array of all user data.
    $userLoggedIn = new User($con, $user['username']);

    $designation = $userLoggedIn->returnDesignation();

} else {
    header("Location: login.php");
}

?>

<html lang="en">

<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title>PlenTree</title>

    <link rel="icon" type="image/png" sizes="96x96" href="assets/images/icons/favicons/icons.png">
    
    <link rel="stylesheet" href="assets/css/all.css">
	<link rel="stylesheet" href="assets/css/bootstrap.min.css">   
    <link rel="stylesheet" href="assets/css/home.css">

</head>

<body>

<link rel="stylesheet" href="assets/css/post.css">

<script>

//Show or hide the comment box
function toggle(){
    var element = document.getElementById("commentSection");

    if(element.style.display == "block"){
        element.style.display = "none";
    }
    else {
        element.style.display = "block";
    }

}

</script>

<?php
    //Get id of post
    if (isset($_GET['postID'])) {
        $postID = $_GET['postID'];
    }
        
    // added_by - userId
    $userID = $user['id'];
    // user_to - classroomId
    $user_query = mysqli_query($con, "SELECT userId, classroomId FROM postuser WHERE postId='$postID'");
    $row = mysqli_fetch_array($user_query);

    $postUser = $row['userId'];
    $classroomID = $row['classroomId'];

    if (isset($_POST['postComment' . $postID])) {
        $commentBody = $_POST['commentBody'];
        $commentBody = mysqli_escape_string($con, $commentBody);
        $date = date("Y-m-d H:i:s");
        $insertComment = mysqli_query($con, "INSERT INTO comments VALUES ('', '$date', '$commentBody') ");
        $insertedCommentID = mysqli_insert_id($con);
        $insertCommentUsers = mysqli_query($con, "INSERT INTO commentPost VALUES ('$insertedCommentID', '$postID', '$userID')");
        echo "<p>Comment Posted! </p>";
    }

    ?>


        <!-- Also passed in a parameter -->
        <form action="commentFrame.php?postID=<?php echo $postID ?>" id="comment_form" id="comment_form" name="postComment<?php echo $post_id ?>" method="POST">
            <textarea name="commentBody"></textarea>
            <input type="submit" name="postComment<?php echo $postID ?>" value="Post Comment">

        </form>

        <?php

    $get_comments = mysqli_query($con, "SELECT * FROM comments c JOIN commentPost cP ON c.commentId=cP.commentId WHERE postId='$postID' ORDER BY c.commentId ASC");
    $count = mysqli_num_rows($get_comments);

    if ($count != 0) {

        while ($comment = mysqli_fetch_array($get_comments)) {

            $comment_body = $comment['commentBody'];
            $posted_by = $comment['userId'];

            // Get username of the posting user
            $posted_byUN = mysqli_query($con, "SELECT username FROM regUser where id='$posted_by'");
            $posted_byUN = mysqli_fetch_array($posted_byUN);
            $posted_byUN = $posted_byUN['username'];

            $date_added = $comment['date'];

            //Timeframe

            $date_time_now = date("Y-m-d H:i:s");
            $start_date = new DateTime($date_added); //Time of post
            $end_date = new DateTime($date_time_now); //Current time
            $interval = $start_date->diff($end_date); //Difference between dates
            if ($interval->y >= 1) {
                if ($interval == 1) {
                    $time_message = $interval->y . " year ago";
                }
                //1 year ago
                else {
                    $time_message = $interval->y . " years ago";
                }
                //1+ year ago
            } else if ($interval->m >= 1) {
                if ($interval->d == 0) {
                    $days = " ago";
                } else if ($interval->d == 1) {
                    $days = $interval->d . " day ago";
                } else {
                    $days = $interval->d . " days ago";
                }

                if ($interval->m == 1) {
                    $time_message = $interval->m . " month" . $days;
                } else {
                    $time_message = $interval->m . " months" . $days;
                }

            } else if ($interval->d >= 1) {
                if ($interval->d == 1) {
                    $time_message = "Yesterday";
                } else {
                    $time_message = $interval->d . " days ago";
                }
            } else if ($interval->h >= 1) {
                if ($interval->h == 1) {
                    $time_message = $interval->h . " hour ago";
                } else {
                    $time_message = $interval->h . " hours ago";
                }
            } else if ($interval->i >= 1) {
                if ($interval->i == 1) {
                    $time_message = $interval->i . " minute ago";
                } else {
                    $time_message = $interval->i . " minutes ago";
                }
            } else {
                if ($interval->s < 30) {
                    $time_message = "Just now";
                } else {
                    $time_message = $interval->s . " seconds ago";
                }
            }

            $user_obj = new User($con, $posted_byUN);

            ?>


            <div class="comment_section">
                <a href="profile.php?id=<?php echo $user_obj->getUserID(); ?>" target="_parent"> 
                    <img src="<?php echo $user_obj->getProfilePic() ?>" title="<?php echo $posted_byUN; ?>" style="float:left;" height="30">   
                </a>

                <a href="profile.php?id=<?php echo $posted_byUN; ?>" target="_parent"> <b><?php echo $user_obj->getFirstAndLastName();?></b> </a>
                &nbsp;&nbsp;&nbsp;&nbsp;

                <?php echo $time_message . "<br>" . $comment_body; ?>

            </div>

            <?php

        } //end of while

    } //end of if
    else {
        //No comment available
        echo "<center><br><br>No comments to show!</center>";
    }

?>


<?php
    include 'includes/footer.php';
?>