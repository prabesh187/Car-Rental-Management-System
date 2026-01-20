<?php
// Car Rental System - Advanced Algorithms
// Collection of algorithms to enhance the car rental system

class CarRentalAlgorithms {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    /**
     * 1. DYNAMIC PRICING ALGORITHM
     * Adjusts car prices based on demand, season, and availability
     */
    public function calculateDynamicPrice($car_id, $start_date, $end_date, $base_price) {
        $multiplier = 1.0;
        
        // Season-based pricing
        $month = date('n', strtotime($start_date));
        if (in_array($month, [12, 1, 2])) { // Winter
            $multiplier += 0.2; // 20% increase
        } elseif (in_array($month, [6, 7, 8])) { // Summer
            $multiplier += 0.3; // 30% increase
        }
        
        // Weekend pricing (Friday-Sunday)
        $start_day = date('N', strtotime($start_date));
        if ($start_day >= 5) {
            $multiplier += 0.15; // 15% weekend surcharge
        }
        
        // Demand-based pricing
        $demand_factor = $this->calculateDemandFactor($start_date, $end_date);
        $multiplier += $demand_factor;
        
        // Availability scarcity pricing
        $availability_factor = $this->calculateAvailabilityFactor($car_id, $start_date);
        $multiplier += $availability_factor;
        
        return round($base_price * $multiplier, 2);
    }
    
