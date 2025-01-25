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

// Initialize counts for different types of menus
$appetizer_count = 0;
$main_course_count = 0;
$dessert_count = 0;
$beverage_count = 0;

foreach ($quantities as $item_id => $quantity) {
    if ($quantity > 0) {
        $sql = "SELECT * FROM food_items WHERE id=$item_id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
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

            // Update counts based on menu type
            switch ($row['type']) {
                case 'Appetizer':
                    $appetizer_count += $quantity;
                    break;
                case 'Main Course':
                    $main_course_count += $quantity;
                    break;
                case 'Dessert':
                    $dessert_count += $quantity;
                    break;
                case 'Drink':
                    $beverage_count += $quantity;
                    break;
            }
        }
    }
}

// Discount calculation
$discount_percent = 0;
if ($total_items > 5) {
    $discount_percent += 0.01; // Additional 1% discount for more than 5 items
}
$all_food_types_selected = true;
if ($appetizer_count == 0 || $main_course_count == 0 || $dessert_count == 0) {
    $all_food_types_selected = false;
}
if ($all_food_types_selected) {
    $discount_percent += 0.05; // 5% discount if all food types selected
}
$discount_amount = $total_amount * $discount_percent;

// Sales and Service Tax (SST) calculation
$sst_rate = 0.08;
$sst_amount = $total_amount * $sst_rate;

// Final amount calculation
$final_amount = $total_amount - $discount_amount + $sst_amount;

// Save order details to the database
$sql_insert = "INSERT INTO users (name, phone, appetizer, main_course, dessert, beverage, total_payment) 
               VALUES ('$name', '$phone', $appetizer_count, $main_course_count, $dessert_count, $beverage_count, $final_amount)";

if ($conn->query($sql_insert) === TRUE) {
    $order_saved_message = "Order saved successfully!";
    $_SESSION['order_saved'] = true;
    
    // Reset the order session data
    unset($_SESSION['order']);
} else {
    $order_saved_message = "Error: " . $sql_insert . "<br>" . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <ul class="navbar">
        <li><a class="active" href="index.php">Home</a></li>
        <li class="right"><button id="toggle-theme">Toggle Theme</button></li>
    </ul>
    <div class="container">
        <h1>Order Summary</h1>
        <h1 class="order-saved-message"><?php echo htmlspecialchars($order_saved_message); ?></h1>
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

                        echo "<li>" . htmlspecialchars($row['name']) . " x " 
                        . htmlspecialchars($quantity) . " - RM" 
                        . htmlspecialchars(number_format($quantity * $price, 2)) 
                        . "</li>";
                    }
                    ?>
                <?php endif; ?>
            <?php endforeach; 
            $conn->close();?>
        </ul>

        <h2>Calculation</h2>
        <p>Total Items Ordered: <?php echo htmlspecialchars($total_items); ?></p>
        <p>Total Amount: RM<?php echo number_format($total_amount, 2); ?></p>
        <p>Discount: RM<?php echo number_format($discount_amount, 2); ?></p>
        <p>Sales and Service Tax (8%): RM<?php echo number_format($sst_amount, 2); ?></p>
        <h3>Final Amount to Pay: RM<?php echo number_format($final_amount, 2); ?></h3>

        <a href="index.php" class="button">Back to Home</a>
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
