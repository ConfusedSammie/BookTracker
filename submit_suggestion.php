<?php
include 'login.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        $stmt = $pdo->prepare("INSERT INTO suggestions (name, book_id, book_title, book_author, suggestion) VALUES (:name, :book_id, :book_title, :book_author, :suggestion)");

        $stmt->bindParam(':name', $_POST['name']);
        $stmt->bindParam(':book_id', $_POST['bookId']);
        $stmt->bindParam(':book_title', $_POST['bookTitle']);
        $stmt->bindParam(':book_author', $_POST['bookAuthor']);
        $stmt->bindParam(':suggestion', $_POST['suggestion']);

        $stmt->execute();

        echo "Suggestion submitted successfully!";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
