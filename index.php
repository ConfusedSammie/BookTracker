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
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #023337;
            color: #fff;
        }

        header {
            text-align: center;
            padding: 20px;
            background-color: #00262a;
        }

        header h1 {
            margin: 0;
            font-size: 2rem;
        }

        .searchContainer {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 20px;
            gap: 10px;
        }

        .filter {
            margin-right: 10px;
        }

        #searchBar {
            width: 50%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .bookList {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            padding: 0 20px;
        }

        .bookItem {
            text-align: center;
            background-color: #004f4f;
            padding: 10px;
            border-radius: 10px;
            transition: transform 0.3s;
        }

        .bookItem:hover {
            transform: scale(1.05);
        }

        .bookImage img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .bookTitle, .bookAuthor {
            margin: 10px 0;
        }

        .bookTitle {
            font-size: 1rem;
            font-weight: 700;
        }

        .bookAuthor {
            font-size: 0.875rem;
            color: #a0a0a0;
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

        .bookRating {
            margin-top: 10px;
        }

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

        .modal {
            display: none; 
            position: fixed; 
            z-index: 2; 
            padding-top: 100px; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0, 0, 0, 0.8);
        }

        .modal-content {
            background-color: white;
            color: black;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 10px;
        }

        .close {
            color: black;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: red;
            text-decoration: none;
            cursor: pointer;
        }

        .open-suggestion-btn {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .open-suggestion-btn:hover {
            background-color: #0056b3;
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
</head>
<body>
    <header>
        <h1>Sam's Book List</h1>
    </header>

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
                    <option value="Read">Read</option>
                    <option value="TBR">To Be Read</option>
                </select>
            </div>

            <input type="text" id="searchBar" placeholder="Search for books..." aria-label="Search for books">
        </div>
        <button class="open-suggestion-btn" onclick="openSuggestionModal()">Submit a Suggestion</button>

        <div id="suggestionModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeSuggestionModal()">&times;</span>
                <div id="suggestionFormContainer">
                    <!-- Suggestion form will be loaded here -->
                </div>
            </div>
        </div>

        <div class="bookList">
            <?php foreach($books as $book): ?>
                <div class="bookItem" role="listitem" data-title="<?= htmlspecialchars($book['Title'], ENT_QUOTES, 'UTF-8') ?>" data-author="<?= htmlspecialchars($book['Author'], ENT_QUOTES, 'UTF-8') ?>" data-year="<?= date('Y', strtotime($book['Month_Read'])) ?>" data-status="<?= htmlspecialchars($book['Status'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="bookMonth <?= $book['Status'] == 'TBR' ? 'tbr' : '' ?>">
                        <?= htmlspecialchars($book['Month_Read'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="bookImage">
                        <img src="<?= htmlspecialchars($book['image_url'], ENT_QUOTES, 'UTF-8') ?>" alt="Book cover of <?= htmlspecialchars($book['Title'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="bookTitle">
                        <?= htmlspecialchars($book['Title'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="bookAuthor">
                        <?= htmlspecialchars($book['Author'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="bookRating">
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

        window.openSuggestionModal = function() {
            var modal = document.getElementById("suggestionModal");
            fetch("suggestion.php")
            .then(response => response.text())
            .then(html => {
                document.getElementById("suggestionFormContainer").innerHTML = html;
                modal.style.display = "block";
                attachSuggestionFormEvents();
            });
        }

        window.closeSuggestionModal = function() {
            var modal = document.getElementById("suggestionModal");
            modal.style.display = "none";
        }

        function attachSuggestionFormEvents() {
            document.getElementById('bookSearch').addEventListener('input', function() {
                var query = this.value;
                if (query.length < 3) {
                    document.getElementById('searchResults').innerHTML = '';
                    return;
                }
                fetch('https://www.googleapis.com/books/v1/volumes?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    var results = data.items || [];
                    var html = results.map(book => {
                        var volumeInfo = book.volumeInfo;
                        return `<li data-id="${book.id}" data-title="${volumeInfo.title}" data-author="${volumeInfo.authors ? volumeInfo.authors.join(', ') : 'Unknown'}">
                        <strong>${volumeInfo.title}</strong> by ${volumeInfo.authors ? volumeInfo.authors.join(', ') : 'Unknown'}
                        </li>`;
                    }).join('');
                    document.getElementById('searchResults').innerHTML = html;
                });
            });

            document.getElementById('searchResults').addEventListener('click', function(e) {
                var li = e.target.closest('li');
                if (li) {
                    document.getElementById('bookId').value = li.getAttribute('data-id');
                    document.getElementById('bookTitle').value = li.getAttribute('data-title');
                    document.getElementById('bookAuthor').value = li.getAttribute('data-author');
                    document.getElementById('searchResults').innerHTML = '';
                    document.getElementById('bookSearch').value = `${li.getAttribute('data-title')} by ${li.getAttribute('data-author')}`;
                }
            });

            document.getElementById('suggestionForm').addEventListener('submit', function(e) {
                e.preventDefault();

                var formData = new FormData(this);
                fetch('submit_suggestion.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    var notification = document.createElement('div');
                    notification.innerText = data;
                    notification.style.position = 'fixed';
                    notification.style.top = '10px';
                    notification.style.left = '50%';
                    notification.style.transform = 'translateX(-50%)';
                    notification.style.backgroundColor = '#28a745';
                    notification.style.color = 'white';
                    notification.style.padding = '10px';
                    notification.style.borderRadius = '5px';
                    document.body.appendChild(notification);
                    setTimeout(() => {
                        notification.remove();
                    }, 3000);
        closeSuggestionModal(); // Close the modal after submitting
    })
                .catch(error => console.error('Error:', error));
            });

        }
    });

</script>
</body>
</html>
