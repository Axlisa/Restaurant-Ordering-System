<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "food_checkout";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch types of menu and their items from the database
$sql = "SELECT DISTINCT type FROM food_items";
$result = $conn->query($sql);

$types = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $type = $row['type'];
        $types[$type] = [];

        // Fetch items for each type of menu
        $item_sql = "SELECT * FROM food_items WHERE type='$type'";
        $item_result = $conn->query($item_sql);

        if ($item_result->num_rows > 0) {
            while ($item_row = $item_result->fetch_assoc()) {
                $types[$type][] = $item_row;
            }
        }
    }
}

// Check if there is previous data in the session
if (isset($_SESSION['order'])) {
    $name = $_SESSION['order']['name'];
    $phone = $_SESSION['order']['phone'];
    $previous_quantities = $_SESSION['order']['quantity'];
    $previous_drink_types = isset($_SESSION['order']['drink_type']) ? $_SESSION['order']['drink_type'] : [];
} else {
    $name = '';
    $phone = '';
    $previous_quantities = [];
    $previous_drink_types = [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $_SESSION['order']['name'] = $_POST['name'];
    $_SESSION['order']['phone'] = $_POST['phone'];
    $_SESSION['order']['quantity'] = $_POST['quantity'];
    $_SESSION['order']['drink_type'] = isset($_POST['drink_type']) ? $_POST['drink_type'] : [];
    header("Location: review.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Your Order</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <ul class="navbar">
        <li><a class="active" href="index.php">Home</a></li>
        <li class="right"><button id="toggle-theme">Toggle Theme</button></li>
    </ul>
    <div class="container">
        <h1>Place Your Order</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($name); ?>">

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" required value="<?php echo htmlspecialchars($phone); ?>">

            <div class="menu-section">
                <?php foreach ($types as $type => $items): ?>
                    <div class="menu-type">
                        <h2><?php echo $type; ?></h2>
                        <?php foreach ($items as $item): ?>
                            <label class="menu-item">
                                <span class="item-name"><?php echo $item['name']; ?></span>
                                <?php if ($type === 'Drink'): ?>
                                    <div class="drink-options">
                                        <?php if ($item['hot_price'] !== NULL): ?>
                                            <input type="radio" name="drink_type[<?php echo $item['id']; ?>]" 
                                            value="hot" <?php echo (isset($previous_drink_types[$item['id']]) 
                                            && $previous_drink_types[$item['id']] == 'hot') ? 'checked' : ''; ?>>
                                            <label for="drink_type[<?php echo $item['id']; ?>]">Hot RM<?php echo $item['hot_price']; ?></label><br>
                                        <?php endif; ?>
                                        <?php if ($item['cold_price'] !== NULL): ?>
                                            <input type="radio" name="drink_type[<?php echo $item['id']; ?>]" 
                                            value="cold" <?php echo (isset($previous_drink_types[$item['id']]) 
                                            && $previous_drink_types[$item['id']] == 'cold') ? 'checked' : ''; ?>>
                                            <label for="drink_type[<?php echo $item['id']; ?>]">Cold RM<?php echo $item['cold_price']; ?></label><br>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="item-price">RM<?php echo $item['price']; ?></span>
                                <?php endif; ?>
                                <input type="number" name="quantity[<?php echo $item['id']; ?>]" min="0" 
                                value="<?php echo isset($previous_quantities[$item['id']]) ? $previous_quantities[$item['id']] : 0; ?>">
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="submit" name="submit" value="Submit">
        </form>
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
