<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: login.php");
    exit();
}

// Database Connection
$conn = new mysqli("localhost", "root", "", "wildlife_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch pending users
$pending_sql = "SELECT id, username, email, role, first_name, last_name FROM user WHERE approved = 0";
$pending_result = $conn->query($pending_sql);

// Fetch all users (for removal)
$users_sql = "SELECT id, username, email, role FROM user WHERE approved = 1";
$users_result = $conn->query($users_sql);

// Placeholder for reports (assuming a reports table exists, adjust later)
$reports_sql = "SELECT * FROM reports"; // Yeh table abhi nahi hai, placeholder hai
$reports_result = $conn->query($reports_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Forest & Wildlife Monitoring Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { background: #0a0f1a; overflow-x: hidden; font-family: 'Orbitron', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .neon-glow { box-shadow: 0 0 25px rgba(34, 197, 94, 0.8), 0 0 50px rgba(34, 197, 94, 0.4); }
        .neon-hover:hover { box-shadow: 0 0 30px rgba(34, 197, 94, 0.9); transform: scale(1.05); transition: all 0.4s ease; }
        .video-bg { filter: brightness(0.7); transition: filter 0.5s ease; }
        #particles { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 5; pointer-events: none; }
        .particle { position: absolute; border-radius: 50%; background: rgba(34, 197, 94, 0.6); animation: float 10s infinite ease-in-out; }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }
        .cursor-glow { position: absolute; width: 100px; height: 100px; background: radial-gradient(circle, rgba(34, 197, 94, 0.5), transparent); pointer-events: none; z-index: 10; transform: translate(-50%, -50%); }
    </style>
</head>
<body class="text-white relative min-h-screen">
    <!-- Background Video -->
    <video autoplay muted loop class="absolute top-0 left-0 w-full h-full object-cover z-0 video-bg" id="bgVideo">
        <source src="video.mp4" type="video/mp4">
    </video>
    <div class="absolute inset-0 bg-black opacity-60 z-2"></div>

    <!-- Particle Container -->
    <div id="particles"></div>

    <!-- Cursor Glow -->
    <div class="cursor-glow hidden md:block" id="cursorGlow"></div>

    <!-- Navbar -->
    <nav class="relative z-20 glass p-4 md:p-6 shadow-lg fixed w-full top-0">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <h1 class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 bg-clip-text text-transparent animate__animated animate__pulse animate__infinite">Admin Panel</h1>
            <div class="mt-4 md:mt-0 space-x-4 md:space-x-8 text-base md:text-lg">
                <a href="index.html" class="neon-hover px-3 py-1 rounded-full hover:bg-green-500/20 transition">Home</a>
                <a href="logout.php" class="neon-hover px-3 py-1 rounded-full hover:bg-green-500/20 transition">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Admin Panel Section -->
    <main class="relative z-20 container mx-auto pt-24 pb-20">
        <!-- Pending Users -->
        <section class="glass p-6 md:p-10 rounded-xl mb-10">
            <h2 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-6 md:mb-8">Pending User Registrations</h2>
            <?php if ($pending_result->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while ($row = $pending_result->fetch_assoc()): ?>
                        <div class="glass p-4 rounded-lg flex justify-between items-center">
                            <div>
                                <p class="text-lg md:text-xl"><span class="font-bold">Username:</span> <?php echo $row["username"]; ?></p>
                                <p class="text-sm md:text-base"><span class="font-bold">Email:</span> <?php echo $row["email"]; ?></p>
                                <p class="text-sm md:text-base"><span class="font-bold">Name:</span> <?php echo $row["first_name"] . " " . $row["last_name"]; ?></p>
                                <p class="text-sm md:text-base"><span class="font-bold">Role:</span> <?php echo ucfirst($row["role"]); ?></p>
                            </div>
                            <div class="space-x-4">
                                <form action="process.php" method="POST" class="inline">
                                    <input type="hidden" name="user_id" value="<?php echo $row["id"]; ?>">
                                    <button type="submit" name="approve" class="bg-green-500 text-white p-2 rounded-full neon-glow hover:bg-green-600 transition">Approve</button>
                                </form>
                                <form action="process.php" method="POST" class="inline">
                                    <input type="hidden" name="user_id" value="<?php echo $row["id"]; ?>">
                                    <button type="submit" name="reject" class="bg-red-500 text-white p-2 rounded-full neon-glow hover:bg-red-600 transition">Reject</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-lg md:text-xl text-gray-300">No pending registrations.</p>
            <?php endif; ?>
        </section>

        <!-- Remove Fake Users -->
        <section class="glass p-6 md:p-10 rounded-xl mb-10">
            <h2 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-6 md:mb-8">Manage Users</h2>
            <?php if ($users_result->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while ($row = $users_result->fetch_assoc()): ?>
                        <div class="glass p-4 rounded-lg flex justify-between items-center">
                            <div>
                                <p class="text-lg md:text-xl"><span class="font-bold">Username:</span> <?php echo $row["username"]; ?></p>
                                <p class="text-sm md:text-base"><span class="font-bold">Email:</span> <?php echo $row["email"]; ?></p>
                                <p class="text-sm md:text-base"><span class="font-bold">Role:</span> <?php echo ucfirst($row["role"]); ?></p>
                            </div>
                            <form action="process.php" method="POST" class="inline">
                                <input type="hidden" name="user_id" value="<?php echo $row["id"]; ?>">
                                <button type="submit" name="delete" class="bg-red-500 text-white p-2 rounded-full neon-glow hover:bg-red-600 transition">Delete</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-lg md:text-xl text-gray-300">No approved users found.</p>
            <?php endif; ?>
        </section>

        <!-- Reports Overview (Placeholder) -->
        <section class="glass p-6 md:p-10 rounded-xl">
            <h2 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-6 md:mb-8">Reports Overview</h2>
            <p class="text-lg md:text-xl text-gray-300">Reports section coming soon! (Add a `reports` table to enable this.)</p>
            <!-- Future: Add table for reports with columns like id, title, location, date -->
        </section>
    </main>

    <!-- Footer -->
    <footer class="relative z-20 glass py-10 md:py-16">
        <div class="container mx-auto text-center">
            <h3 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 bg-clip-text text-transparent mb-4 md:mb-6">Forest & Wildlife Monitoring Dashboard</h3>
            <p class="text-sm md:text-base text-gray-300">© 2025 | All Rights Reserved</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        const particleContainer = document.getElementById("particles");
        for (let i = 0; i < 50; i++) {
            const particle = document.createElement("div");
            particle.className = "particle";
            particle.style.width = `${Math.random() * 10 + 5}px`;
            particle.style.height = particle.style.width;
            particle.style.left = `${Math.random() * 100}vw`;
            particle.style.top = `${Math.random() * 100}vh`;
            particle.style.animationDuration = `${Math.random() * 5 + 5}s`;
            particle.style.animationDelay = `${Math.random() * 5}s`;
            particleContainer.appendChild(particle);
        }

        const video = document.getElementById("bgVideo");
        const cursorGlow = document.getElementById("cursorGlow");
        document.addEventListener("mousemove", (e) => {
            cursorGlow.style.left = `${e.pageX}px`;
            cursorGlow.style.top = `${e.pageY}px`;
            const hue = (e.pageX / window.innerWidth) * 360;
            video.style.filter = `hue-rotate(${hue}deg) brightness(0.7)`;
        });
        document.addEventListener("mouseleave", () => {
            cursorGlow.style.display = "none";
            video.style.filter = "brightness(0.7)";
        });
        document.addEventListener("mouseenter", () => {
            cursorGlow.style.display = "block";
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>