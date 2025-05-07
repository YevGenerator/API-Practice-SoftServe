# Movie Cinema API Documentation

This document provides detailed information about the Movie Cinema API endpoints, request/response formats, and authentication requirements.

## Base URL

```
/api
```

## Authentication

Some endpoints require authentication using JWT tokens. To authenticate:

1. Obtain a token by making a POST request to `/api/login`
2. Include the token in the Authorization header of subsequent requests:
   ```
   Authorization: Bearer <your_jwt_token>
   ```

## Error Responses

All API endpoints return errors in the following format:

```json
{
  "status": "error",
  "message": "Error description"
}
```

Common HTTP status codes:
- 200: Success
- 201: Resource created
- 400: Bad request (missing or invalid parameters)
- 401: Unauthorized (authentication required)
- 403: Forbidden (insufficient permissions)
- 404: Resource not found
- 405: Method not allowed
- 500: Internal server error

## API Endpoints

### Movies

#### Get Movies List

```
GET /api/movies
```

Returns a list of movies. Can be filtered by genre, year, and rating.

**Query Parameters:**
- `genre` (optional): Filter by movie genre
- `year` (optional): Filter by release year
- `rating` (optional): Filter by minimum rating

**Response (200 OK):**
```json
[
  {
    "id": 1,
    "title": "The Shawshank Redemption",
    "description": "Two imprisoned men bond over a number of years...",
    "duration": 142,
    "posterUrl": "https://example.com/posters/shawshank.jpg",
    "genre": "Drama",
    "year": 1994,
    "rating": 9.3,
    "actors": "Tim Robbins, Morgan Freeman"
  },
  // More movies...
]
```

#### Get Movie Details

```
GET /api/movies/{id}
```

Returns detailed information about a specific movie.

**Path Parameters:**
- `id`: Movie ID

**Response (200 OK):**
```json
{
  "id": 1,
  "title": "The Shawshank Redemption",
  "description": "Two imprisoned men bond over a number of years...",
  "duration": 142,
  "posterUrl": "https://example.com/posters/shawshank.jpg",
  "genre": "Drama",
  "year": 1994,
  "rating": 9.3,
  "actors": "Tim Robbins, Morgan Freeman"
}
```

#### Add Movie

```
POST /api/movies
```

Adds a new movie (admin only).

**Request Body:**
```json
{
  "title": "The Godfather",
  "description": "The aging patriarch of an organized crime dynasty...",
  "duration": 175,
  "posterUrl": "https://example.com/posters/godfather.jpg",
  "genre": "Crime",
  "year": 1972,
  "rating": 9.2,
  "actors": "Marlon Brando, Al Pacino"
}
```

**Required Fields:**
- `title`
- `description`
- `duration`

**Response (201 Created):**
```json
{
  "id": 2,
  "message": "Movie created"
}
```

#### Update Movie

```
PUT /api/movies/{id}
```

Updates an existing movie (admin only).

**Path Parameters:**
- `id`: Movie ID

**Request Body:**
```json
{
  "title": "The Godfather",
  "description": "Updated description...",
  "duration": 175,
  "posterUrl": "https://example.com/posters/godfather.jpg",
  "genre": "Crime",
  "year": 1972,
  "rating": 9.2,
  "actors": "Marlon Brando, Al Pacino"
}
```

**Response (200 OK):**
```json
{
  "message": "Movie updated"
}
```

#### Delete Movie

```
DELETE /api/movies/{id}
```

Deletes a movie (admin only).

**Path Parameters:**
- `id`: Movie ID

**Response (200 OK):**
```json
{
  "message": "Movie deleted"
}
```

### Sessions (Showtimes)

#### Get Sessions List

```
GET /api/sessions
```

Returns a list of movie sessions/showtimes. Can be filtered by date, time, genre, and movie ID.

**Query Parameters:**
- `date` (optional): Filter by date (YYYY-MM-DD)
- `time` (optional): Filter by minimum time (HH:MM:SS)
- `genre` (optional): Filter by movie genre
- `movieId` (optional): Filter by movie ID

