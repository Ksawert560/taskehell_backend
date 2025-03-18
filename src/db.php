<?php
class Database {
    protected $host;
    protected $user;
    protected $pass;
    protected $name;
    protected $conn;

    function __construct($db_host, $db_user, $db_pass, $db_name) {
        $this->host = $db_host;
        $this->user = $db_user;
        $this->pass = $db_pass;
        $this->name = $db_name;
    }

    public function connect() {
        $this->conn = new mysqli(
            $this->host,
            $this->user,
            $this->pass,
            $this->name
        );

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        echo "Connected successfully";
    }

    public function disconnect() {
        if ($this->conn) {
            $this->conn->close();
            echo "Disconnected successfully";
        }
    }

    /* USERS */
    public function register_user($username, $password){
        $stmt = $this->conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            echo "New user created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    public function remove_user($username) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);

        if ($stmt->execute()) {
            echo "User removed successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    public function return_user_id($username) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['id'];
        } else {
            echo "User not found";
            return null;
        }

        $stmt->close();
    }

    /* TASKS */
    public function register_task($user_id, $task, $due=null) {
        $stmt = $this->conn->prepare("INSERT INTO tasks (user_id, task, due) VALUES (?, ?, ?)");
        $stmt->bind_param("is", $user_id, $task, $due);

        if ($stmt->execute()) {
            echo "Task added successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    public function register_random_task($user_id) {
        // Get random verb
        $verb_result = $this->conn->query("SELECT verb FROM verbs ORDER BY RAND() LIMIT 1");
        $verb = $verb_result->fetch_assoc()['verb'];
    
        // Get random noun
        $noun_result = $this->conn->query("SELECT noun FROM nouns ORDER BY RAND() LIMIT 1");
        $noun = $noun_result->fetch_assoc()['noun'];
    
        // Combine them into a task
        $task = ucfirst($verb) . " " . $noun;
    
        // Insert task into tasks table
        $stmt = $this->conn->prepare("INSERT INTO tasks (user_id, task) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $task);
    
        if ($stmt->execute()) {
            echo "Random task '{$task}' added successfully for user ID {$user_id}";
        } else {
            echo "Error: " . $stmt->error;
        }
    
        $stmt->close();
    }

    public function remove_task($user_id, $task_id) {
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);

        if ($stmt->execute()) {
            echo "Task removed successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    public function update_task($task_id, $state) {
        $stmt = $this->conn->prepare("UPDATE tasks SET finished = ? WHERE id = ?");
        $stmt->bind_param("ii", $state, $task_id);

        if ($stmt->execute()) {
            echo "Task state updated successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    public function return_tasks($user_id, $state_filter=null) {
        if (is_null($state_filter)) {
            $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM tasks WHERE user_id = ? AND finished = ?");
            $stmt->bind_param("ii", $user_id, $state_filter);
        }

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $tasks = [];
            while ($row = $result->fetch_assoc()) {
                $tasks[] = $row;
            }
            echo json_encode($tasks);
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
