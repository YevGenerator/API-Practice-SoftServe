<?php

class BookingController extends BaseController {
    private $booking;
    private $bookingId;

    public function __construct($db, $requestMethod) {
        parent::__construct($db, $requestMethod);
        $this->booking = new Booking($db);
    }

    public function setBookingId($bookingId) {
        $this->bookingId = $bookingId;
    }

    public function processRequest() {
        try {
            switch ($this->requestMethod) {
                case 'GET':
                    if (isset($this->bookingId)) {
                        $this->getBooking($this->bookingId);
                    } else {
                        $this->setResponse(400, null, 'Booking ID is required');
                    }
                    break;
                case 'POST':
                    $this->createBooking();
                    break;
                case 'DELETE':
                    if (isset($this->bookingId)) {
                        $this->deleteBooking($this->bookingId);
                    } else {
                        $this->setResponse(400, null, 'Booking ID is required');
                    }
                    break;
                default:
                    $this->setResponse(405, null, 'Method not allowed');
                    break;
            }
        } catch (Exception $e) {
            $this->setResponse(500, null, 'Internal server error');
            error_log($e->getMessage());
        }
        
        $this->sendResponse();
        exit();
    }

    private function getBooking($id) {
        if (!is_numeric($id)) {
            $this->setResponse(400, null, 'Invalid booking ID');
            return;
        }

        $this->booking->id = (int)$id;
        
        if (!$this->booking->readOne()) {
            $this->setResponse(404, null, 'Booking not found');
            return;
        }

        $bookingData = [
            'id' => $this->booking->id,
            'sessionId' => $this->booking->session_id,
            'userId' => $this->booking->user_id,
            'seatNumber' => $this->booking->seat_number,
            'createdAt' => $this->booking->created_at
        ];

        $this->setResponse(200, $bookingData);
    }

    private function createBooking() {
        // Check if user is authenticated
        // You should implement proper authentication validation here
        
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$this->validateRequiredFields($data, ['session_id', 'user_id', 'seat_number'])) {
            $this->setResponse(400, null, 'Missing required fields: session_id, user_id, and seat_number are required');
            return;
        }

        // Check if the seat is available
        if (!$this->booking->isSeatAvailable($data['session_id'], $data['seat_number'])) {
            $this->setResponse(400, null, 'The selected seat is already booked');
            return;
        }

        $this->booking->session_id = $data['session_id'];
        $this->booking->user_id = $data['user_id'];
        $this->booking->seat_number = $data['seat_number'];

        if (!$this->booking->create()) {
            $this->setResponse(500, null, 'Unable to create booking');
            return;
        }

        $this->setResponse(201, [
            'id' => $this->booking->id,
            'sessionId' => $this->booking->session_id,
            'userId' => $this->booking->user_id,
            'seatNumber' => $this->booking->seat_number
        ]);
    }
    
    private function deleteBooking($id) {
        // Check if user is authenticated and authorized
        // You should implement proper authorization checks here
        
        if (!is_numeric($id)) {
            $this->setResponse(400, null, 'Invalid booking ID');
            return;
        }

        $this->booking->id = (int)$id;
        
        // Check if booking exists
        if (!$this->booking->readOne()) {
            $this->setResponse(404, null, 'Booking not found');
            return;
        }
        
        // Check if user owns this booking or is admin
        // Authorization logic should go here
        
        if ($this->booking->delete()) {
            $this->setResponse(200, ['message' => 'Booking deleted successfully']);
        } else {
            $this->setResponse(500, null, 'Failed to delete booking');
        }
    }
    
    protected function validateRequiredFields($data, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return false;
            }
        }
        return true;
    }
} 