    /**
     * 2. CAR RECOMMENDATION ALGORITHM
     * Recommends cars based on customer history and preferences
     */
    public function recommendCars($customer_username, $limit = 5) {
        // Get customer's rental history
        $history_sql = "SELECT car_id, COUNT(*) as rental_count, AVG(total_amount) as avg_spent
                       FROM rentedcars WHERE customer_username = ? GROUP BY car_id";
        $stmt = $this->conn->prepare($history_sql);
        $stmt->bind_param("s", $customer_username);
        $stmt->execute();
        $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get all available cars
        $cars_sql = "SELECT * FROM cars WHERE car_availability = 'yes'";
        $cars_result = $this->conn->query($cars_sql);
        $cars = $cars_result->fetch_all(MYSQLI_ASSOC);
        
        $recommendations = [];
        
        foreach ($cars as $car) {
            $score = 0;
            
            // Previous rental bonus
            foreach ($history as $hist) {
                if ($hist['car_id'] == $car['car_id']) {
                    $score += $hist['rental_count'] * 10; // Loyalty bonus
                }
            }
            
            // Price preference scoring
            if (!empty($history)) {
                $avg_budget = array_sum(array_column($history, 'avg_spent')) / count($history);
                $car_price = ($car['ac_price_per_day'] + $car['non_ac_price_per_day']) / 2;
                
                if (abs($car_price - $avg_budget) < $avg_budget * 0.2) {
                    $score += 15; // Price match bonus
                }
            }
            
            // Popularity scoring
            $popularity_sql = "SELECT COUNT(*) as bookings FROM rentedcars WHERE car_id = ?";
            $stmt = $this->conn->prepare($popularity_sql);
            $stmt->bind_param("i", $car['car_id']);
            $stmt->execute();
            $popularity = $stmt->get_result()->fetch_assoc()['bookings'];
            $score += min($popularity * 2, 20); // Max 20 points for popularity
            
            $recommendations[] = [
                'car' => $car,
                'score' => $score,
                'reason' => $this->getRecommendationReason($score, $history, $car)
            ];
        }
        
        // Sort by score and return top recommendations
        usort($recommendations, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return array_slice($recommendations, 0, $limit);
    }
    
    /**
     * 3. OPTIMAL DRIVER ASSIGNMENT ALGORITHM
     * Assigns drivers to bookings based on location, availability, and performance
     */
    public function assignOptimalDriver($car_id, $customer_location = null) {
        // Get ALL available drivers (not just client-specific ones)
        $sql = "SELECT d.*, AVG(COALESCE(f.rating, 5)) as avg_rating, COUNT(rc.id) as completed_trips
                FROM driver d 
                LEFT JOIN rentedcars rc ON d.driver_id = rc.driver_id AND rc.return_status = 'R'
                LEFT JOIN feedback f ON d.driver_id = f.driver_id
                WHERE d.driver_availability = 'yes'
                GROUP BY d.driver_id";
        
        $drivers = $this->conn->query($sql)->fetch_all(MYSQLI_ASSOC);
        
        if (empty($drivers)) {
            return null;
        }
        
        $scored_drivers = [];
        
        foreach ($drivers as $driver) {
            $score = 0;
            
            // Experience score (completed trips)
            $score += min($driver['completed_trips'] * 2, 30);
            
            // Rating score
            $score += ($driver['avg_rating'] - 3) * 10; // Scale 1-5 to contribute 0-20 points
            
            // Availability bonus (recently available drivers get slight preference)
            $score += 5;
            
            // Location proximity (if customer location provided)
            if ($customer_location) {
                $distance = $this->calculateDistance($driver['driver_address'], $customer_location);
                $score += max(20 - $distance, 0); // Closer drivers get more points
            }
            
            $scored_drivers[] = [
                'driver' => $driver,
                'score' => $score
            ];
        }
        
        // Sort by score and return best driver
        usort($scored_drivers, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return $scored_drivers[0]['driver'];
    }
    
    /**
     * 4. ROUTE OPTIMIZATION ALGORITHM
     * Optimizes pickup and drop-off routes for multiple bookings
     */
    public function optimizeRoutes($bookings) {
        if (count($bookings) <= 1) {
            return $bookings;
        }
        
        // Simple nearest neighbor algorithm for route optimization
        $optimized = [];
        $remaining = $bookings;
        $current_location = "depot"; // Starting point
        
        while (!empty($remaining)) {
            $nearest_index = 0;
            $min_distance = PHP_FLOAT_MAX;
            
            for ($i = 0; $i < count($remaining); $i++) {
                $distance = $this->calculateDistance($current_location, $remaining[$i]['pickup_location']);
                if ($distance < $min_distance) {
                    $min_distance = $distance;
                    $nearest_index = $i;
                }
            }
            
            $optimized[] = $remaining[$nearest_index];
            $current_location = $remaining[$nearest_index]['pickup_location'];
            array_splice($remaining, $nearest_index, 1);
        }
        
        return $optimized;
    }
    
    /**
     * 5. DEMAND FORECASTING ALGORITHM
     * Predicts future demand based on historical data
     */
    public function forecastDemand($target_date, $days_ahead = 7) {
        // Get historical data for the same period in previous years
        $target_month = date('n', strtotime($target_date));
        $target_day = date('j', strtotime($target_date));
        
        $sql = "SELECT DATE(booking_date) as date, COUNT(*) as bookings
                FROM rentedcars 
                WHERE MONTH(booking_date) = ? 
                AND DAY(booking_date) BETWEEN ? AND ?
                GROUP BY DATE(booking_date)
                ORDER BY booking_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $end_day = $target_day + $days_ahead;
        $stmt->bind_param("iii", $target_month, $target_day, $end_day);
        $stmt->execute();
        $historical_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (empty($historical_data)) {
            return ['forecast' => 0, 'confidence' => 'low'];
        }
        
        // Simple moving average with trend analysis
        $bookings = array_column($historical_data, 'bookings');
        $average = array_sum($bookings) / count($bookings);
        
        // Calculate trend
        $trend = 0;
        if (count($bookings) > 1) {
            $recent = array_slice($bookings, -3); // Last 3 data points
            $older = array_slice($bookings, 0, 3); // First 3 data points
            $trend = (array_sum($recent) / count($recent)) - (array_sum($older) / count($older));
        }
        
        $forecast = max(0, round($average + $trend));
        $confidence = count($historical_data) > 10 ? 'high' : (count($historical_data) > 5 ? 'medium' : 'low');
        
        return [
            'forecast' => $forecast,
            'trend' => $trend > 0 ? 'increasing' : ($trend < 0 ? 'decreasing' : 'stable'),
            'confidence' => $confidence,
            'historical_average' => round($average, 2)
        ];
    }
    
    /**
     * 6. MAINTENANCE SCHEDULING ALGORITHM
     * Schedules car maintenance based on usage patterns
     */
    public function scheduleMaintenanceAlgorithm($car_id) {
        // Get car usage data
        $sql = "SELECT 
                    SUM(CASE WHEN charge_type = 'km' THEN distance ELSE no_of_days * 50 END) as total_km,
                    COUNT(*) as total_trips,
                    MAX(car_return_date) as last_rental,
                    MIN(rent_start_date) as first_rental
                FROM rentedcars 
                WHERE car_id = ? AND return_status = 'R'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $car_id);
        $stmt->execute();
        $usage = $stmt->get_result()->fetch_assoc();
        
        $maintenance_score = 0;
        $recommendations = [];
        
        // Mileage-based maintenance
        $total_km = $usage['total_km'] ?? 0;
        if ($total_km > 10000) {
            $maintenance_score += 30;
            $recommendations[] = "Major service due (high mileage: {$total_km}km)";
        } elseif ($total_km > 5000) {
            $maintenance_score += 15;
            $recommendations[] = "Minor service recommended ({$total_km}km)";
        }
        
        // Time-based maintenance
        if ($usage['last_rental']) {
            $days_since_last = (time() - strtotime($usage['last_rental'])) / (60 * 60 * 24);
            if ($days_since_last > 90) {
                $maintenance_score += 20;
                $recommendations[] = "Long idle period - inspection needed";
            }
        }
        
        // Usage frequency
        $total_trips = $usage['total_trips'] ?? 0;
        if ($total_trips > 50) {
            $maintenance_score += 10;
            $recommendations[] = "High usage vehicle - frequent checks needed";
        }
        
        // Determine priority
        $priority = 'low';
        if ($maintenance_score > 40) {
            $priority = 'urgent';
        } elseif ($maintenance_score > 20) {
            $priority = 'high';
        } elseif ($maintenance_score > 10) {
            $priority = 'medium';
        }
        
        return [
            'car_id' => $car_id,
            'maintenance_score' => $maintenance_score,
            'priority' => $priority,
            'recommendations' => $recommendations,
            'usage_stats' => $usage
        ];
    }
    
    // Helper functions
    private function calculateDemandFactor($start_date, $end_date) {
        $sql = "SELECT COUNT(*) as bookings FROM rentedcars 
                WHERE rent_start_date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $bookings = $stmt->get_result()->fetch_assoc()['bookings'];
        
        // High demand = higher prices
        return min($bookings * 0.05, 0.5); // Max 50% increase
    }
    
    private function calculateAvailabilityFactor($car_id, $date) {
        $sql = "SELECT COUNT(*) as available_cars FROM cars WHERE car_availability = 'yes'";
        $available = $this->conn->query($sql)->fetch_assoc()['available_cars'];
        
        // Fewer available cars = higher prices
        if ($available < 3) return 0.3;
        if ($available < 5) return 0.15;
        return 0;
    }
    
    private function getRecommendationReason($score, $history, $car) {
        if ($score > 30) return "Highly recommended based on your preferences";
        if ($score > 20) return "Good match for your rental history";
        if ($score > 10) return "Popular choice among customers";
        return "Available option";
    }
    
    private function calculateDistance($location1, $location2) {
        // Simplified distance calculation (in real app, use Google Maps API)
        return rand(1, 20); // Random distance for demo
    }
}
?>