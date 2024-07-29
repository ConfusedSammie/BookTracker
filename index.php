<?php include 'login.php'; ?>
<?php
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query('SELECT * FROM books ORDER BY id DESC');
    $books = $stmt->fetchAll();
    $totalBooks = 24;
    $booksRead = count($books);
    $percent = round(($booksRead / $totalBooks) * 100);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

function generateStarRating($rating) {
    $fullStars = floor($rating);
    $halfStars = ($rating - $fullStars) * 2;

    $starOutput = str_repeat('⭐', $fullStars);
    if ($halfStars) {
        $starOutput .= '✨';
    }

    return $starOutput;
}
?>
<html>
    <head>
        <title>Sam's Book List</title>
    </head>

<div class='wholeContainer'>
    <div class='bookList'>
        <?php foreach($books as $book): ?>
        <div class='bookItem'>
            <div class='bookMonth <?= $book['Status'] == 'TBR' ? 'tbr' : '' ?>'>
                <?= htmlspecialchars($book['Month_Read'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class='bookImage'>
                <img src='<?= htmlspecialchars($book['image_url'], ENT_QUOTES, 'UTF-8') ?>'>
            </div>
            <div class='bookTitle'>
                <?= htmlspecialchars($book['Title'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class='bookAuthor'>
                <?= htmlspecialchars($book['Author'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <div class='bookRating'>
                <?= generateStarRating($book['Stars']) ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var width = <?= $percent; ?>;
    document.getElementById("myBar").style.width = width + '%';
});
</script>

<style>
body {
    margin: 0;
}

.wholeContainer {
    background-color: #023337;
    padding: 20px;
    color: white;
}

.bookList {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 16px;
    padding-top: 30px;
}

.bookItem {
    text-align: center;
}

.bookImage img {
    width: 100%;
    height: auto;
}

.bookImage {
    margin-bottom: 10px;
}

.bookTitle {
    font-size: 16px;
    font-weight: 700;
}

.bookAuthor {
    margin-bottom: 5px;
}

.bookMonth {
    background-color: #EDF4FF;
    color: #3F63C0;
    padding: 5px 10px;
    border-radius: 10px;
    font-weight: bold;
    display: inline-block;
    margin-bottom: 10px;
}

.bookMonth.tbr {
    background-color: red;
    color: white;
}

@media (max-width: 768px) {
    .bookList {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }

    .bookTitle {
        font-size: 14px;
    }

    .bookAuthor {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .bookList {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }

    .bookTitle {
        font-size: 12px;
    }

    .bookAuthor {
        font-size: 10px;
    }
}
</style>


</html>