<?php
session_start();
$conn = new mysqli("localhost", "root", "", "wildlife_db");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Check if username already exists
    $check_sql = "SELECT id FROM users WHERE username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error = "Username already taken!";
    } else {
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password); // Plain text for simplicity; hash in production
        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed!";
        }
        $stmt->close();
    }
    $check_stmt->close();
}

// Check if user is logged in and fetch username for navbar
$logged_in = isset($_SESSION['user_id']);
$username = '';
if ($logged_in) {
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT username FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $username = htmlspecialchars($row['username']);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Forest & Wildlife Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0a0f1a 0%, #1a2a44 100%); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .neon-glow { box-shadow: 0 0 25px rgba(34, 197, 94, 0.8), 0 0 50px rgba(34, 197, 94, 0.4); }
        .neon-hover:hover { box-shadow: 0 0 30px rgba(34, 197, 94, 0.9); transform: scale(1.05); transition: all 0.4s ease; }
        .register-box { 
            background: rgba(255, 255, 255, 0.1); 
            backdrop-filter: blur(15px); 
            border-radius: 20px; 
            padding: 40px; 
            box-shadow: 0 0 40px rgba(34, 197, 94, 0.6); 
            transition: transform 0.3s ease; 
        }
        .register-box:hover { transform: scale(1.02); }
        .input-field { 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(34, 197, 94, 0.5); 
            padding: 12px; 
            border-radius: 10px; 
            color: white; 
            width: 100%; 
            transition: border-color 0.3s ease; 
        }
        .input-field:focus { border-color: #22c55e; outline: none; }
        .register-btn { 
            background: linear-gradient(90deg, #1e40af, #22c55e); 
            padding: 12px; 
            border-radius: 10px; 
            font-weight: bold; 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
        }
        .register-btn:hover { transform: scale(1.05); box-shadow: 0 0 20px rgba(34, 197, 94, 0.8); }
        .nav-btn { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .nav-btn:hover { transform: scale(1.1); box-shadow: 0 0 20px rgba(34, 197, 94, 0.8); }
    </style>
</head>
<body class="text-white relative min-h-screen">
    <!-- 1. Header (Navigation Bar) -->
    <nav class="relative z-20 glass p-4 md:p-6 shadow-lg fixed w-full top-0">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <h1 class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 bg-clip-text text-transparent">Forest & Wildlife Monitoring</h1>
            <div class="mt-4 md:mt-0 space-x-4 md:space-x-8 text-base md:text-lg">
                <a href="index.php" class="neon-hover px-3 py-1 rounded-full hover:bg-green-500/20 transition">Home</a>
                <a href="reports.php" class="neon-hover px-3 py-1 rounded-full hover:bg-green-500/20 transition">Reports</a>
                <a href="about.php" class="neon-hover px-3 py-1 rounded-full hover:bg-green-500/20 transition">About Us</a>
                <a href="contact.php" class="neon-hover px-3 py-1 rounded-full hover:bg-green-500/20 transition">Contact</a>
                <?php if ($logged_in): ?>
                    <span class="px-4 py-2 text-green-400"><?php echo $username; ?></span>
                    <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow nav-btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="bg-green-500 px-4 py-2 rounded-full neon-glow nav-btn">Login</a>
                    <a href="register.php" class="bg-blue-500 px-4 py-2 rounded-full neon-glow nav-btn">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- 2. Register Section -->
    <section class="relative z-20 min-h-screen flex items-center justify-center pt-24">
        <div class="register-box animate__animated animate__fadeIn w-full max-w-md">
            <h2 class="text-3xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-8">Join the Movement</h2>
            <?php if (isset($error)): ?>
                <p class="text-red-400 text-center mb-4"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Username</label>
                    <input type="text" name="username" placeholder="Choose a username" class="input-field" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Password</label>
                    <input type="password" name="password" placeholder="Create a password" class="input-field" required>
                </div>
                <button type="submit" class="register-btn w-full text-white">Register</button>
            </form>
            <p class="text-gray-300 text-center mt-6">Already have an account? <a href="login.php" class="text-green-400 hover:underline">Login here</a></p>
        </div>
    </section>

    <!-- 3. Footer -->
    <footer class="relative z-20 glass py-16">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-4 gap-8 text-center md:text-left">
            <div>
                <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                <a href="index.php" class="block text-gray-300 hover:text-green-400">Home</a>
                <a href="reports.php" class="block text-gray-300 hover:text-green-400">Reports</a>
                <a href="about.php" class="block text-gray-300 hover:text-green-400">About</a>
                <a href="contact.php" class="block text-gray-300 hover:text-green-400">Contact</a>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Social Media</h3>
                <a href="#" class="block text-gray-300 hover:text-green-400">Facebook</a>
                <a href="#" class="block text-gray-300 hover:text-green-400">Twitter</a>
                <a href="#" class="block text-gray-300 hover:text-green-400">Instagram</a>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Contact Info</h3>
                <p class="text-gray-300">Email: info@wildlife.org</p>
                <p class="text-gray-300">Phone: +91 123-456-7890</p>
                <p class="text-gray-300">Address: Eco Street, Green City</p>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Legal</h3>
                <a href="#" class="block text-gray-300 hover:text-green-400">Privacy Policy</a>
                <a href="#" class="block text-gray-300 hover:text-green-400">Terms & Conditions</a>
            </div>
        </div>
        <p class="text-center text-gray-300 mt-8">© 2025 | Made with ❤️ for Nature</p>
    </footer>

    <!-- JavaScript -->
    <script>
        // No map or scroll animation needed here, keeping it lightweight
    </script>
</body>
</html>

<?php $conn->close(); ?>