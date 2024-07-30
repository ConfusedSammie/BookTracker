<?php include 'login.php'; ?>
<?php
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $stmt = $pdo->query('SELECT * FROM books ORDER BY id DESC');
    $books = $stmt->fetchAll();

    // Extract unique years
    $years = [];
    foreach ($books as $book) {
        $year = date('Y', strtotime($book['Month_Read']));
        if (!in_array($year, $years)) {
            $years[] = $year;
        }
    }
    sort($years);
} 
catch (PDOException $e) {
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

    <div id="bookModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="bookDetails"></div>
        </div>
    </div>




    <div class='filter'>
        <label for="yearFilter">Filter by Year:</label>
        <select id="yearFilter">
            <option value="">All Years</option>
            <?php foreach ($years as $year): ?>
                <option value="<?= $year ?>"><?= $year ?></option>
            <?php endforeach; ?>
        </select>
        &emsp;
        <label for="statusFilter">Filter by Status:</label>
        <select id="statusFilter">
            <option value="">All Statuses</option>
            <option value="Done">Read</option>
            <option value="TBR">To Be Read</option>
        </select>
    </div>


    <div class='bookList'>
        <?php foreach($books as $book): ?>
            <div class='bookItem' data-title="<?= htmlspecialchars($book['Title'], ENT_QUOTES, 'UTF-8') ?>" data-author="<?= htmlspecialchars($book['Author'], ENT_QUOTES, 'UTF-8') ?>" data-year="<?= date('Y', strtotime($book['Month_Read'])) ?>" data-status="<?= htmlspecialchars($book['Status'], ENT_QUOTES, 'UTF-8') ?>">




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
    var yearFilter = document.getElementById("yearFilter");
    var statusFilter = document.getElementById("statusFilter");

    function filterBooks() {
        var selectedYear = yearFilter.value;
        var selectedStatus = statusFilter.value;
        var bookItems = document.querySelectorAll(".bookItem");

        bookItems.forEach(function(item) {
            var itemYear = item.getAttribute("data-year");
            var itemStatus = item.getAttribute("data-status");

            var yearMatch = selectedYear === "" || itemYear === selectedYear;
            var statusMatch = selectedStatus === "" || itemStatus === selectedStatus;

            if (yearMatch && statusMatch) {
                item.style.display = "block";
            } else {
                item.style.display = "none";
            }
        });
    }

    yearFilter.addEventListener("change", filterBooks);
    statusFilter.addEventListener("change", filterBooks);

    var bookItems = document.querySelectorAll(".bookItem");
    bookItems.forEach(function(item) {
        item.addEventListener("click", function() {
            var bookTitle = item.getAttribute("data-title");
            var bookAuthor = item.getAttribute("data-author");
            fetchBookDetails(bookTitle, bookAuthor);
        });
    });

    var modal = document.getElementById("bookModal");
    var span = document.getElementsByClassName("close")[0];

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    function fetchBookDetails(bookTitle, bookAuthor) {
        var url = `https://www.googleapis.com/books/v1/volumes?q=intitle:${encodeURIComponent(bookTitle)}+inauthor:${encodeURIComponent(bookAuthor)}`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.items && data.items.length > 0) {
                    var book = data.items[0].volumeInfo;
                    var subjects = book.categories ? book.categories.slice(0, 5).join(', ') : 'No subjects available.';
                    var detailsHtml = `
                        <h2>${book.title}</h2>
                        <p><strong>Authors:</strong> ${book.authors ? book.authors.join(', ') : 'Unknown'}</p>
                        <p><strong>First Publish Year:</strong> ${book.publishedDate || 'Unknown'}</p>
                        <p><strong>Subjects:</strong> ${subjects}</p>
                        <p><strong>Summary:</strong> ${book.description || 'No summary available.'}</p>
                    `;
                    document.getElementById("bookDetails").innerHTML = detailsHtml;
                    modal.style.display = "block";
                } else {
                    document.getElementById("bookDetails").innerHTML = '<p>No details available for this book.</p>';
                    modal.style.display = "block";
                }
            });
    }
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
    .filter {
        margin-bottom: 20px;
    }

    .bookList {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 16px;
        padding-top: 30px;
    }
    .bookItem {
        text-align: center;
        cursor:pointer;
    }
    .bookImage img {
        width: 100%;
        height: auto;
    }
    .bookImage{
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

    .modal {
        display: none; 
        position: fixed; 
        z-index: 1; 
        padding-top: 100px; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto; 
        background-color: rgb(0,0,0); 
        background-color: rgba(0,0,0,0.4); 
    }

    .modal-content {
        background-color: black;
        color:white;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }

    .close {
        color: white;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
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
