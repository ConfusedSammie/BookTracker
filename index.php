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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sam's Book List</title>
</head>
<body>

    <main role="main" class='wholeContainer'>

        <div id="bookModal" class="modal" role="dialog" aria-labelledby="modalTitle" aria-modal="true">
            <div class="modal-content">
                <span class="close" role="button" tabindex="0" aria-label="Close">&times;</span>
                <div id="bookDetails"></div>
            </div>
        </div>

        <div class="searchContainer">
            <div class="filter">
                <label for="yearFilter">Filter by Year:</label>
                <select id="yearFilter" aria-label="Filter by Year">
                    <option value="">All Years</option>
                    <?php foreach ($years as $year): ?>
                        <option value="<?= $year ?>"><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter">
                <label for="statusFilter">Filter by Status:</label>
                <select id="statusFilter" aria-label="Filter by Status">
                    <option value="">All Statuses</option>
                    <option value="Done">Read</option>
                    <option value="TBR">To Be Read</option>
                </select>
            </div>

            <input type="text" id="searchBar" placeholder="Search for books..." aria-label="Search for books">
        </div>

        <div class='bookList'>
            <?php foreach($books as $book): ?>
                <div class='bookItem' role="listitem" data-title="<?= htmlspecialchars($book['Title'], ENT_QUOTES, 'UTF-8') ?>" data-author="<?= htmlspecialchars($book['Author'], ENT_QUOTES, 'UTF-8') ?>" data-year="<?= date('Y', strtotime($book['Month_Read'])) ?>" data-status="<?= htmlspecialchars($book['Status'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class='bookMonth <?= $book['Status'] == 'TBR' ? 'tbr' : '' ?>'>
                        <?= htmlspecialchars($book['Month_Read'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class='bookImage'>
                        <img src='<?= htmlspecialchars($book['image_url'], ENT_QUOTES, 'UTF-8') ?>' alt='Book cover of <?= htmlspecialchars($book['Title'], ENT_QUOTES, 'UTF-8') ?>'>
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

    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var yearFilter = document.getElementById("yearFilter");
            var statusFilter = document.getElementById("statusFilter");
            var searchBar = document.getElementById("searchBar");

            function filterBooks() {
                var selectedYear = yearFilter.value;
                var selectedStatus = statusFilter.value.toLowerCase();
                var searchQuery = searchBar.value.toLowerCase();
                var bookItems = document.querySelectorAll(".bookItem");

                bookItems.forEach(function(item) {
                    var itemYear = item.getAttribute("data-year");
                    var itemStatus = item.getAttribute("data-status").toLowerCase();
                    var itemTitle = item.getAttribute("data-title").toLowerCase();
                    var itemAuthor = item.getAttribute("data-author").toLowerCase();

                    var yearMatch = selectedYear === "" || itemYear === selectedYear;
                    var statusMatch = selectedStatus === "" || itemStatus === selectedStatus;
                    var searchMatch = itemTitle.includes(searchQuery) || itemAuthor.includes(searchQuery);

                    if (yearMatch && statusMatch && searchMatch) {
                        item.style.display = "block";
                    } else {
                        item.style.display = "none";
                    }
                });
            }

            yearFilter.addEventListener("change", filterBooks);
            statusFilter.addEventListener("change", filterBooks);
            searchBar.addEventListener("input", filterBooks);

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
                        console.log(book)
                        var isbn = book.industryIdentifiers ? book.industryIdentifiers[0].identifier : 'N/A';
                        var averageRating = book.averageRating ? book.averageRating + ' / 5' : 'No rating available';
                        var subjects = book.categories ? book.categories.slice(0, 5).join(', ') : 'No subjects available.';
                        var detailsHtml = `
                        <h2>${book.title}</h2>
                        <p><strong>Authors:</strong> ${book.authors ? book.authors.join(', ') : 'Unknown'}</p>
                        <p><strong>First Publish Year:</strong> ${book.publishedDate || 'Unknown'}</p>
                        <p><strong>Subjects:</strong> ${subjects}</p>
                        <p><strong>Summary:</strong> ${book.description || 'No summary available.'}</p>
                        <p><strong>ISBN:</strong> ${isbn}</p>
                        <p><strong>Average Rating:</strong> ${averageRating} <em>(This is a global rating, not mine)</em></p>
                        <div class="buy-buttons">
                        <a href="https://www.amazon.ca/s?k=${isbn}" target="_blank" class="buy-button amazon-button">Buy from Amazon.ca</a>
                        </div>
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
        .buy-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
        }

        .buy-button {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            color: white;
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            width: 200px;
            height: 40px;
            line-height: 40px;
        }

        .amazon-button {
            background-color: #FF9900; /* Amazon's button color */
            color: black;
            border: 1px solid #B12704;
        }

        .amazon-button:hover {
            background-color: #B12704; /* Darker shade for hover effect */
        }

        body {
            margin: 0;
            color: white;
            background-color: #023337;
        }

        .wholeContainer {
            padding: 20px;
        }

        .searchContainer {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter {
            margin-right: 10px;
        }

        #searchBar {
            width: 50%;
            padding: 5px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .bookList {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 16px;
            padding-top: 30px;
        }

        .bookItem {
            text-align: center;
            cursor: pointer;
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
            color: white;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
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
</body>
</html>
