<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "wildlife_db");
$threads_sql = "SELECT t.*, u.username FROM forum_threads t JOIN user u ON t.user_id = u.id ORDER BY t.date DESC";
$threads_result = $conn->query($threads_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - Forest & Wildlife Monitoring Dashboard</title>
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
    <video autoplay muted loop class="absolute top-0 left-0 w-full h-full object-cover z-0 video-bg" id="bgVideo">
        <source src="video.mp4" type="video/mp4">
    </video>
    <div class="absolute inset-0 bg-black opacity-60 z-2"></div>
    <div id="particles"></div>
    <div class="cursor-glow hidden md:block" id="cursorGlow"></div>

    <nav class="relative z-20 glass p-4 md:p-6 shadow-lg fixed w-full top-0">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
            <h1 class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 bg-clip-text text-transparent animate__animated animate__pulse animate__infinite">Forest & Wildlife Monitoring Dashboard</h1>
            <div class="mt-4 md:mt-0 space-x-4 md:space-x-8 text-base md:text-lg">
                <a href="index.html" class="neon-hover px-3 py-1 rounded-full hover:bg-green-500/20 transition">Home</a>
                <a href="logout.php" class="neon-hover px-3 py-1 rounded-full hover:bg-green-500/20 transition">Logout</a>
            </div>
        </div>
    </nav>

    <main class="relative z-20 container mx-auto pt-24 pb-20">
        <div class="glass p-6 md:p-10 rounded-xl">
            <h2 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent mb-6 md:mb-8">Community Forum</h2>
            <form action="process.php" method="POST" class="space-y-6 mb-10">
                <input type="text" name="title" placeholder="Thread Title" class="p-3 md:p-4 glass border-none rounded-full w-full placeholder-gray-400 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-green-500" required>
                <textarea name="message" placeholder="Message" class="p-3 md:p-4 glass border-none rounded-xl w-full placeholder-gray-400 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-green-500" rows="4" required></textarea>
                <select name="category" class="p-3 md:p-4 glass border-none rounded-full w-full text-gray-400 text-sm md:text-base focus:outline-none focus:ring-2 focus:ring-green-500" required>
                    <option value="" disabled selected>Select Category</option>
                    <option value="Wildlife">Wildlife</option>
                    <option value="Conservation Issues">Conservation Issues</option>
                </select>
                <button type="submit" name="submit_thread" class="bg-gradient-to-r from-green-400 to-blue-500 text-white p-3 md:p-4 rounded-full w-full neon-glow text-sm md:text-lg hover:bg-gradient-to-r hover:from-blue-500 hover:to-purple-600 transition">Post Thread</button>
            </form>

            <div class="space-y-6">
                <?php while ($thread = $threads_result->fetch_assoc()): ?>
                    <div class="glass p-4 rounded-lg">
                        <h3 class="text-xl md:text-2xl font-bold"><?php echo $thread["title"]; ?></h3>
                        <p class="text-sm md:text-base text-gray-300">By <?php echo $thread["username"]; ?> | <?php echo $thread["date"]; ?> | <?php echo $thread["category"]; ?></p>
                        <p class="mt-2"><?php echo $thread["message"]; ?></p>
                        <!-- Placeholder for replies -->
                        <a href="thread.php?id=<?php echo $thread["id"]; ?>" class="text-green-400 hover:underline mt-2 block">View Replies</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <footer class="relative z-20 glass py-10 md:py-16">
        <div class="container mx-auto text-center">
            <h3 class="text-2xl md:text-3xl font-bold bg-gradient-to-r from-green-400 via-blue-500 to-purple-600 bg-clip-text text-transparent mb-4 md:mb-6">Forest & Wildlife Monitoring Dashboard</h3>
            <p class="text-sm md:text-base text-gray-300">© 2025 | All Rights Reserved</p>
        </div>
    </footer>

    <script>
        // Particle Animation
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

        // Cursor Glow
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