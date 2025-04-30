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
            'seatNumber' => $this->booking->seat_number,
            'customerName' => $this->booking->customer_name,
            'customerEmail' => $this->booking->customer_email
        ];

        $this->setResponse(200, $bookingData);
    }

    private function createBooking() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$this->validateRequiredFields($data, ['session_id', 'seat_number', 'customer_name', 'customer_email'])) {
            $this->setResponse(400, null, 'Missing required fields');
            return;
        }

        $this->booking->session_id = $data['session_id'];
        $this->booking->seat_number = $data['seat_number'];
        $this->booking->customer_name = $data['customer_name'];
        $this->booking->customer_email = $data['customer_email'];

        if (!$this->booking->create()) {
            $this->setResponse(400, null, 'Unable to create booking');
            return;
        }

        $this->setResponse(201, [
            'id' => $this->booking->id,
            'sessionId' => $this->booking->session_id,
            'seatNumber' => $this->booking->seat_number,
            'customerName' => $this->booking->customer_name,
            'customerEmail' => $this->booking->customer_email
        ]);
    }
} 