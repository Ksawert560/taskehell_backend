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
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Connection failed: " . $this->conn->connect_error]);
            exit;
        }
    }

    public function disconnect() {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    private function success($message) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => $message]);
    }

    private function error($message) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => $message]);
    }

    /* USERS */
    public function register_user($username, $password){
        $stmt = $this->conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            $user_id = $this->conn->insert_id;
            http_response_code(201); // Created
            echo json_encode([
                "message" => "User created",
                "user_id" => $user_id
            ]);
        } else {
            http_response_code(409); // Conflict (e.g., duplicate username)
            echo json_encode(["error" => $stmt->error]);
        }

        $stmt->close();
    }

    public function remove_user($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "User removed"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
        }

        $stmt->close();
    }

    public function update_user_username($id, $username) {
        $stmt = $this -> conn -> prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $username, $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "User updated"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
        }

        $stmt->close();
    }

    public function update_user_password($id, $password) {
        $stmt = $this -> conn -> prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $password, $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "User updated"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "User not found"]);
        }

        $stmt->close();
    }

    public function get_user_by_username($username) {
        $stmt = $this->conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        $stmt->close();
        return $user;
    }

    /* TASKS */
    public function register_task($user_id, $task, $due = null) {
        if ($due === null) {
            // NULL directly in the SQL statement
            $stmt = $this->conn->prepare("INSERT INTO tasks (user_id, task, due) VALUES (?, ?, NULL)");
            $stmt->bind_param("is", $user_id, $task);
        } else {
            // If due is provided
            $stmt = $this->conn->prepare("INSERT INTO tasks (user_id, task, due) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $user_id, $task, $due);
        }
    
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Task added"]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => $stmt->error]);
        }
    
        $stmt->close();
    }

    public function register_random_task($user_id) {
        $verb_result = $this->conn->query("SELECT verb FROM verbs ORDER BY RAND() LIMIT 1");
        $verb = $verb_result->fetch_assoc()['verb'];

        $noun_result = $this->conn->query("SELECT noun FROM nouns ORDER BY RAND() LIMIT 1");
        $noun = $noun_result->fetch_assoc()['noun'];

        $task = ucfirst($verb) . " a " . $noun;

        $stmt = $this->conn->prepare("INSERT INTO tasks (user_id, task) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $task);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Random task '{$task}' added"]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => $stmt->error]);
        }

        $stmt->close();
    }

    public function remove_task($user_id, $task_id) {
        $stmt = $this->conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $task_id, $user_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "Task removed"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Task not found"]);
        }

        $stmt->close();
    }

    public function update_task($task_id, $state) {
        $stmt = $this->conn->prepare("UPDATE tasks SET finished = ? WHERE id = ?");
        $stmt->bind_param("ii", $state, $task_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            http_response_code(200);
            echo json_encode(["message" => "Task updated"]);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Task not found"]);
        }

        $stmt->close();
    }

    public function return_tasks($user_id, $state_filter = null) {
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

            http_response_code(200);
            echo json_encode(["tasks" => $tasks]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => $stmt->error]);
        }

        $stmt->close();
    }
}
?>
