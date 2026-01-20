<?php
// Advanced Search and Filtering Algorithms

class SearchAlgorithms {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * 7. ADVANCED CAR SEARCH ALGORITHM
     * Multi-criteria search with fuzzy matching and ranking
     */
    public function advancedCarSearch($criteria) {
        $base_sql = "SELECT c.*, 
                     AVG(COALESCE(r.rating, 5)) as avg_rating,
                     COUNT(rc.id) as total_bookings
                     FROM cars c
                     LEFT JOIN rentedcars rc ON c.car_id = rc.car_id
                     LEFT JOIN reviews r ON c.car_id = r.car_id
                     WHERE c.car_availability = 'yes'";
        
        $params = [];
        $param_types = "";
        $conditions = [];
        
        // Price range filter
        if (isset($criteria['min_price']) && isset($criteria['max_price'])) {
            $conditions[] = "(c.ac_price_per_day BETWEEN ? AND ? OR c.non_ac_price_per_day BETWEEN ? AND ?)";
            $params = array_merge($params, [$criteria['min_price'], $criteria['max_price'], 
                                          $criteria['min_price'], $criteria['max_price']]);
            $param_types .= "dddd";
        }
        
        // Car name fuzzy search
        if (isset($criteria['car_name']) && !empty($criteria['car_name'])) {
            $conditions[] = "c.car_name LIKE ?";
            $params[] = "%" . $criteria['car_name'] . "%";
            $param_types .= "s";
        }
        
        // Add conditions to query
        if (!empty($conditions)) {
            $base_sql .= " AND " . implode(" AND ", $conditions);
        }
        
        $base_sql .= " GROUP BY c.car_id";
        
        $stmt = $this->conn->prepare($base_sql);
        if (!empty($params)) {
            $stmt->bind_param($param_types, ...$params);
        }
        $stmt->execute();
        $cars = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Apply ranking algorithm
        return $this->rankSearchResults($cars, $criteria);
    }
    
    /**
     * 8. INTELLIGENT SORTING ALGORITHM
     * Sorts results based on multiple factors with weighted scoring
     */
    public function intelligentSort($cars, $sort_criteria) {
        foreach ($cars as &$car) {
            $score = 0;
            
            // Price scoring (lower price = higher score)
            $avg_price = ($car['ac_price_per_day'] + $car['non_ac_price_per_day']) / 2;
            $price_score = max(0, 100 - ($avg_price / 10)); // Normalize price
            
            // Popularity scoring
            $popularity_score = min($car['total_bookings'] * 5, 50);
            
            // Rating scoring
            $rating_score = ($car['avg_rating'] - 1) * 25; // Scale 1-5 to 0-100
            
            // Availability scoring (always available gets bonus)
            $availability_score = 20;
            
            // Weighted combination based on sort criteria
            switch ($sort_criteria) {
                case 'price_low':
                    $score = $price_score * 0.7 + $rating_score * 0.2 + $popularity_score * 0.1;
                    break;
                case 'popularity':
                    $score = $popularity_score * 0.6 + $rating_score * 0.3 + $price_score * 0.1;
                    break;
                case 'rating':
                    $score = $rating_score * 0.6 + $popularity_score * 0.2 + $price_score * 0.2;
                    break;
                default: // balanced
                    $score = $price_score * 0.3 + $rating_score * 0.3 + $popularity_score * 0.3 + $availability_score * 0.1;
            }
            
            $car['search_score'] = round($score, 2);
        }
        
        // Sort by score
        usort($cars, function($a, $b) {
            return $b['search_score'] - $a['search_score'];
        });
        
        return $cars;
    }
    
    /**
     * 9. AUTOCOMPLETE ALGORITHM
     * Provides intelligent autocomplete suggestions
     */
    public function getAutocompleteSuggestions($query, $type = 'car_name') {
        $suggestions = [];
        
        switch ($type) {
            case 'car_name':
                $sql = "SELECT DISTINCT car_name, COUNT(*) as frequency 
                       FROM cars 
                       WHERE car_name LIKE ? 
                       GROUP BY car_name 
                       ORDER BY frequency DESC, car_name ASC 
                       LIMIT 10";
                break;
            case 'location':
                $sql = "SELECT DISTINCT driver_address as suggestion, COUNT(*) as frequency 
                       FROM driver 
                       WHERE driver_address LIKE ? 
                       GROUP BY driver_address 
                       ORDER BY frequency DESC 
                       LIMIT 10";
                break;
        }
        
        $stmt = $this->conn->prepare($sql);
        $search_term = "%" . $query . "%";
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        foreach ($results as $result) {
            $suggestions[] = [
                'text' => $type === 'car_name' ? $result['car_name'] : $result['suggestion'],
                'frequency' => $result['frequency'],
                'relevance' => $this->calculateRelevance($query, $type === 'car_name' ? $result['car_name'] : $result['suggestion'])
            ];
        }
        
        return $suggestions;
    }
    
