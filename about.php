<?php
session_start();
$conn = new mysqli("localhost", "root", "", "wildlife_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    <title>About Us - Forest & Wildlife Monitoring</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0a0f1a 0%, #1a2a44 100%); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .neon-glow { box-shadow: 0 0 25px rgba(34, 197, 94, 0.8), 0 0 50px rgba(34, 197, 94, 0.4); }
        .neon-hover:hover { box-shadow: 0 0 30px rgba(34, 197, 94, 0.9); transform: scale(1.05); transition: all 0.4s ease; }
        .about-hero { background: url('https://images.unsplash.com/photo-1506748686214-e9df14d4d9d0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') no-repeat center/cover; }
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

    <!-- 2. Hero Section -->
    <section class="relative z-20 min-h-screen flex items-center justify-center pt-24 about-hero">
        <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
        <div class="text-center animate__animated animate__fadeIn relative z-10">
            <h1 class="text-5xl md:text-7xl font-bold bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-6">About Us</h1>
            <p class="text-lg md:text-2xl text-gray-300 mb-8">Empowering conservation through technology and community action.</p>
        </div>
    </section>

    <!-- 3. Our Mission Section -->
    <section class="relative z-20 container mx-auto py-20 scroll-section">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-12">Our Mission</h2>
        <div class="glass p-8 rounded-xl neon-hover mx-auto max-w-3xl">
            <p class="text-gray-300 text-lg">We are dedicated to protecting forests and wildlife by leveraging real-time data, cutting-edge technology, and collaboration with conservationists worldwide. Our goal is to create a sustainable future where nature thrives alongside humanity.</p>
        </div>
    </section>

    <!-- 4. Our Team Section -->
    <section class="relative z-20 container mx-auto py-20 scroll-section">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-12">Our Team</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <img src="https://via.placeholder.com/150" alt="Team Member" class="w-32 h-32 rounded-full mx-auto mb-4">
                <h3 class="text-xl font-bold">Rohit Sharma</h3>
                <p class="text-gray-300">Founder & Conservationist</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <img src="https://via.placeholder.com/150" alt="Team Member" class="w-32 h-32 rounded-full mx-auto mb-4">
                <h3 class="text-xl font-bold">Priya Singh</h3>
                <p class="text-gray-300">Wildlife Researcher</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <img src="https://via.placeholder.com/150" alt="Team Member" class="w-32 h-32 rounded-full mx-auto mb-4">
                <h3 class="text-xl font-bold">Amit Patel</h3>
                <p class="text-gray-300">Tech Lead</p>
            </div>
        </div>
    </section>

    <!-- 5. Footer -->
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
        // Scroll Animation
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