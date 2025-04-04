<?php
session_start();
$conn = new mysqli("localhost", "root", "", "wildlife_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_sql = "SELECT username, role FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$username = htmlspecialchars($user['username']);
if ($user['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Manage Users
if (isset($_POST['toggle_role'])) {
    $target_user_id = $_POST['user_id'];
    $new_role = $_POST['current_role'] === 'user' ? 'admin' : 'user';
    $update_sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_role, $target_user_id);
    $stmt->execute();
}

// Manage Reports
if (isset($_POST['approve_report'])) {
    $report_id = $_POST['report_id'];
    $update_sql = "UPDATE reports SET status = 'approved' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
}
if (isset($_POST['disapprove_report'])) {
    $report_id = $_POST['report_id'];
    $update_sql = "UPDATE reports SET status = 'disapproved' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
}

// Fetch Data
$users_sql = "SELECT id, username, role FROM users";
$users_result = $conn->query($users_sql);

$reports_sql = "SELECT * FROM reports WHERE status = 'pending'";
$reports_result = $conn->query($reports_sql);

$analytics_sql = "SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM reports WHERE status = 'approved') as approved_reports,
    (SELECT COUNT(*) FROM reports WHERE status = 'pending') as pending_reports";
$analytics = $conn->query($analytics_sql)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Forest & Wildlife Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0a0f1a 0%, #1a2a44 100%); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .neon-glow { box-shadow: 0 0 25px rgba(34, 197, 94, 0.8), 0 0 50px rgba(34, 197, 94, 0.4); }
        .neon-hover:hover { box-shadow: 0 0 30px rgba(34, 197, 94, 0.9); transform: scale(1.05); transition: all 0.4s ease; }
        .nav-btn { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .nav-btn:hover { transform: scale(1.1); box-shadow: 0 0 20px rgba(34, 197, 94, 0.8); }
        .scroll-section { opacity: 0; transform: translateY(60px); transition: opacity 0.8s ease, transform 0.8s ease; }
        .scroll-section.visible { opacity: 1; transform: translateY(0); }
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
                <span class="px-4 py-2 text-green-400"><?php echo $username; ?> (Admin)</span>
                <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow nav-btn">Logout</a>
            </div>
        </div>
    </nav>

    <!-- 2. Admin Dashboard Section -->
    <section class="relative z-20 container mx-auto py-20 pt-24 scroll-section">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-12">Admin Dashboard</h2>
        
        <!-- Analytics -->
        <div class="glass p-8 rounded-xl neon-hover mb-12 mx-auto max-w-3xl">
            <h3 class="text-2xl font-bold mb-4">Analytics</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <p class="text-3xl font-bold text-green-400"><?php echo $analytics['total_users']; ?></p>
                    <p class="text-gray-300">Total Users</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-green-400"><?php echo $analytics['approved_reports']; ?></p>
                    <p class="text-gray-300">Approved Reports</p>
                </div>
                <div class="text-center">
                    <p class="text-3xl font-bold text-green-400"><?php echo $analytics['pending_reports']; ?></p>
                    <p class="text-gray-300">Pending Reports</p>
                </div>
            </div>
        </div>

        <!-- Manage Users -->
        <div class="glass p-8 rounded-xl neon-hover mb-12 mx-auto max-w-3xl">
            <h3 class="text-2xl font-bold mb-4">Manage Users</h3>
            <?php if ($users_result->num_rows > 0): ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <div class="bg-gray-800 p-4 rounded-lg flex justify-between items-center">
                            <p class="text-gray-300"><?php echo htmlspecialchars($user['username']); ?> (<?php echo $user['role']; ?>)</p>
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="current_role" value="<?php echo $user['role']; ?>">
                                <button type="submit" name="toggle_role" class="bg-blue-500 px-4 py-2 rounded-full text-white hover:bg-blue-600"><?php echo $user['role'] === 'user' ? 'Make Admin' : 'Make User'; ?></button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-300">No users found.</p>
            <?php endif; ?>
        </div>

        <!-- Manage Reports -->
        <div class="glass p-8 rounded-xl neon-hover mx-auto max-w-3xl">
            <h3 class="text-2xl font-bold mb-4">Pending Reports</h3>
            <?php if ($reports_result->num_rows > 0): ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php while ($report = $reports_result->fetch_assoc()): ?>
                        <div class="bg-gray-800 p-4 rounded-lg flex justify-between items-center">
                            <div>
                                <p class="text-gray-300"><?php echo htmlspecialchars($report['description']); ?> (<?php echo $report['species']; ?>)</p>
                                <p class="text-sm text-gray-400">Location: <?php echo $report['location_lat'] . ", " . $report['location_lng']; ?></p>
                            </div>
                            <div class="space-x-2">
                                <form method="POST" class="inline">
                                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                    <button type="submit" name="approve_report" class="bg-green-500 px-4 py-2 rounded-full text-white hover:bg-green-600">Approve</button>
                                </form>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                    <button type="submit" name="disapprove_report" class="bg-red-500 px-4 py-2 rounded-full text-white hover:bg-red-600">Disapprove</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-300">No pending reports.</p>
            <?php endif; ?>
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
        const sections = document.querySelectorAll('.scroll-section');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });
        sections.forEach(section => observer.observe(section));
    </script>
</body>
</html>

<?php $conn->close(); ?>