    private function rankSearchResults($cars, $criteria) {
        foreach ($cars as &$car) {
            $relevance_score = 0;
            
            // Name matching relevance
            if (isset($criteria['car_name']) && !empty($criteria['car_name'])) {
                $relevance_score += $this->calculateRelevance($criteria['car_name'], $car['car_name']) * 30;
            }
            
            // Price preference relevance
            if (isset($criteria['preferred_price'])) {
                $avg_price = ($car['ac_price_per_day'] + $car['non_ac_price_per_day']) / 2;
                $price_diff = abs($avg_price - $criteria['preferred_price']);
                $relevance_score += max(0, 20 - ($price_diff / 100));
            }
            
            // Popularity boost
            $relevance_score += min($car['total_bookings'] * 2, 20);
            
            // Rating boost
            $relevance_score += ($car['avg_rating'] - 3) * 5;
            
            $car['relevance_score'] = round($relevance_score, 2);
        }
        
        // Sort by relevance
        usort($cars, function($a, $b) {
            return $b['relevance_score'] - $a['relevance_score'];
        });
        
        return $cars;
    }
    
    private function calculateRelevance($query, $text) {
        $query = strtolower($query);
        $text = strtolower($text);
        
        // Exact match
        if ($query === $text) return 100;
        
        // Starts with
        if (strpos($text, $query) === 0) return 80;
        
        // Contains
        if (strpos($text, $query) !== false) return 60;
        
        // Levenshtein distance for fuzzy matching
        $distance = levenshtein($query, $text);
        $max_len = max(strlen($query), strlen($text));
        
        if ($max_len === 0) return 0;
        
        $similarity = (1 - ($distance / $max_len)) * 40;
        return max(0, $similarity);
    }
}

/**
 * 10. BOOKING OPTIMIZATION ALGORITHM
 * Optimizes booking assignments to maximize revenue and efficiency
 */
class BookingOptimization {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function optimizeBookingAssignments($pending_bookings) {
        $optimized_assignments = [];
        
        // Sort bookings by priority (revenue potential, customer tier, etc.)
        usort($pending_bookings, function($a, $b) {
            return $this->calculateBookingPriority($b) - $this->calculateBookingPriority($a);
        });
        
        foreach ($pending_bookings as $booking) {
            $best_assignment = $this->findBestAssignment($booking);
            if ($best_assignment) {
                $optimized_assignments[] = [
                    'booking' => $booking,
                    'assignment' => $best_assignment,
                    'score' => $best_assignment['score']
                ];
            }
        }
        
        return $optimized_assignments;
    }
    
    private function calculateBookingPriority($booking) {
        $priority = 0;
        
        // Revenue potential
        $priority += $booking['estimated_revenue'] * 0.4;
        
        // Customer loyalty (repeat customer bonus)
        if ($booking['is_repeat_customer']) {
            $priority += 20;
        }
        
        // Booking duration (longer bookings preferred)
        $priority += min($booking['duration_days'] * 2, 15);
        
        // Advance booking bonus
        $days_ahead = (strtotime($booking['start_date']) - time()) / (60 * 60 * 24);
        if ($days_ahead > 7) {
            $priority += 10;
        }
        
        return $priority;
    }
    
    private function findBestAssignment($booking) {
        // Get available cars for the booking period
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM rentedcars rc WHERE rc.car_id = c.car_id AND rc.return_status = 'R') as usage_count
                FROM cars c 
                WHERE c.car_availability = 'yes'";
        
        $cars = $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
        
        $best_score = 0;
        $best_assignment = null;
        
        foreach ($cars as $car) {
            $score = 0;
            
            // Price matching (customer budget vs car price)
            $car_price = ($car['ac_price_per_day'] + $car['non_ac_price_per_day']) / 2;
            if ($car_price <= $booking['budget'] * 1.1) { // Within 10% of budget
                $score += 30;
            }
            
            // Car utilization optimization (prefer underused cars)
            $score += max(0, 20 - $car['usage_count']);
            
            // Maintenance scheduling consideration
            $score += $this->getMaintenanceScore($car['car_id']);
            
            if ($score > $best_score) {
                $best_score = $score;
                $best_assignment = [
                    'car' => $car,
                    'score' => $score,
                    'reasons' => $this->getAssignmentReasons($score, $car, $booking)
                ];
            }
        }
        
        return $best_assignment;
    }
    
    private function getMaintenanceScore($car_id) {
        // Prefer cars that don't need immediate maintenance
        $sql = "SELECT SUM(CASE WHEN charge_type = 'km' THEN distance ELSE no_of_days * 50 END) as total_km
                FROM rentedcars WHERE car_id = ? AND return_status = 'R'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $total_km = $result['total_km'] ?? 0;
        
        if ($total_km > 8000) return -10; // Needs maintenance soon
        if ($total_km > 5000) return 0;   // Moderate usage
        return 10; // Low usage, good condition
    }
    
    private function getAssignmentReasons($score, $car, $booking) {
        $reasons = [];
        
        if ($score > 40) {
            $reasons[] = "Excellent match for your requirements";
        } elseif ($score > 25) {
            $reasons[] = "Good value and availability";
        } else {
            $reasons[] = "Available option";
        }
        
        return $reasons;
    }
}
?>