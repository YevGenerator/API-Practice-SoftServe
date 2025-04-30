# Cinema REST API

A RESTful API for a cinema system that allows users to view movies, check available sessions, and book seats.

## Setup Instructions

1. Clone the repository
2. Create a MySQL database named `cinema_db`
3. Import the database schema from `database/schema.sql`
4. Configure your web server (Apache/nginx) to point to the project directory
5. Update database credentials in `config/database.php` if needed

## API Endpoints

### Movies

#### Get all movies
```
GET /api/movies
```

Response:
```json
[
  {
    "id": 1,
    "title": "Interstellar",
    "description": "A sci-fi epic about space and time travel.",
    "duration": 169,
    "posterUrl": "https://example.com/posters/interstellar.jpg"
  }
]
```

#### Get movie details
```
GET /api/movies/{id}
```

Response:
```json
{
  "id": 1,
  "title": "Interstellar",
  "description": "A sci-fi epic about space and time travel.",
  "duration": 169,
  "posterUrl": "https://example.com/posters/interstellar.jpg"
}
```

### Sessions

#### Get sessions for a movie
```
GET /api/sessions?movie_id={id}
```

Response:
```json
[
  {
    "sessionId": 101,
    "startTime": "2025-04-28T15:00:00",
    "hall": "Hall 1",
    "price": 350.00
  }
]
```

### Bookings

#### Book seats
```
POST /api/bookings
```

Request body:
```json
{
  "sessionId": 101,
  "seats": [5, 6, 7],
  "customerName": "John Doe"
}
```

Success response:
```json
{
  "status": "success",
  "message": "Booking completed",
  "bookingIds": [5001, 5002, 5003]
}
```

## Error Responses

### 400 Bad Request
```json
{
  "status": "error",
  "message": "Invalid input"
}
```

### 404 Not Found
```json
{
  "status": "error",
  "message": "Resource not found"
}
```

### 409 Conflict
```json
{
  "status": "error",
  "message": "Seat 5 is already booked"
}
```

### 500 Internal Server Error
```json
{
  "status": "error",
  "message": "Internal server error"
}
```

## Business Rules

1. Seat numbers must be in the range 1-50
2. A seat can only be booked once per session
3. The session must exist before creating a booking
4. All responses are in JSON format

## Technologies Used

- PHP 8.x
- MySQL 8.x
- Apache/nginx
- PDO for database operations 