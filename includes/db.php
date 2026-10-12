<?php
/**
 * Database Connection Handler
 * Manages PDO connection with error handling
 */

// Load configuration
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

// Initialize database connection (only once per request)
if (!isset($pdo) || $pdo === null) {
    try {
        $dsn = "mysql:host=" . DB_HOST . ":" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $pdo = new PDO(
            $dsn,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ]
        );
        
        // Test connection
        $pdo->query("SELECT 1");
        
    } catch (PDOException $e) {
        // Log error to file
        $error_msg = "Database Connection Failed: " . $e->getMessage() . " (Errno: " . $e->getCode() . ")";
        error_log($error_msg, 3, LOG_DIR . 'database_errors.log');
        
        // Display user-friendly error
        if (APP_ENV === 'production') {
            die("Database connection failed. Please contact the administrator.");
        } else {
            die("<div style='color: red; font-family: monospace; padding: 20px;'>" 
                . "<strong>Database Connection Error:</strong><br>" 
                . htmlspecialchars($error_msg) 
                . "<br><br><strong>Debugging Info:</strong>"
                . "<ul>"
                . "<li>Host: " . DB_HOST . "</li>"
                . "<li>Database: " . DB_NAME . "</li>"
                . "<li>User: " . DB_USER . "</li>"
                . "<li>Make sure MySQL is running and the database exists</li>"
                . "</ul>"
                . "</div>");
        }
    }
}

/**
 * Function to test if database connection is active
 */
function isDatabaseConnected() {
    global $pdo;
    try {
        if ($pdo === null) {
            return false;
        }
        $pdo->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Function to safely execute database queries
 */
function executeQuery($query, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query Error: " . $e->getMessage() . " | Query: " . $query, 3, LOG_DIR . 'database_errors.log');
        throw $e;
    }
}
?>

