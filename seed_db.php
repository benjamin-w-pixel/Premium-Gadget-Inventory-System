<?php
// seed_db.php
require_once 'Database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    // Read the schema.sql file
    $sqlFile = 'schema.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("schema.sql file not found!");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Simple parsing of multiple SQL statements separated by semicolon
    // We replace comment lines first
    $sql = preg_replace('/--.*\n/', '', $sql);
    
    $queries = explode(';', $sql);
    
    echo "<div style='font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 30px; background: #0f172a; color: white; border-radius: 16px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.5);'>";
    echo "<h2 style='color: #8b5cf6; margin-bottom: 20px;'>Premium Database Seeder</h2>";
    
    $successCount = 0;
    foreach ($queries as $query) {
        $trimmedQuery = trim($query);
        if (!empty($trimmedQuery)) {
            $stmt = $conn->prepare($trimmedQuery);
            $stmt->execute();
            $successCount++;
        }
    }
    
    echo "<p style='color: #10B981; font-weight: 600; font-size: 1.1rem;'>✓ Successfully executed $successCount SQL statements!</p>";
    echo "<p style='color: #94a3b8;'>Your database is now seeded with <strong>32 premium high-end tech items</strong> and a default admin account.</p>";
    echo "<br>";
    echo "<a href='index.php' style='display: inline-block; padding: 12px 24px; background: #8b5cf6; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;'>Return to Shop</a>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 30px; background: #fee2e2; color: #991b1b; border-radius: 16px; border: 1px solid #fca5a5; box-shadow: 0 10px 30px rgba(0,0,0,0.1);'>";
    echo "<h2 style='margin-bottom: 20px;'>Seeding Failed</h2>";
    echo "<p><strong>Error Detail:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<br>";
    echo "<a href='index.php' style='display: inline-block; padding: 12px 24px; background: #ef4444; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;'>Return to Shop</a>";
    echo "</div>";
}
?>
