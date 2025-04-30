<?php

class BookingController extends BaseController {
    private $booking;
    private $session;

    public function __construct($db, $requestMethod) {
        parent::__construct($db, $requestMethod);
        $this->booking = new Booking($db);
        $this->session = new Session($db);
    }

    public function processRequest() {
        try {
            switch ($this->requestMethod) {
                case 'POST':
                    $this->createBooking();
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
    }

    private function createBooking() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$this->validateBooking($input)) {
            $this->setResponse(422, null, 'Invalid input data');
            return;
        }

        $this->session->id = (int)$input['sessionId'];
        if (!$this->session->readOne()) {
            $this->setResponse(404, null, 'Session not found');
            return;
        }

        $bookingIds = [];
        foreach ($input['seats'] as $seat) {
            if (!$this->booking->isSeatAvailable($input['sessionId'], $seat)) {
                $this->setResponse(409, null, "Seat {$seat} is already booked");
                return;
            }

            $this->booking->session_id = $input['sessionId'];
            $this->booking->customer_name = $this->sanitizeInput($input['customerName']);
            $this->booking->seat_number = (int)$seat;

            $bookingId = $this->booking->create();
            if (!$bookingId) {
                $this->setResponse(500, null, 'Failed to create booking');
                return;
            }
            $bookingIds[] = $bookingId;
        }

        $this->setResponse(201, ['bookingIds' => $bookingIds], 'Booking completed successfully');
    }

    private function validateBooking($input) {
        $requiredFields = ['sessionId', 'seats', 'customerName'];
        
        if (!$this->validateRequiredFields($input, $requiredFields)) {
            return false;
        }

        if (!is_array($input['seats']) || empty($input['seats'])) {
            return false;
        }

        foreach ($input['seats'] as $seat) {
            if (!is_numeric($seat) || $seat < 1 || $seat > 50) {
                return false;
            }
        }

        return true;
    }
} 