**Response (200 OK):**
```json
[
  {
    "id": 1,
    "movieId": 1,
    "movieTitle": "The Shawshank Redemption",
    "genre": "Drama",
    "startTime": "2023-08-15 12:00:00",
    "hall": "Hall 1",
    "price": 10.00
  },
  // More sessions...
]
```

#### Get Session Details

```
GET /api/sessions/{id}
```

Returns detailed information about a specific session.

**Path Parameters:**
- `id`: Session ID

**Response (200 OK):**
```json
{
  "id": 1,
  "movieId": 1,
  "startTime": "2023-08-15 12:00:00",
  "hall": "Hall 1",
  "price": 10.00,
  "bookedSeats": ["A1", "A2", "B5"]
}
```

#### Add Session

```
POST /api/sessions
```

Adds a new session (admin only).

**Request Body:**
```json
{
  "movieId": 1,
  "startTime": "2023-08-16 12:00:00",
  "hall": "Hall 1",
  "price": 10.00
}
```

**Required Fields:**
- `movieId`
- `startTime`
- `hall`
- `price`

**Response (201 Created):**
```json
{
  "id": 6,
  "message": "Session created"
}
```

#### Update Session

```
PUT /api/sessions/{id}
```

Updates an existing session (admin only).

**Path Parameters:**
- `id`: Session ID

**Request Body:**
```json
{
  "movieId": 1,
  "startTime": "2023-08-16 14:00:00",
  "hall": "Hall 2",
  "price": 12.00
}
```

**Response (200 OK):**
```json
{
  "message": "Session updated"
}
```

#### Delete Session

```
DELETE /api/sessions/{id}
```

Deletes a session (admin only).

**Path Parameters:**
- `id`: Session ID

**Response (200 OK):**
```json
{
  "message": "Session deleted"
}
```

### User Management

#### Register User

```
POST /api/register
```

Registers a new user.

**Request Body:**
```json
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "password123"
}
```

**Required Fields:**
- `username`
- `email`
- `password`

**Response (201 Created):**
```json
{
  "id": 3,
  "message": "User registered successfully"
}
```

#### Login

```
POST /api/login
```

Authenticates a user and returns a JWT token.

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Required Fields:**
- `email`
- `password`

**Response (200 OK):**
```json
{
  "id": 2,
  "username": "john_doe",
  "email": "john@example.com",
  "role": "user",
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
}
```

#### Get User Details

```
GET /api/users/{id}
```

Returns information about a specific user (authenticated users can only access their own data).

**Path Parameters:**
- `id`: User ID

**Authentication Required: Yes**

**Response (200 OK):**
```json
{
  "id": 2,
  "username": "john_doe",
  "email": "john@example.com",
  "role": "user",
  "created_at": "2023-08-10 15:30:45"
}
```

### Favorites

#### Get User Favorites

```
GET /api/users/{userId}/favorites
```

Returns a list of user's favorite movies.

**Path Parameters:**
- `userId`: User ID

**Authentication Required: Yes**

**Response (200 OK):**
```json
[
  {
    "id": 1,
    "title": "The Shawshank Redemption",
    "description": "Two imprisoned men bond over a number of years...",
    "duration": 142,
    "posterUrl": "https://example.com/posters/shawshank.jpg",
    "genre": "Drama",
    "year": 1994,
    "rating": 9.3
  },
  // More favorite movies...
]
```

#### Add Movie to Favorites

```
POST /api/users/{userId}/favorites
```

Adds a movie to user's favorites.

**Path Parameters:**
- `userId`: User ID

**Request Body:**
```json
{
  "movieId": 3
}
```

**Required Fields:**
- `movieId`

**Authentication Required: Yes**

**Response (200 OK):**
```json
{
  "message": "Movie added to favorites"
}
```

#### Remove Movie from Favorites

```
DELETE /api/users/{userId}/favorites/{movieId}
```

Removes a movie from user's favorites.

**Path Parameters:**
- `userId`: User ID
- `movieId`: Movie ID

**Authentication Required: Yes**

**Response (200 OK):**
```json
{
  "message": "Movie removed from favorites"
}
```

