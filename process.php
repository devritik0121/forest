<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "wildlife_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Registration Logic
if (isset($_POST["register"])) {
    $username = htmlspecialchars($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $first_name = htmlspecialchars($_POST["first_name"]);
    $middle_name = htmlspecialchars($_POST["middle_name"] ?? "");
    $last_name = htmlspecialchars($_POST["last_name"]);
    $gender = htmlspecialchars($_POST["gender"]);
    $date_of_birth = $_POST["date_of_birth"];
    $email = htmlspecialchars($_POST["email"]);
    $mobile_number = htmlspecialchars($_POST["mobile_number"]);
    $area = htmlspecialchars($_POST["area"]);
    $pincode = htmlspecialchars($_POST["pincode"]);
    $city = htmlspecialchars($_POST["city"]);
    $state = htmlspecialchars($_POST["state"]);
    $security_question = htmlspecialchars($_POST["security_question"]);
    $security_answer = htmlspecialchars($_POST["security_answer"]);
    $role = htmlspecialchars($_POST["role"]);
    $approved = 0;

    $check_sql = "SELECT username, email FROM user WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        echo "<script>alert('Username or Email already registered!'); window.location.href='register.php';</script>";
    } else {
        $sql = "INSERT INTO user (username, password, first_name, middle_name, last_name, gender, date_of_birth, email, mobile_number, area, pincode, city, state, security_question, security_answer, role, approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssss", $username, $password, $first_name, $middle_name, $last_name, $gender, $date_of_birth, $email, $mobile_number, $area, $pincode, $city, $state, $security_question, $security_answer, $role, $approved);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Awaiting admin approval.\\nNow you can start your journey!'); window.location.href='index.html';</script>";
        } else {
            echo "<script>alert('Registration failed. Try again.'); window.location.href='register.php';</script>";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Login Logic
if (isset($_POST["login"])) {
    $email = htmlspecialchars($_POST["email"]);
    $password = $_POST["password"];

    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        if ($user["approved"] == 1) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] === "admin") {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.html");
            }
            exit();
        } else {
            echo "<script>alert('Your account is pending admin approval.'); window.location.href='login.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid email or password'); window.location.href='login.php';</script>";
    }

    $stmt->close();
}

// Admin Actions
if (isset($_POST["approve"])) {
    $user_id = $_POST["user_id"];
    $sql = "UPDATE user SET approved = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('User approved successfully!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Approval failed.'); window.location.href='admin.php';</script>";
    }
    $stmt->close();
}

if (isset($_POST["reject"])) {
    $user_id = $_POST["user_id"];
    $sql = "DELETE FROM user WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('User rejected and removed successfully!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Rejection failed.'); window.location.href='admin.php';</script>";
    }
    $stmt->close();
}

if (isset($_POST["delete"])) {
    $user_id = $_POST["user_id"];
    $sql = "DELETE FROM user WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('User deleted successfully!'); window.location.href='admin.php';</script>";
    } else {
        echo "<script>alert('Deletion failed.'); window.location.href='admin.php';</script>";
    }
    $stmt->close();
}

// Report Submission
if (isset($_POST["submit_report"])) {
    $user_id = $_SESSION["user_id"];
    $description = htmlspecialchars($_POST["description"]);
    $latitude = $_POST["latitude"];
    $longitude = $_POST["longitude"];
    $photo = $_FILES["photo"]["name"];
    $target = "uploads/" . basename($photo);

    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target)) {
        $sql = "INSERT INTO reports (user_id, photo, description, location_lat, location_lng) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issdd", $user_id, $photo, $description, $latitude, $longitude);
        if ($stmt->execute()) {
            echo "<script>alert('Report submitted successfully!'); window.location.href='report-submit.php';</script>";
        } else {
            echo "<script>alert('Report submission failed.'); window.location.href='report-submit.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Photo upload failed.'); window.location.href='report-submit.php';</script>";
    }
}

// Forum Thread Submission
if (isset($_POST["submit_thread"])) {
    $user_id = $_SESSION["user_id"];
    $title = htmlspecialchars($_POST["title"]);
    $message = htmlspecialchars($_POST["message"]);
    $category = htmlspecialchars($_POST["category"]);

    $sql = "INSERT INTO forum_threads (user_id, title, message, category) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $title, $message, $category);
    if ($stmt->execute()) {
        echo "<script>alert('Thread posted successfully!'); window.location.href='forum.php';</script>";
    } else {
        echo "<script>alert('Thread posting failed.'); window.location.href='forum.php';</script>";
    }
    $stmt->close();
}

// Donation
if (isset($_POST["donate"])) {
    $user_id = $_SESSION["user_id"];
    $event_id = $_POST["event_id"];
    $amount = $_POST["amount"];

    $sql = "INSERT INTO donations (user_id, event_id, amount) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iid", $user_id, $event_id, $amount);
    if ($stmt->execute()) {
        echo "<script>alert('Donation successful!'); window.location.href='events.php';</script>";
    } else {
        echo "<script>alert('Donation failed.'); window.location.href='events.php';</script>";
    }
    $stmt->close();
}

// Volunteer
if (isset($_POST["volunteer"])) {
    $user_id = $_SESSION["user_id"];
    $event_id = $_POST["event_id"];

    $sql = "INSERT INTO volunteers (user_id, event_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $event_id);
    if ($stmt->execute()) {
        echo "<script>alert('Volunteered successfully!'); window.location.href='events.php';</script>";
    } else {
        echo "<script>alert('Volunteering failed.'); window.location.href='events.php';</script>";
    }
    $stmt->close();
}

$conn->close();
?>