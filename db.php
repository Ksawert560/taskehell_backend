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
        $conn = new mysqli(
            $this -> host,
            $this -> user,
            $this -> pass,
            $this -> name
        );

        if ($conn -> connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $this -> conn = $conn;
        echo "Connected successfully";
    }

    public function disconnect() {
        try {
            $this -> conn -> close();
            echo "Disconnected successfully";
        } catch (Throwable $e) {
            die("Disconnecting failed: " . $e);
        }
    }

    public function add_user($username, $password) {
        $sql = "INSERT INTO users (username, password)
            VALUES ('$username', '$password')";
        
        $this -> resolve_queries($sql, "New record created successfully");
    }

    public function remove_user($username) {
        $sql = "DELETE FROM users
            WHERE username = '$username'";

        $this -> resolve_queries($sql, "Record deleted successfully");
    }

    public function add_task($user_id, $task) {
        $sql = "INSERT INTO tasks (user_id, task, finished)
            VALUES ($user_id, '$task', FALSE)";

        $this -> resolve_queries($sql, "New record created successfully");
    }

    public function get_user_id($username) {
        $sql = "SELECT id FROM users WHERE username = '$username'";

        $result = $this -> resolve_queries($sql, "Records selected successfully");
        
        if ($result -> num_rows > 0) {
            while($row = $result -> fetch_assoc()) {
                return $row["id"];
            }
        } else {
            echo "0 results";
        }
    }

    public function change_task_state($task_id) {
        $sql = "UPDATE tasks SET finished = NOT finished WHERE id=$task_id";
    
        // TO DO
    }

    private function resolve_queries($sql, $success_msg) {
        try {
            $result = $this -> conn -> query($sql);

            echo $success_msg;

            return $result;
        } catch (Throwable $error) {
            print("Error: ".$error);
        }

        
        // if($result = $this -> conn -> query($sql) === TRUE) {
        //     echo $success_msg;

        //     return $result;
        // } else {
        //     echo "Error encountered";
        // }
    }
}
?>