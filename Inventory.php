<?php
require_once 'Database.php';

class Inventory {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function addItem($name, $category, $quantity, $price, $image_url) {
        $query = "INSERT INTO items (name, category, quantity, price, image_url) 
                  VALUES (:name, :category, :quantity, :price, :image_url)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image_url', $image_url);
        
        return $stmt->execute();
    }

    public function getItems($search = '', $category = '') {
        $query = "SELECT * FROM items WHERE 1=1";
        $params = [];

        if (!empty($category)) {
            $query .= " AND category = :category";
            $params['category'] = $category;
        }

        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($search)) {
            return $items;
        }

        // Advanced Premium Fuzzy Search Logic
        $scoredItems = [];
        $searchRaw = strtolower(trim($search));
        $searchClean = str_replace([' ', '-', '_'], '', $searchRaw);

        foreach ($items as $item) {
            $score = 0;
            $name = strtolower($item['name']);
            $cat = strtolower($item['category']);
            $nameClean = str_replace([' ', '-', '_'], '', $name);

            // 1. Exact matches (Substrings)
            if (strpos($name, $searchRaw) !== false) {
                $score += 100;
            }
            if (strpos($cat, $searchRaw) !== false) {
                $score += 80;
            }

            // 2. Cleaned exact matches (e.g., user typed "i phone", item is "iphone")
            if (strpos($nameClean, $searchClean) !== false) {
                $score += 90;
            }

            // 3. Word-by-word typo correction (Levenshtein Distance)
            $searchWords = explode(' ', $searchRaw);
            $nameWords = explode(' ', $name);
            
            foreach ($searchWords as $sWord) {
                if (strlen($sWord) < 2) continue;
                
                foreach ($nameWords as $nWord) {
                    if (strlen($nWord) < 2) continue;
                    
                    $lev = levenshtein($sWord, $nWord);
                    if ($lev === 0) {
                        $score += 50; // Exact word match
                    } elseif ($lev === 1) {
                        $score += 30; // 1 typo (e.g. sumsung -> samsung)
                    } elseif ($lev === 2 && strlen($sWord) > 4) {
                        $score += 15; // 2 typos on a long word
                    }
                }
            }

            // 4. Smashed-word typo correction (e.g. "i phote" -> "iphote" vs "iphone")
            foreach ($nameWords as $nWord) {
                 $lev = levenshtein($searchClean, $nWord);
                 if ($lev === 1) $score += 40;
                 if ($lev === 2 && strlen($searchClean) > 4) $score += 25;
            }

            // 5. Overall Similarity Fallback
            similar_text($searchClean, $nameClean, $percent);
            if ($percent > 75) {
                $score += ($percent / 2);
            }

            // If the item has a high enough relevance score, include it in results
            if ($score > 12) {
                $item['relevance'] = $score;
                $scoredItems[] = $item;
            }
        }

        // Sort items by relevance (highest first)
        usort($scoredItems, function($a, $b) {
            return $b['relevance'] <=> $a['relevance'];
        });

        return $scoredItems;
    }

    public function getItemById($id) {
        $query = "SELECT * FROM items WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateItem($id, $name, $category, $quantity, $price, $image_url) {
        $query = "UPDATE items 
                  SET name = :name, category = :category, quantity = :quantity, price = :price, image_url = :image_url 
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function deleteItem($id) {
        $query = "DELETE FROM items WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }

    public function getCategories() {
        $query = "SELECT DISTINCT category FROM items ORDER BY category ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>
