<?php
class Database {
    protected $host;
    protected $user;
    protected $pass;
    protected $name;
    protected $conn;

    function __construct($db_host, $db_user, $db_pass, $db_name) {
        $this -> host = $db_host;
        $this -> user = $db_user;
        $this -> pass = $db_pass;
        $this -> name = $db_name;
    }

    public function connect() {
        $this -> conn = new mysqli(
            $this -> host,
            $this -> user,
            $this -> pass,
            $this -> name
        );

        if ($this -> conn -> connect_error)
            HttpResponse::fromStatus(['error' => "Connection failed: " . $this->conn->connect_error], 500);
    }

    public function disconnect() {
        if (isset($this -> conn) && $this -> conn instanceof mysqli) {
            $this -> conn -> close();
            $this -> conn = null;
        } else
            HttpResponse::fromStatus(['error' => "No active connection to close."], 400);
    }

    /* USERS */

    public function register_user($username, $password) {
        $stmt = $this -> conn -> prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt -> bind_param("ss", $username, $password);

        try {
            if ($stmt -> execute()) {
                return $this -> conn -> insert_id;
            }
        } catch (mysqli_sql_exception $e) {
            if (strpos($e -> getMessage(), 'Duplicate') !== false)
                HttpResponse::fromStatus(['error' => "Username already exists."], 409);
    
            HttpResponse::fromStatus(['error' => "Registration failed: " . $e->getMessage()], 500);
        } finally {
            $stmt -> close();
        }
    }

