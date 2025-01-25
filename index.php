<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Ordering System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <ul class="navbar">
        <li><a class="active" href="index.php">Home</a></li>
        <li class="right"><button id="toggle-theme">Toggle Theme</button></li>
    </ul>
    <div class="container">
        <h1>Welcome to Our Restaurant</h1>
        <a href="order.php" class="button">Place Your Order</a>
    </div>
    <script>
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

        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days*24*60*60*1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "")  + expires + "; path=/";
        }

        function getCookie(name) {
            const nameEQ = name + "=";
            const ca = document.cookie.split(';');
            for(let i=0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') c = c.substring(1,c.length);
                if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
            }
            return null;
        }
    </script>
</body>
</html>
