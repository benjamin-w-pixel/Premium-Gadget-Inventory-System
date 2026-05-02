<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh;
    private $error;

    public function __construct() {
        // Set DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname;
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        // Create PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            // Stop execution to prevent fatal errors later
            die("<div style='color: white; background: #ef4444; padding: 20px; font-family: sans-serif; text-align: center; border-radius: 10px; margin: 20px;'><strong>Database Connection Error:</strong><br><br>" . htmlspecialchars($this->error) . "<br><br>Please check your config.php to ensure your database credentials (like password) match your MySQL setup.</div>");
        }
    }

    public function getConnection() {
        return $this->dbh;
    }
}
?>
