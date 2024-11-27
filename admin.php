<?php
session_start();
require 'db.php'; 
require 'Includes/Blog.php'; 

$blog = new Blog($conn);

// Check if the user is logged in
$is_logged_in = isset($_SESSION['user_id']);
if (!$is_logged_in) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_blog'])) {
        $title = $_POST['title'];
        $content = strip_tags($_POST['content'], '<br><ul><li><a><img><b><strong><i><em>');
        $image = $_FILES['image'];
        if ($blog->createPost($title, $content, $image)) {
            header("Location: admin.php");
            exit();
        } else {
            echo "Error uploading image.";
        }
    }

    if (isset($_POST['update_blog'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = strip_tags($_POST['content'], '<br><ul><li><a><img><b><strong><i><em>');
        $image = $_FILES['image'];
        if ($blog->updatePost($id, $title, $content, $image)) {
            header("Location: admin.php");
            exit();
        } else {
            echo "Error updating post.";
        }
    }

    if (isset($_POST['delete_blog'])) {
        $id = $_POST['id'];
        if ($blog->deletePost($id)) {
            header("Location: admin.php");
            exit();
        } else {
            echo "Error deleting post.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.tiny.cloud/1/89072pqwvzy8nq26qrakx799e1spssg6l6ati6n1ryjwgn1k/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><img src="uploads/hi.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
        Admin Dashboard</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <?php if ($is_logged_in): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <!-- Create Blog Post -->
    <?php if (!isset($_GET['edit_id'])): ?>
    <h2 class="mt-4">Create Blog Post</h2>
    <form method="POST" action="admin.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Blog Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="content">Blog Content</label>
            <div id="content-editor"></div>
            <input type="hidden" name="content" id="content">
        </div>
        <div class="form-group">
            <label for="image">Blog Image</label>
            <input type="file" name="image" class="form-control" required>
        </div>
        <button type="submit" name="create_blog" class="btn btn-primary">Create Blog</button>
    </form>
    <?php endif; ?>

    <!-- Edit Blog Post -->
    <?php if (isset($_GET['edit_id'])): 
 $id = $_GET['edit_id'];
    $blogPost = $blog->getPostById($id);
?>
    <h2 class="mt-4">Edit Blog Post</h2>
    <form method="POST" action="admin.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $blogPost['id']; ?>">
        <div class="form-group">
            <label for="title">Blog Title</label>
            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($blogPost['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="content">Blog Content</label>
            <div id="content-editor-edit"><?php echo $blogPost['content']; ?></div>
            <input type="hidden" name="content" id="content-edit">
        </div>
        <div class="form-group">
            <label for="image">Blog Image</label>
            <input type="file" name="image" class="form-control">
        </div>
        <button type="submit" name="update_blog" class="btn btn-primary">Update Blog</button>
    </form>
    <?php endif; ?>
</div>

<!-- Blog Posts Table -->
<h2 class="mt-4">Manage Blog Posts</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Title</th>
            <th>Content</th>
            <th>Image</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $result = $blog->getAllPosts();
        while ($row = $result->fetch_assoc()) {
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars(substr($row['content'], 0, 100)); ?>...</td>
                <td><img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Blog Image" width="100"></td>
                <td>
                    <a href="admin.php?edit_id=<?php echo $row['id']; ?>" class="btn btn-primary">Edit</a>
                    <form method="POST" action="admin.php" style="display: inline;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="delete_blog" class="btn btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>

<script>

tinymce.init({
    selector: '#content-editor, #content-editor-edit',
    plugins: 'lists link image code',
    toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | link image',
    valid_elements: '*[*]', // Allow all HTML elements and attributes
    entity_encoding: 'raw', // Prevent encoding of characters
    forced_root_block: false, // Prevent wrapping content in <p> tags automatically
    setup: function (editor) {
        editor.on('change', function () {
            // Update the hidden input fields when content changes
            const contentId = editor.id === 'content-editor' ? 'content' : 'content-edit';
            document.getElementById(contentId).value = editor.getContent();
        });
    }
});


</script>
</body>
</html>