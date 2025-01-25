<?php
session_start();

// Check if the order details are available in the session
if (!isset($_SESSION['order'])) {
    header('Location: order.php');
    exit;
}

$name = $_SESSION['order']['name'];
$phone = $_SESSION['order']['phone'];
$quantities = $_SESSION['order']['quantity'];
$drink_types = isset($_SESSION['order']['drink_type']) ? $_SESSION['order']['drink_type'] : [];

$total_items = 0;
$total_amount = 0;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "food_checkout";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Review</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <ul class="navbar">
        <li><a class="active" href="index.php">Home</a></li>
        <li class="right"><button id="toggle-theme">Toggle Theme</button></li>
    </ul>
    <div class="container">
        <h1>Order Review</h1>
        <p>Name: <?php echo htmlspecialchars($name); ?></p>
        <p>Phone: <?php echo htmlspecialchars($phone); ?></p>

        <h2>Your Order</h2>
        <ul>
            <?php foreach ($quantities as $item_id => $quantity): ?>
                <?php if ($quantity > 0): ?>
                    <?php
                    $sql = "SELECT * FROM food_items WHERE id=$item_id";
                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        // Get the price based on the selected drink type
                        if ($row['type'] === 'Drink' && isset($drink_types[$item_id])) {
                            $drink_type = $drink_types[$item_id];
                            switch ($drink_type) {
                                case 'hot':
                                    $price = $row['hot_price'];
                                    break;
                                case 'cold':
                                    $price = $row['cold_price'];
                                    break;
                                default:
                                    $price = $row['price'];
                            }
                        } else {
                            $price = $row['price'];
                        }

                        $total_items += $quantity;
                        $total_amount += $quantity * $price;
                        echo "<li>" . htmlspecialchars($row['name']) . " x " . htmlspecialchars($quantity) .
                         " - RM" . htmlspecialchars(number_format($quantity * $price, 2)) . "</li>";
                    }
                    ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>

        <h2>Total Amount</h2>
        <p>Total Items Ordered: <?php echo htmlspecialchars($total_items); ?></p>
        <p>Total Amount: RM<?php echo number_format($total_amount, 2); ?></p>

        <a href="order.php" class="button">Edit Order</a>
        <a href="summary.php" class="button">Proceed to Summary</a>
    </div>
    <script>
        // Load theme from cookie
        const currentTheme = getCookie('theme');
        if (currentTheme) {
            document.body.classList.add(currentTheme);
        }

        const toggleButton = document.getElementById('toggle-theme');
        toggleButton.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');
            let theme = 'light-mode';
            if (document.body.classList.contains('dark-mode')) {
                theme = 'dark-mode';
            }
            setCookie('theme', theme, 365);
        });

        // Function to set cookie
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/";
        }

        // Function to get cookie
        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }
    </script>
</body>
</html>
