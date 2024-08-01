<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Suggestion</title>
    <style>
        .body2 {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container2 {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        input, textarea, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .search-results {
            list-style: none;
            padding: 0;
        }
        .search-results li {
            padding: 10px;
            background: #f4f4f4;
            margin-bottom: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-results li:hover {
            background: #e2e2e2;
        }
    </style>
</head>
<body class='body2'>
    <div class="container2">
        <h1>Submit a Suggestion</h1>
        <form id="suggestionForm" method="POST" action="submit_suggestion.php">
            <label for="name">Your Name:</label>
            <input type="text" id="name" name="name" required><br>

            <label for="bookSearch">Search for a Book:</label>
            <input type="text" id="bookSearch" name="bookSearch" placeholder="Enter book title or author" required><br>
            
            <ul id="searchResults" class="search-results"></ul>

            <input type="hidden" id="bookId" name="bookId">
            <input type="hidden" id="bookTitle" name="bookTitle">
            <input type="hidden" id="bookAuthor" name="bookAuthor">

            <label for="suggestion">Comments:</label>
            <textarea id="suggestion" name="suggestion" required></textarea><br>

            <button type="submit">Submit Suggestion</button>
        </form>
    </div>

    <script>
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
    </script>
</body>
</html>
