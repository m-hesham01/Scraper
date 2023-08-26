This is a simple PHP Laravel web scraper that scrapes data about books on the website Kotobati.

################################
Running Instructions:
################################
1- Place the laravel-scraper folder in the xampp/htdocs directory
2- Make sure that XAMPP is active and that Apache & MySQL services are Running
3- Access the URL localhost/laravel-scraper/public/scraper from your browser

################################
Notes:
################################
- The scraper script is located here:
    laravel-scraper\app\Http\Controllers\ScraperController.php
- Due to the absence of my database table on your end, here's the MySQL code to replicate it
    CREATE TABLE `scraping` (
    `id` int(11) NOT NULL,
    `book_name` varchar(50) NOT NULL,
    `book_author` varchar(50) NOT NULL,
    `language` varchar(15) NOT NULL,
    `book_size` varchar(15) NOT NULL,
    `pages_count` varchar(10) NOT NULL,
    `pdf` varchar(50) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
- Make sure to edit the database credentials appropriately in the script, included in lines 13-16