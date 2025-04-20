<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'User') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Colour Says Mood</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg,rgb(255, 193, 7),rgb(222, 227, 152));
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        .container {
            background: #fff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 600px;
            text-align: center;
            transform: scale(1);
            transition: transform 0.3s ease;
        }

        .container:hover {
            transform: scale(1.05);
        }

        h1 {
            color: #333;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            font-weight: 600;
        }

        input[type="color"] {
            width: 120px;
            height: 120px;
            border: none;
            cursor: pointer;
            border-radius: 50%;
            transition: transform 0.3s ease;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        input[type="color"]:hover {
            transform: scale(1.1);
        }

        .mood-display {
            font-size: 1.8rem;
            font-weight: 600;
            margin-top: 1rem;
            color: #444;
            transition: color 0.3s ease;
        }

        .back-button {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.8rem 2rem;
            background-color:rgb(219, 172, 52);
            color: #fff;
            text-decoration: none;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .back-button:hover {
            background-color:rgb(185, 161, 41);
            transform: scale(1.05);
        }

        .back-button:focus {
            outline: none;
            box-shadow: 0 0 0 4px rgba(219, 208, 52, 0.3);
        }

        @media (max-width: 768px) {
            .container {
                width: 85%;
                padding: 1.5rem;
            }

            h1 {
                font-size: 1.6rem;
            }

            .mood-display {
                font-size: 1.4rem;
            }

            .back-button {
                padding: 0.7rem 1.8rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pick a Colour to Reveal Your Mood</h1>
        <input type="color" id="colorPicker">
        <div class="mood-display" id="moodText">Your mood will appear here 😊</div>

        <a href="user_dashboard.php" class="back-button">← Back to Dashboard</a>
    </div>

    <script>
        const colorPicker = document.getElementById("colorPicker");
        const moodText = document.getElementById("moodText");

        function getMoodByColor(hex) {
            const simpleHex = hex.toLowerCase();
            const r = parseInt(hex.substring(1, 3), 16);
            const g = parseInt(hex.substring(3, 5), 16);
            const b = parseInt(hex.substring(5, 7), 16);

            // Mood estimation by dominant color combinations
            if (r > 200 && g < 100 && b < 100) return "Energetic 🔥";           // Strong Red
            if (r > 200 && g > 150 && b < 100) return "Optimistic 🌞";         // Orange-ish
            if (r > 200 && g > 200 && b < 150) return "Cheerful 😊";           // Yellowish
            if (g > r && g > b) return "Peaceful 🌿";                          // Green-dominant
            if (b > r && b > g) {
                if (b > 200 && r < 100) return "Sad 😢";                      // Soft Blue
                return "Calm 🌊";                                             // Deep Blue
            }
            if (r > 150 && b > 150 && g < 100) return "Loving 💜";             // Magenta/Violet
            if (r < 80 && g < 80 && b < 80) return "Moody 😎";                 // Very dark = blackish
            if (r > 180 && g > 180 && b > 180) return "Happy 😄";              // Very light = near white
            if (r === g && g === b) return "Neutral 😐";                       // Grayscale values

            // fallback approximate mood
            if (r > g && r > b) return "Passionate ❤️";
            if (g > r && g > b) return "Natural 🍃";
            if (b > r && b > g) return "Dreamy 🌌";

            return "Reflective 🤍"; // Ultimate fallback (rare case)
        }

        colorPicker.addEventListener("input", () => {
            const selectedColor = colorPicker.value;
            const mood = getMoodByColor(selectedColor);
            moodText.textContent = `Mood: ${mood}`;
        });
    </script>
</body>
</html>

