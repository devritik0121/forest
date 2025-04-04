<?php
session_start();

// Database Connection
$conn = new mysqli("localhost", "root", "", "wildlife_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch Recent Reports for Updates Section
$recent_reports_sql = "SELECT * FROM reports WHERE status = 'approved' ORDER BY id DESC LIMIT 3";
$recent_reports_result = $conn->query($recent_reports_sql);

// Check if user is logged in and fetch username and role
$logged_in = isset($_SESSION['user_id']);
$username = '';
$role = '';
if ($logged_in) {
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT username, role FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $username = htmlspecialchars($row['username']);
        $role = htmlspecialchars($row['role']); // Role fetch karo
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forest & Wildlife Monitoring Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        body { background: linear-gradient(135deg, #0a0f1a 0%, #1a2a44 100%); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .neon-glow { box-shadow: 0 0 25px rgba(34, 197, 94, 0.8), 0 0 50px rgba(34, 197, 94, 0.4); }
        .neon-hover:hover { box-shadow: 0 0 30px rgba(34, 197, 94, 0.9); transform: scale(1.05); transition: all 0.4s ease; }
        .hero-bg { background: url('https://images.unsplash.com/photo-1501854140801-50d01698950b?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80') no-repeat center/cover; }
        .scroll-section { opacity: 0; transform: translateY(60px); transition: opacity 0.8s ease, transform 0.8s ease; }
        .scroll-section.visible { opacity: 1; transform: translateY(0); }
        #map { height: 400px; }
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
                    <!-- Username ke saath role bracket mein -->
                    <a href="<?php echo $role === 'admin' ? 'admin-dashboard.php' : 'dashboard.php'; ?>" class="px-4 py-2 text-green-400 hover:bg-green-500/20 rounded-full transition"><?php echo $username . " (" . ucfirst($role) . ")"; ?></a>
                    <a href="logout.php" class="bg-red-500 px-4 py-2 rounded-full neon-glow nav-btn">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="bg-green-500 px-4 py-2 rounded-full neon-glow nav-btn">Login</a>
                    <a href="register.php" class="bg-blue-500 px-4 py-2 rounded-full neon-glow nav-btn">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- 2. Hero Section -->
    <section class="relative z-20 min-h-screen flex items-center justify-center pt-24 hero-bg">
        <div class="absolute inset-0 bg-black opacity-50 z-0"></div>
        <div class="text-center animate__animated animate__fadeIn relative z-10">
            <h1 class="text-5xl md:text-7xl font-bold bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-6">Protecting Forests & Wildlife for a Greener Future</h1>
            <p class="text-lg md:text-2xl text-gray-300 mb-8">Join us in conserving nature and monitoring wildlife in real-time.</p>
            <div class="space-x-4">
                <a href="register.php" class="bg-gradient-to-r from-green-400 to-blue-500 text-white px-6 py-3 rounded-full neon-glow text-lg hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-600">Register Now</a>
                <a href="reports.php" class="bg-transparent border-2 border-green-500 text-green-500 px-6 py-3 rounded-full neon-hover text-lg hover:bg-green-500 hover:text-white">Explore Reports</a>
            </div>
        </div>
    </section>

    <!-- 3. Key Highlights -->
    <section class="relative z-20 container mx-auto py-20 scroll-section" id="highlights">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-12">Why This Platform?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">🌳</span>
                <h3 class="text-2xl font-bold mt-4">Track Conservation</h3>
                <p class="text-gray-300 mt-2">Live tracking of conservation areas.</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">🦅</span>
                <h3 class="text-2xl font-bold mt-4">Monitor Wildlife</h3>
                <p class="text-gray-300 mt-2">Real-time updates on endangered species.</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">🛠</span>
                <h3 class="text-2xl font-bold mt-4">Collaborate</h3>
                <p class="text-gray-300 mt-2">Work with researchers & NGOs.</p>
            </div>
        </div>
    </section>

    <!-- 4. Recent Updates Section -->
    <section class="relative z-20 container mx-auto py-20 scroll-section" id="updates">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-12">Recent Updates</h2>
        <?php if ($recent_reports_result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php while ($report = $recent_reports_result->fetch_assoc()): ?>
                    <div class="glass p-6 rounded-xl neon-hover">
                        <img src="uploads/<?php echo htmlspecialchars($report['photo']); ?>" alt="Report Image" class="w-full h-48 object-cover rounded-t-xl" onerror="this.src='uploads/placeholder.jpg';">
                        <h3 class="text-xl font-bold mt-4"><?php echo htmlspecialchars($report['description']); ?></h3>
                        <p class="text-gray-300 mt-2">Location: <?php echo $report['location_lat'] . ", " . $report['location_lng']; ?></p>
                        <a href="report-details.php?id=<?php echo $report['id']; ?>" class="text-green-400 mt-4 inline-block hover:underline">Read More</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-300">No recent reports available.</p>
        <?php endif; ?>
    </section>

    <!-- 5. Interactive Map Section -->
    <section class="relative z-20 container mx-auto py-20 scroll-section" id="map-section">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-12">Explore Conservation Areas</h2>
        <div id="map" class="w-full h-96 rounded-xl glass"></div>
        <div class="mt-6 flex justify-center space-x-4">
            <select id="regionFilter" class="glass p-2 rounded-full text-gray-300">
                <option value="">Forest Region</option>
                <option value="Amazon">Amazon</option>
                <option value="Sundarbans">Sundarbans</option>
                <option value="Kaziranga">Kaziranga</option>
                <option value="Serengeti">Serengeti</option>
            </select>
            <select id="speciesFilter" class="glass p-2 rounded-full text-gray-300">
                <option value="">Wildlife Species</option>
                <option value="Tiger">Tiger</option>
                <option value="Elephant">Elephant</option>
                <option value="Giraffe">Giraffe</option>
            </select>
            <select id="statusFilter" class="glass p-2 rounded-full text-gray-300">
                <option value="">Conservation Status</option>
                <option value="Endangered">Endangered</option>
                <option value="Safe">Safe</option>
            </select>
        </div>
    </section>

    <!-- 6. How It Works Section -->
    <section class="relative z-20 container mx-auto py-20 scroll-section" id="how-it-works">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-12">How It Works?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">1️⃣</span>
                <h3 class="text-2xl font-bold mt-4">Sign Up</h3>
                <p class="text-gray-300 mt-2">Register as a Researcher or Conservationist.</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">2️⃣</span>
                <h3 class="text-2xl font-bold mt-4">Submit Report</h3>
                <p class="text-gray-300 mt-2">Share wildlife and conservation data.</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover text-center">
                <span class="text-5xl">3️⃣</span>
                <h3 class="text-2xl font-bold mt-4">Track Progress</h3>
                <p class="text-gray-300 mt-2">Monitor reports and impact.</p>
            </div>
        </div>
    </section>

    <!-- 7. Testimonials Section -->
    <section class="relative z-20 container mx-auto py-20 scroll-section" id="testimonials">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-12">Success Stories</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="glass p-6 rounded-xl neon-hover">
                <p class="text-lg text-gray-300">"This platform helped us save 100+ endangered species!"</p>
                <p class="mt-4 font-bold text-green-400">– NGO "Ek vichitra Pehl"</p>
            </div>
            <div class="glass p-6 rounded-xl neon-hover">
                <p class="text-lg text-gray-300">"Real-time data tracking improved our conservation efforts!"</p>
                <p class="mt-4 font-bold text-green-400">– Researcher "Aishwary Mishra"</p>
            </div>
        </div>
    </section>

    <!-- 8. Newsletter Subscription -->
    <section class="relative z-20 container mx-auto py-20 scroll-section" id="newsletter">
        <h2 class="text-4xl font-bold text-center bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-12">Stay Updated</h2>
        <form class="max-w-md mx-auto flex space-x-4">
            <input type="email" placeholder="Enter your email" class="p-3 glass rounded-full w-full text-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500">
            <button type="submit" class="bg-green-500 px-6 py-3 rounded-full neon-glow text-white">Subscribe</button>
        </form>
    </section>

    <!-- 9. Footer -->
    <footer class="relative z-20 glass py-16 scroll-section" id="footer">
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
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

        // OpenStreetMap Initialization
        const map = L.map('map').setView([21.0, 78.0], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let markers = []; // To store markers

        // Region coordinates
        const regions = {
            'Amazon': [-3.4653, -62.2159],
            'Sundarbans': [22.0, 88.0],
            'Kaziranga': [26.5, 93.0],
            'Serengeti': [-2.5, 34.5]
        };

        function updateMap() {
            const region = document.getElementById('regionFilter').value;
            const species = document.getElementById('speciesFilter').value;
            const status = document.getElementById('statusFilter').value;

            // Clear existing markers
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            // Set map view based on region
            if (region && regions[region]) {
                map.setView(regions[region], 8);
            } else {
                map.setView([21.0, 78.0], 5); // Default view if no region selected
            }

            // Fetch filtered reports
            fetch(`get_reports.php?species=${species}&status=${status}&region=${region}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(report => {
                        const marker = L.marker([report.location_lat, report.location_lng])
                            .addTo(map)
                            .bindPopup(`${report.description} (${report.species} - ${report.status})`);
                        markers.push(marker);
                    });
                })
                .catch(error => console.error('Error fetching reports:', error));
        }

        // Event listeners for dropdowns
        document.getElementById('regionFilter').addEventListener('change', updateMap);
        document.getElementById('speciesFilter').addEventListener('change', updateMap);
        document.getElementById('statusFilter').addEventListener('change', updateMap);

        // Initial map load
        updateMap();
    </script>
</body>
</html>

<?php $conn->close(); ?>