    public function update_refresh_token($id, $refresh_token) {
        $stmt = $this->conn->prepare("UPDATE users SET refresh_token = ? WHERE id = ?");
    
        try {
            $stmt->bind_param("si", $refresh_token, $id);
    
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    return true;
                } else {
                    HttpResponse::fromStatus(['error' => "User not found or refresh token unchanged."], 404);
                }
            } else {
                HttpResponse::fromStatus(['error' => "Refresh token update failed: " . $stmt->error], 500);
            }
        } finally {
            $stmt->close();
        }
    }
    
    public function delete_refresh_token($id) {
        $stmt = $this->conn->prepare("UPDATE users SET refresh_token = NULL WHERE id = ?");
    
        try {
            $stmt->bind_param("i", $id);
    
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    return true;
                } else {
                    HttpResponse::fromStatus(['error' => "User not found or refresh token already removed."], 404);
                }
            } else {
                HttpResponse::fromStatus(['error' => "Refresh token deletion failed: " . $stmt->error], 500);
            }
        } finally {
            $stmt->close();
        }
    }

    public function get_refresh_token($id) {
        $stmt = $this -> conn -> prepare("SELECT refresh_token FROM users WHERE id = ?");
        $stmt -> bind_param("i", $id);
    
        try {
            if ($stmt -> execute()) {
                $result = $stmt -> get_result();
                $token = $result -> fetch_assoc();
                return $token ?: null;
            }
        } catch (mysqli_sql_exception $e) {
            HttpResponse::fromStatus(['error' => "Token lookup failed: " . $e -> getMessage()], 500);
        } finally {
            $stmt -> close();
        }
    }

    public function get_user_by_username($username) {
        $stmt = $this -> conn -> prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt -> bind_param("s", $username);
    
        try {
            if ($stmt -> execute()) {
                $result = $stmt -> get_result();
                $user = $result -> fetch_assoc();
                return $user ?: null;
            }
        } catch (mysqli_sql_exception $e) {
            HttpResponse::fromStatus(['error' => "User lookup failed: " . $e -> getMessage()], 500);
        } finally {
            $stmt -> close();
        }
    }

    public function get_username($user_id) {
        $stmt = $this -> conn -> prepare("SELECT username FROM users WHERE id = ?");
        $stmt -> bind_param("i", $user_id);
    
        try {
            if ($stmt -> execute()) {
                $result = $stmt -> get_result();
                $user = $result -> fetch_assoc();
                return $user ?: null;
            }
        } catch (mysqli_sql_exception $e) {
            HttpResponse::fromStatus(['error' => "User lookup failed: " . $e -> getMessage()], 500);
        } finally {
            $stmt -> close();
        }
    }

    public function user_exists($id) {
        $stmt = $this -> conn -> prepare("SELECT id FROM users WHERE id = ?");
    
        try {
            $stmt -> bind_param("i", $id);
    
            if ($stmt -> execute()) {
                $result = $stmt -> get_result();
                return $result -> num_rows > 0;
            } else {
                HttpResponse::fromStatus(['error' => "User existence check failed: " . $stmt->error], 500);
            }
        } finally {
            $stmt -> close();
        }
    }
    
    public function remove_user($id) {
        $stmt = $this -> conn -> prepare("DELETE FROM users WHERE id = ?");
        $stmt -> bind_param("i", $id);

        try {
            if($stmt -> execute()) {
                if ($stmt -> affected_rows > 0)
                    return true;
                else
                    HttpResponse::fromStatus(['error' => "User not found."], 404);
            } else
                HttpResponse::fromStatus(['error' => "Delete failed: " . $stmt -> error], 505);
        } finally {
            $stmt -> close();
        }
    }

    public function update_user($id, $username = null, $password = null, $image = null) {
        $fields = [];
        $params = [];
        $types = "";

        if($username !== null) {
            $fields[] = "username = ?";
            $params[] = $username;
            $types .= "s";
        }

        if($password !== null) {
            $fields[] = "password = ?";
            $params[] = $password;
            $types .= "s";
        }
    
        if($image !== null) {
            $fields[] = "image = ?";
            $params[] = $image;
            $types .= "s";
        }

        if(empty($fields))
            HttpResponse::fromStatus(['error' => "No fields provided to update."], 400);

        $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        $params[] = $id;
        $types .= "i";

        $stmt = $this -> conn -> prepare($query);

        try {
            $stmt -> bind_param($types, ...$params);

            if($stmt -> execute()) {
                if($stmt -> affected_rows > 0)
                    return true;
                else
                    HttpResponse::fromStatus(['error' => "User not found or no changes made."], 404);
            } else
                HttpResponse::fromStatus(['error' => "Update failed: " . $stmt -> error]. 500);
        } finally {
            $stmt -> close();
        }
    }

    /* LISTS */

    public function add_list($user_id) {
        $stmt = $this -> conn -> prepare("INSERT INTO lists (user_id) VALUES (?)");

        try {
            $stmt -> bind_param("i", $user_id);

            if ($stmt -> execute())
                return $this -> conn -> insert_id;
            else
                HttpResponse::fromStatus(['error' => "Insert failed: " . $stmt->error], 500);
        } finally {
            $stmt->close();
        }
    }

    public function remove_list($list_id) {
        $stmt = $this -> conn -> prepare("DELETE FROM lists WHERE id = ?");

        try {
            $stmt -> bind_param("i", $list_id);

            if($stmt -> execute()) {
                if($stmt -> affected_rows > 0)
                    return true;
                else
                    HttpResponse::fromStatus(['error' => "List not found."], 404);
            } else
                HttpResponse::fromStatus(['error' => "Delete failed: " . $stmt->error], 500);
        } finally {
            $stmt->close();
        }
    }

    public function get_lists_by_user($user_id) {
        $stmt = $this -> conn -> prepare("SELECT id FROM lists WHERE user_id = ?");

        try {
            $stmt -> bind_param("i", $user_id);

            if($stmt -> execute()) {
                $result = $stmt -> get_result();
                $lists = [];

                while ($row = $result -> fetch_assoc())
                    $lists[] = $row;

                return $lists;
            } else
                HttpResponse::fromStatus(['error' => "Query failed: " . $stmt->error], 500);
        } finally {
            $stmt->close();
        }
    }

    public function is_list_owned_by_user($list_id, $user_id) {
        $stmt = $this -> conn -> prepare("SELECT id FROM lists WHERE id = ? AND user_id = ?");
    
        try {
            $stmt -> bind_param("ii", $list_id, $user_id);
    
            if ($stmt->execute()) {
                $result = $stmt -> get_result();
                return $result -> num_rows > 0;
            } else {
                HttpResponse::fromStatus(['error' => "Ownership check failed: " . $stmt->error], 500);
            }
        } finally {
            $stmt -> close();
        }
    }

    /* TASKS */
    public function add_task($list_id, $task, $due = null, $random = 0) {
        $stmt = $this -> conn -> prepare("INSERT INTO tasks (list_id, task, due, random) VALUES (?, ?, ?, ?)");

        $random = ($random == 1) ? 1 : 0;

        try {
            $stmt -> bind_param("issi", $list_id, $task, $due, $random);

            if($stmt -> execute())
                return $this -> conn -> insert_id;
            else 
                HttpResponse::fromStatus(['error' => "Task insert failed: " . $stmt->error], 500);
        } finally {
            $stmt -> close();
        }
    }

    public function get_list_id_by_task($task_id) {
        $stmt = $this -> conn -> prepare("SELECT list_id FROM tasks WHERE id = ?");
    
        try {
            $stmt -> bind_param("i", $task_id);
    
            if ($stmt -> execute()) {
                $result = $stmt -> get_result();
                $row = $result -> fetch_assoc();
    
                return $row ? $row['list_id'] : null;
            } else {
                HttpResponse::fromStatus(['error' => "Query failed: " . $stmt -> error], 500);
            }
        } finally {
            $stmt -> close();
        }
    
        return null;
    }

    public function remove_task($task_id) {
        $stmt = $this -> conn -> prepare("DELETE FROM tasks WHERE id = ?");

        try {
            $stmt -> bind_param("i", $task_id);

            if($stmt -> execute()) {
                if($stmt -> affected_rows > 0)
                    return true;
                else
                    HttpResponse::fromStatus(['error' => "Task not found"], 404);
            } else
                HttpResponse::fromStatus(['error' => "Delete failed: " . $stmt -> error], 500);
        } finally {
            $stmt -> close();
        }
    }

    public function update_task($task_id, $task = null, $finished = null) {
        $fields = [];
        $params = [];
        $types = "";

        if($task !== null) {
            $fields[] = "task = ?";
            $params[] = $task;
            $types .= "s";
        }

        if($finished !== null) {
            $finished = ($finished == 1) ? 1 : 0;
            $fields[] = "finished = ?";
            $params[] = $finished;
            $types .= "i";
        }

        if(empty($fields))
            HttpResponse::fromStatus(['error' => "No fields provided to update"], 400);

        $query = "UPDATE tasks SET " . implode(", ", $fields) . " WHERE id = ?";
        $params[] = $task_id;
        $types .= "i";

        $stmt = $this -> conn -> prepare($query);

        try {
            $stmt -> bind_param($types, ...$params);

            if($stmt -> execute()) {
                if($stmt -> affected_rows > 0)
                    return true;
                else
                    HttpResponse::fromStatus(['error' => "Task not found or no changes made."], 404);
            } else
                HttpResponse::fromStatus(['error' => "Update failed: " . $stmt -> error], 500);
        } finally {
            $stmt -> close();
        }
    }

    // public function is_task_random($task_id) {
    //     $stmt = $this->conn->prepare("SELECT random FROM tasks WHERE id= ?");

    //     try {
    //         $stmt->bind_param("i", $task_id);
            
    //         if($stmt->execute()) {
    //             $result = $stmt->get_result();
    //             if($row = $result->fetch_assoc())
    //                 return (bool) $row['random'];
    //             else
    //                 throw new HttpResponse(404, ['error' => "Task not found"]);
    //         } else
    //             throw new HttpResponse(500, ['error' => "Query failed: " . $stmt->error]);
    //     } finally {
    //         $stmt->close();
    //     }
    // }

    public function get_tasks_by_list($list_id, $filter_finished = null, $filter_random = null) {
        $query = "SELECT * FROM tasks WHERE list_id = ?";
        $params = [$list_id];
        $types = "i";

        if($filter_finished !== null) {
            $filter_finished = ($filter_finished == 1) ? 1 : 0;
            $query .= " AND finished = ?";
            $params[] = $filter_finished;
            $types .= "i";
        }

        if($filter_random !== null) {
            $filter_random = ($filter_random == 1) ? 1 : 0;
            $query .= " AND random = ?";
            $params[] = $filter_random;
            $types .= "i";
        }

        $stmt = $this -> conn -> prepare($query);

        try {
            $stmt -> bind_param($types, ...$params);

            if($stmt -> execute()) {
                $result = $stmt -> get_result();
                $tasks = [];

                while($row = $result -> fetch_assoc())
                    $tasks[] = $row;

                return $tasks;
            } else
                HttpResponse::fromStatus(['error' => "Query failed: " . $stmt -> error], 500);
        } finally {
            $stmt -> close();
        }
    }

    public function generate_random_task() {
        $noun_stmt = $this -> conn -> prepare("SELECT noun FROM nouns ORDER BY RAND() LIMIT 1");
        $verb_stmt = $this -> conn -> prepare("SELECT verb FROM verbs ORDER BY RAND() LIMIT 1");

        try {
            $noun = null;
            $verb = null;

            if($noun_stmt -> execute()){
                $result = $noun_stmt -> get_result();
                if($row = $result -> fetch_assoc())
                    $noun = $row['noun'];
                else
                    HttpResponse::fromStatus(['error' => "No noun found"], 500);
            } else
                HttpResponse::fromStatus(['error' => "Noun query failed: " . $noun_stmt->error], 500);

            if($verb_stmt->execute()){
                $result = $verb_stmt -> get_result();
                if($row = $result -> fetch_assoc())
                    $verb = $row['verb'];
                else
                    HttpResponse::fromStatus(['error' => "No verb found."]. 500);
            } else
                HttpResponse::fromStatus(['error' => "Verb query failed: " . $verb_stmt->error], 500);

            return ucfirst($verb) . " a " . $noun;
        } finally {
            $noun_stmt->close();
            $verb_stmt->close();
        }
    }
}
?>