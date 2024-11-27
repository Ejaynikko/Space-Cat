<?php
require 'db.php';
require 'includes/Blog.php';

$blog = new Blog($conn);

if (isset($_POST['search_query'])) {
    $search_query = '%' . $_POST['search_query'] . '%';
    $query = "SELECT * FROM blogs WHERE title LIKE ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $search_query);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($blog = $result->fetch_assoc()) {
            echo '
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <img src="' . $blog['image'] . '" class="card-img-top" alt="Blog Image">
                        <div class="card-body">
                            <h5 class="card-title">' . $blog['title'] . '</h5>
                            <p class="card-text">' . substr($blog['content'], 0, 100) . '...</p>
                            <a href="blog.php?id=' . $blog['id'] . '" class="btn btn-primary">Read More</a>
                        </div>
                    </div>
                </div>
            ';
        }
    } else {
        echo '<p class="text-center">No blogs found.</p>';
    }
}
