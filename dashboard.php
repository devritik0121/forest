<?php
session_start();
$conn = new mysqli("localhost", "root", "", "wildlife_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$username = htmlspecialchars($user['username']);

// Profile Update
if (isset($_POST['update_profile'])) {
    $new_username = htmlspecialchars($_POST['username']);
    $update_sql = "UPDATE users SET username = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_username, $user_id);
    if ($stmt->execute()) {
        $username = $new_username;
        $profile_message = "Profile updated successfully!";
    } else {
        $profile_message = "Failed to update profile.";
    }
}

// Report Submission with Photo Upload
if (isset($_POST['submit_report'])) {
    $description = htmlspecialchars($_POST['description']);
    $location_lat = floatval($_POST['location_lat']);
    $location_lng = floatval($_POST['location_lng']);
    $species = htmlspecialchars($_POST['species']);

    // Image upload logic
    $photo = '';
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true); // Create uploads folder if not exists
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo = $target_dir . basename($_FILES['photo']['name']);
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $photo)) {
            $photo = basename($_FILES['photo']['name']); // Store only filename in DB
        } else {
            $photo = 'placeholder.jpg'; // Fallback if upload fails
        }
    } else {
        $photo = 'placeholder.jpg'; // Default if no photo uploaded
    }

    $report_sql = "INSERT INTO reports (user_id, photo, description, location_lat, location_lng, species, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($report_sql);
    $stmt->bind_param("isssds", $user_id, $photo, $description, $location_lat, $location_lng, $species);
    if ($stmt->execute()) {
        $report_message = "Report submitted successfully! Awaiting approval.";
    } else {
        $report_message = "Failed to submit report.";
    }
}

// Fetch Recent Updates
$updates_sql = "SELECT * FROM reports WHERE status = 'approved' ORDER BY id DESC LIMIT 3";
$updates_result = $conn->query($updates_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Forest & Wildlife Monitoring</title>
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
        .input-field { background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(34, 197, 94, 0.5); padding: 12px; border-radius: 10px; color: white; width: 100%; transition: border-color 0.3s ease, box-shadow 0.3s ease; }
        .input-field:focus { border-color: #22c55e; box-shadow: 0 0 10px rgba(34, 197, 94, 0.8); outline: none; background: rgba(255, 255, 255, 0.2); }
        .submit-btn { background: linear-gradient(90deg, #22c55e, #1e40af); padding: 12px; border-radius: 10px; font-weight: bold; transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .submit-btn:hover { transform: scale(1.05); box-shadow: 0 0 20px rgba(34, 197, 94, 0.8); }
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
                <span class="px-4 py-2 text-green-400"><?php echo $username; ?></span>
                <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow nav-btn">Logout</a>
            </div>
        </div>
    </nav>

    <!-- 2. Dashboard Section -->
    <section class="relative z-20 container mx-auto py-20 pt-24 scroll-section">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-12">User Dashboard</h2>
        
        <!-- Profile Update -->
        <div class="glass p-8 rounded-xl neon-hover mb-12 mx-auto max-w-lg">
            <h3 class="text-2xl font-bold mb-4">Update Profile</h3>
            <?php if (isset($profile_message)): ?>
                <p class="text-green-400 mb-4"><?php echo $profile_message; ?></p>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Username</label>
                    <input type="text" name="username" value="<?php echo $username; ?>" class="input-field" required>
                </div>
                <button type="submit" name="update_profile" class="submit-btn w-full text-white">Update</button>
            </form>
        </div>

        <!-- Submit Report -->
        <div class="glass p-8 rounded-xl neon-hover mb-12 mx-auto max-w-lg">
            <h3 class="text-2xl font-bold mb-4">Submit a Report</h3>
            <?php if (isset($report_message)): ?>
                <p class="text-green-400 mb-4"><?php echo $report_message; ?></p>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Description</label>
                    <input type="text" name="description" placeholder="e.g., Animal spotted" class="input-field" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Species</label>
                    <input type="text" name="species" placeholder="e.g., Tiger" class="input-field" required>
                </div>
                <div class="mb-6 flex space-x-4">
                    <div class="w-1/2">
                        <label class="block text-gray-300 mb-2">Latitude</label>
                        <input type="number" step="any" name="location_lat" placeholder="e.g., 22.0" class="input-field" required>
                    </div>
                    <div class="w-1/2">
                        <label class="block text-gray-300 mb-2">Longitude</label>
                        <input type="number" step="any" name="location_lng" placeholder="e.g., 88.0" class="input-field" required>
                    </div>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-300 mb-2">Upload Photo</label>
                    <input type="file" name="photo" class="input-field" accept="image/*" required>
                </div>
                <button type="submit" name="submit_report" class="submit-btn w-full text-white">Submit Report</button>
            </form>
        </div>

        <!-- Recent Updates -->
        <div class="glass p-8 rounded-xl neon-hover mx-auto max-w-3xl">
            <h3 class="text-2xl font-bold mb-4">Recent Conservation Updates</h3>
            <?php if ($updates_result->num_rows > 0): ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php while ($report = $updates_result->fetch_assoc()): ?>
                        <div class="bg-gray-800 p-4 rounded-lg flex items-center space-x-4">
                            <img src="uploads/<?php echo htmlspecialchars($report['photo']); ?>" alt="Report Image" class="w-24 h-24 object-cover rounded-lg" onerror="this.src='uploads/placeholder.jpg';">
                            <div>
                                <p class="text-gray-300"><?php echo htmlspecialchars($report['description']); ?> (<?php echo $report['species']; ?>)</p>
                                <p class="text-sm text-gray-400">Location: <?php echo $report['location_lat'] . ", " . $report['location_lng']; ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-300">No recent updates available.</p>
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