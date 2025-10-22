<?php
require_once(__DIR__ . '/../config.php');

class TodoModel
{
    private $conn;

    // public function __construct()
    // {
    //     $this->conn = pg_connect('host=' . DB_HOST . ' port=' . DB_PORT . ' dbname=' . DB_NAME . ' user=' . DB_USER . ' password=' . DB_PASSWORD);
    //     if (!$this->conn) {
    //         die('Koneksi database gagal');
    //     }
    // }

    public function __construct() {
    // Mengambil DATABASE_URL yang disediakan oleh Heroku
    $dbUrl = getenv('DATABASE_URL');

    if (empty($dbUrl)) {
        // Fallback untuk development di lokal jika DATABASE_URL tidak ada
        $host = 'localhost';
        $port = '5432';
        $dbname = 'latihan_php';
        $user = 'postgres';
        $password = '12345';
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password";
    } else {
        // Parsing URL database dari Heroku
        $dbopts = parse_url($dbUrl);
        $dsn = sprintf(
            "pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s",
            $dbopts['host'],
            $dbopts['port'],
            ltrim($dbopts['path'], '/'),
            $dbopts['user'],
            $dbopts['pass']
        );
    }

    try {
        $this->db = new PDO($dsn);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
    public function getAllTodos($filter = 'all', $search = '')
    {
        $query = 'SELECT * FROM todo';
        $params = [];

        if ($filter === 'finished') {
            $query .= ' WHERE is_finished = 1';
        } elseif ($filter === 'unfinished') {
            $query .= ' WHERE is_finished = 0';
        }

        if (!empty($search)) {
            if (strpos($query, 'WHERE') !== false) {
                $query .= ' AND (title ILIKE $1 OR description ILIKE $1)';
            } else {
                $query .= ' WHERE (title ILIKE $1 OR description ILIKE $1)';
            }
            $params[] = '%' . $search . '%';
        }

        $query .= ' ORDER BY id ASC';
        $result = pg_query_params($this->conn, $query, $params);
        $todos = [];
        if ($result && pg_num_rows($result) > 0) {
            while ($row = pg_fetch_assoc($result)) {
                $todos[] = $row;
            }
        }
        return $todos;
    }

    public function getTodoById($id)
    {
        $query = 'SELECT * FROM todo WHERE id = $1';
        $result = pg_query_params($this->conn, $query, [$id]);
        return pg_fetch_assoc($result);
    }

    public function createTodo($title, $description)
    {
        $query = 'INSERT INTO todo (title, description) VALUES ($1, $2)';
        $result = pg_query_params($this->conn, $query, [$title, $description]);
        return $result !== false;
    }

    public function updateTodo($id, $title, $description, $is_finished)
    {
        $query = 'UPDATE todo SET title = $1, description = $2, is_finished = $3 WHERE id = $4';
        $result = pg_query_params($this->conn, $query, [$title, $description, $is_finished, $id]);
        return $result !== false;
    }

    public function deleteTodo($id)
    {
        $query = 'DELETE FROM todo WHERE id = $1';
        $result = pg_query_params($this->conn, $query, [$id]);
        return $result !== false;
    }

    public function isTitleExists($title, $id = null)
{
    // Ganti '=' menjadi 'ILIKE' untuk perbandingan case-insensitive
    $query = 'SELECT id FROM todo WHERE title ILIKE $1';
    $params = [$title];
    if ($id) {
        $query .= ' AND id != $2';
        $params[] = $id;
    }
    $result = pg_query_params($this->conn, $query, $params);
    return pg_num_rows($result) > 0;
}
}