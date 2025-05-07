<?php

class AdminController extends BaseController {
    protected $db;

    public function __construct($db, $requestMethod) {
        parent::__construct($db, $requestMethod);
        $this->db = $db;
    }

    public function processRequest() {
        try {
            switch ($this->requestMethod) {
                case 'GET':
                    if (isset($_GET['action']) && $_GET['action'] === 'statistics') {
                        $this->getStatistics();
                    } else {
                        $this->setResponse(400, null, 'Invalid action');
                    }
                    break;
                case 'PUT':
                    if (isset($_GET['action']) && $_GET['action'] === 'pricing') {
                        $this->updatePricing();
                    } else {
                        $this->setResponse(400, null, 'Invalid action');
                    }
                    break;
                default:
                    $this->setResponse(405, null, 'Method not allowed');
                    break;
            }
        } catch (Exception $e) {
            error_log('AdminController Error: ' . $e->getMessage());
            $this->setResponse(500, null, 'Internal server error');
        }
        
        $this->sendResponse();
        exit();
    }

    private function updatePricing() {
        // Check if user has admin rights
        // Implement your authorization logic here
        
        // Get request body
        $data = json_decode(file_get_contents("php://input"));
        
        if (empty($data->pricingData)) {
            $this->setResponse(400, null, 'Pricing data is required');
            return;
        }
        
        $pricingData = $data->pricingData;
        $updateCount = 0;
        
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            foreach ($pricingData as $pricing) {
                if (
                    !isset($pricing->sessionId) && 
                    !isset($pricing->movieId) && 
                    !isset($pricing->hallType)
                ) {
                    continue;
                }
                
                $price = $pricing->price ?? 0;
                if ($price <= 0) {
                    continue;
                }
                
                $query = "UPDATE sessions SET price = ? WHERE 1=1";
                $params = [$price];
                
                if (isset($pricing->sessionId)) {
                    $query .= " AND id = ?";
                    $params[] = $pricing->sessionId;
                }
                
                if (isset($pricing->movieId)) {
                    $query .= " AND movie_id = ?";
                    $params[] = $pricing->movieId;
                }
                
                if (isset($pricing->hallType)) {
                    $query .= " AND hall = ?";
                    $params[] = $pricing->hallType;
                }
                
                $stmt = $this->db->prepare($query);
                $stmt->execute($params);
                $updateCount += $stmt->rowCount();
            }
            
            // Commit transaction
            $this->db->commit();
            
            $this->setResponse(200, [
                'message' => 'Pricing updated successfully',
                'sessionsUpdated' => $updateCount
            ]);
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            error_log('Pricing Update Error: ' . $e->getMessage());
            $this->setResponse(500, null, 'Failed to update pricing');
        }
    }
    
    private function getStatistics() {
        // Check if user has admin rights
        // Implement your authorization logic here
        
        // Define the statistics to collect
        $stats = [
            'totalMovies' => 0,
            'totalSessions' => 0,
            'totalBookings' => 0,
            'totalUsers' => 0,
            'totalRevenue' => 0,
            'popularMovies' => [],
            'popularGenres' => [],
            'bookingsByMonth' => []
        ];
        
        // Get total movies
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM movies");
        $stmt->execute();
        $stats['totalMovies'] = (int)$stmt->fetchColumn();
        
        // Get total sessions
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sessions");
        $stmt->execute();
        $stats['totalSessions'] = (int)$stmt->fetchColumn();
        
        // Get total bookings
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bookings");
        $stmt->execute();
        $stats['totalBookings'] = (int)$stmt->fetchColumn();
        
        // Get total users
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users");
        $stmt->execute();
        $stats['totalUsers'] = (int)$stmt->fetchColumn();
        
        // Get total revenue
        $stmt = $this->db->prepare("
            SELECT SUM(s.price) 
            FROM bookings b 
            JOIN sessions s ON b.session_id = s.id
        ");
        $stmt->execute();
        $stats['totalRevenue'] = (float)$stmt->fetchColumn();
        
        // Get popular movies (top 5 by bookings)
        $stmt = $this->db->prepare("
            SELECT m.id, m.title, COUNT(b.id) as booking_count 
            FROM movies m 
            JOIN sessions s ON m.id = s.movie_id 
            JOIN bookings b ON s.id = b.session_id 
            GROUP BY m.id 
            ORDER BY booking_count DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $stats['popularMovies'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get popular genres
        $stmt = $this->db->prepare("
            SELECT m.genre, COUNT(b.id) as booking_count 
            FROM movies m 
            JOIN sessions s ON m.id = s.movie_id 
            JOIN bookings b ON s.id = b.session_id 
            GROUP BY m.genre 
            ORDER BY booking_count DESC
        ");
        $stmt->execute();
        $stats['popularGenres'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get bookings by month (for the last 12 months)
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(b.created_at, '%Y-%m') as month,
                COUNT(*) as booking_count,
                SUM(s.price) as revenue
            FROM bookings b
            JOIN sessions s ON b.session_id = s.id
            WHERE b.created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month
        ");
        $stmt->execute();
        $stats['bookingsByMonth'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->setResponse(200, $stats);
    }
} 