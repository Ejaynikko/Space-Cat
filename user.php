<?php
class User {
    private $conn;

    // Constructor to initialize the database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Method to log in a user
    public function login($email, $password) {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Verify the password
        if ($user && password_verify($password, $user['password'])) {
            return $user; // Return the user data
        } else {
            return false; // Invalid credentials
        }
    }

    // Method to log out the user
    public function logout() {
        session_start(); // Start the session
        session_unset(); // Unset all session variables
        session_destroy(); // Destroy the session
    }

    // Check if the user is logged in
    public function is_logged_in() {
        return isset($_SESSION['user_id']);
    }

    // Method to find or create a user for Google OAuth
    public function findOrCreateUser($email, $name) {
        // Check if user already exists
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User exists, return the user data
            return $result->fetch_assoc();
        } else {
            // User doesn't exist, create a new user
            $defaultPassword = password_hash("default_password", PASSWORD_DEFAULT); // Set a default password (can be updated later)
            $defaultRole = 'user'; // Default role for new users
            $insertQuery = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bind_param("ssss", $name, $email, $defaultPassword, $defaultRole);
            $insertStmt->execute();

            // Retrieve the newly created user
            $newUserId = $this->conn->insert_id;
            return [
                'id' => $newUserId,
                'name' => $name,
                'email' => $email,
                'role' => $defaultRole,
            ];
        }
    }
}
?>
