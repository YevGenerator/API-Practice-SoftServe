# Cinema API

API for managing a cinema system. Allows viewing movies, checking sessions, and booking seats.

## API Endpoints

### Movies

- `GET /api/movies` - Get all movies
- `GET /api/movies/{id}` - Get movie details

### Sessions

- `GET /api/sessions` - Get all sessions
- `GET /api/sessions/{id}` - Get session details
- `GET /api/sessions?movie_id={id}` - Get sessions for a specific movie

### Bookings

- `GET /api/bookings` - Get all bookings
- `GET /api/bookings/{id}` - Get booking details
- `POST /api/bookings` - Create a new booking

## Request Examples

### Get all movies
```http
GET /api/movies
```

### Get movie details
```http
GET /api/movies/1
```

### Get sessions for a movie
```http
GET /api/sessions?movie_id=1
```

### Create a booking
```http
POST /api/bookings
Content-Type: application/json

{
    "session_id": 1,
    "seat_number": "A5",
    "customer_name": "John Doe",
    "customer_email": "john@example.com"
}
```

## API Responses

### Success Response
```json
{
    "status": "success",
    "data": {
        // Response data
    },
    "message": null
}
```

### Error Response
```json
{
    "status": "error",
    "data": null,
    "message": "Error description"
}
```

## Response Codes

- 200 - Success
- 201 - Created
- 400 - Bad Request
- 404 - Not Found
- 405 - Method Not Allowed
- 500 - Internal Server Error

## Setup Instructions

1. Clone the repository
2. Create a MySQL database named `cinema_db`
3. Import the database schema from `database/schema.sql`
4. Configure your web server (Apache/nginx) to point to the project directory
5. Update database credentials in `config/database.php` if needed

## Business Rules

1. Seat numbers must be unique for each session
2. A session must exist before creating a booking
3. All responses are in JSON format
4. Customer email is optional for bookings

## Technologies Used

- PHP 8.x
- MySQL 8.x
- Apache/nginx
- PDO for database operations 