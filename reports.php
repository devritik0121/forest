<?php
session_start();
$conn = new mysqli("localhost", "root", "", "wildlife_db");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Check if user is logged in
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

// Fetch unique species from database
$species_list = $conn->query("SELECT DISTINCT species FROM reports WHERE species IS NOT NULL ORDER BY species ASC")->fetch_all(MYSQLI_ASSOC);

// Filters
$species = $_GET['species'] ?? '';
$lat = $_GET['lat'] ?? '';
$lng = $_GET['lng'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 4; // 4 reports per page as requested
$offset = ($page - 1) * $per_page;

// Build query (only approved reports)
$sql = "SELECT * FROM reports WHERE status = 'approved'";
$params = [];
$types = '';

// Filter by Species
if ($species) {
    $sql .= " AND species = ?";
    $params[] = $species;
    $types .= "s";
}

// Filter by Location (within a small range, e.g., ±0.5 degrees)
if ($lat && $lng) {
    $lat = floatval($lat);
    $lng = floatval($lng);
    $lat_min = $lat - 0.5;
    $lat_max = $lat + 0.5;
    $lng_min = $lng - 0.5;
    $lng_max = $lng + 0.5;
    $sql .= " AND location_lat BETWEEN ? AND ? AND location_lng BETWEEN ? AND ?";
    $params[] = $lat_min;
    $params[] = $lat_max;
    $params[] = $lng_min;
    $params[] = $lng_max;
    $types .= "dddd";
}

// Pagination
$count_sql = "SELECT COUNT(*) as total FROM ($sql) as temp";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total_reports = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_reports / $per_page);

$sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$stmt = $conn->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$reports = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Forest & Wildlife Monitoring</title>
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
    </style>
</head>
<body class="text-white relative min-h-screen">
    <!-- Header -->
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

    <!-- Reports Section -->
    <section class="relative z-20 container mx-auto py-20 pt-24 scroll-section">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-12">View Reports</h2>
        
        <!-- Filters -->
        <div class="glass p-6 rounded-xl neon-hover mb-12 mx-auto max-w-4xl">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-300 mb-2">Filter by Species</label>
                    <select name="species" class="input-field">
                        <option value="">Select Species</option>
                        <?php foreach ($species_list as $sp): ?>
                            <option value="<?php echo htmlspecialchars($sp['species']); ?>" <?php if ($species == $sp['species']) echo 'selected'; ?>><?php echo htmlspecialchars($sp['species']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-300 mb-2">Filter by Location</label>
                    <div class="flex space-x-4">
                        <input type="number" step="any" name="lat" value="<?php echo htmlspecialchars($lat); ?>" placeholder="Latitude (e.g., 22.0)" class="input-field w-1/2">
                        <input type="number" step="any" name="lng" value="<?php echo htmlspecialchars($lng); ?>" placeholder="Longitude (e.g., 88.0)" class="input-field w-1/2">
                    </div>
                </div>
                <button type="submit" class="bg-green-500 px-4 py-2 rounded-full text-white neon-glow mt-4 md:col-span-2 mx-auto block">Filter Reports</button>
            </form>
        </div>

        <!-- Reports List -->
        <div class="glass p-8 rounded-xl neon-hover mx-auto max-w-4xl">
            <h3 class="text-2xl font-bold mb-4">Approved Reports</h3>
            <?php if ($reports->num_rows > 0): ?>
                <div class="grid grid-cols-1 gap-4">
                    <?php while ($report = $reports->fetch_assoc()): ?>
                        <div class="bg-gray-800 p-4 rounded-lg flex items-center space-x-4">
                            <img src="uploads/<?php echo htmlspecialchars($report['photo']); ?>" alt="Report Image" class="w-24 h-24 object-cover rounded-lg" onerror="this.src='uploads/placeholder.jpg';">
                            <div>
                                <p class="text-gray-300"><?php echo htmlspecialchars($report['description']); ?></p>
                                <p class="text-sm text-gray-400">Species: <?php echo htmlspecialchars($report['species']); ?> | Location: <?php echo $report['location_lat'] . ", " . $report['location_lng']; ?></p>
                            </div>
                            <a href="report-details.php?id=<?php echo $report['id']; ?>" class="bg-blue-500 px-4 py-2 rounded-full text-white neon-hover ml-auto">View Details</a>
                        </div>
                    <?php endwhile; ?>
                </div>
                <!-- Pagination -->
                <div class="mt-6 flex justify-center space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&species=<?php echo urlencode($species); ?>&lat=<?php echo urlencode($lat); ?>&lng=<?php echo urlencode($lng); ?>" class="px-4 py-2 bg-green-500 rounded-full neon-hover">Previous</a>
                    <?php endif; ?>
                    <span class="px-4 py-2 text-gray-300">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&species=<?php echo urlencode($species); ?>&lat=<?php echo urlencode($lat); ?>&lng=<?php echo urlencode($lng); ?>" class="px-4 py-2 bg-green-500 rounded-full neon-hover">Next</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-300">No approved reports found.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
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