### Bookings

#### Create Booking

```
POST /api/bookings
```

Creates a new booking/ticket reservation.

**Request Body:**
```json
{
  "session_id": 1,
  "seat_number": "C3",
  "customer_name": "John Doe",
  "customer_email": "john@example.com"
}
```

**Required Fields:**
- `session_id`
- `seat_number`
- `customer_name`
- `customer_email`

**Authentication Required: Yes**

**Response (201 Created):**
```json
{
  "id": 4,
  "sessionId": 1,
  "seatNumber": "C3",
  "customerName": "John Doe",
  "customerEmail": "john@example.com"
}
```

#### Get User Bookings History

```
GET /api/users/{userId}/bookings
```

Returns a list of user's bookings.

**Path Parameters:**
- `userId`: User ID

**Authentication Required: Yes**

**Response (200 OK):**
```json
[
  {
    "id": 1,
    "sessionId": 1,
    "userId": 2,
    "seatNumber": "A1",
    "bookingDate": "2023-08-10 14:23:45",
    "movieTitle": "The Shawshank Redemption",
    "startTime": "2023-08-15 12:00:00",
    "hall": "Hall 1",
    "price": 10.00
  },
  // More bookings...
]
```

### Recommendations

#### Get User Recommendations

```
GET /api/users/{userId}/recommendations
```

Returns personalized movie recommendations based on user's booking history and favorites.

**Path Parameters:**
- `userId`: User ID

**Authentication Required: Yes**

**Response (200 OK):**
```json
[
  {
    "id": 4,
    "title": "Inception",
    "description": "A thief who steals corporate secrets...",
    "duration": 148,
    "posterUrl": "https://example.com/posters/inception.jpg",
    "genre": "Sci-Fi",
    "year": 2010,
    "rating": 8.8
  },
  // More recommended movies...
]
```

### Admin Panel

#### Update Pricing

```
PUT /api/pricing
```

Updates ticket prices (admin only).

**Request Body:**
```json
{
  "pricingData": [
    {
      "sessionId": 1,
      "price": 12.00
    },
    {
      "movieId": 2,
      "price": 15.00
    },
    {
      "hallType": "Hall 1",
      "price": 14.00
    }
  ]
}
```

**Authentication Required: Yes (Admin)**

**Response (200 OK):**
```json
{
  "message": "Pricing updated successfully",
  "sessionsUpdated": 5
}
```

#### Get Statistics

```
GET /api/statistics
```

Returns various statistics and metrics (admin only).

**Authentication Required: Yes (Admin)**

**Response (200 OK):**
```json
{
  "totalMovies": 4,
  "totalSessions": 5,
  "totalBookings": 3,
  "totalUsers": 2,
  "totalRevenue": 32.00,
  "popularMovies": [
    {
      "id": 1,
      "title": "The Shawshank Redemption",
      "booking_count": 2
    },
    // More popular movies...
  ],
  "popularGenres": [
    {
      "genre": "Drama",
      "booking_count": 2
    },
    // More genres...
  ],
  "bookingsByMonth": [
    {
      "month": "2023-08",
      "booking_count": 3,
      "revenue": 32.00
    },
    // More monthly data...
  ]
}
```

## Security Considerations

1. **Authentication**: JWT tokens are used to authenticate users.
2. **Authorization**: Role-based access control ensures only authorized users can access certain endpoints.
3. **Input Validation**: All user inputs are validated and sanitized to prevent SQL injection and other attacks.
4. **Error Handling**: Detailed error messages are provided for developers while maintaining security.
5. **CORS**: Cross-Origin Resource Sharing headers are set to allow secure cross-origin requests.

## Rate Limiting

The API implements rate limiting to prevent abuse. If you exceed the rate limit, you will receive a 429 (Too Many Requests) response.

## Pagination

For endpoints returning potentially large sets of data, pagination is supported using the following query parameters:
- `page`: Page number (default: 1)
- `limit`: Items per page (default: 20, max: 100)

## Versioning

This documentation describes API version 1.0. Future versions may introduce breaking changes. 