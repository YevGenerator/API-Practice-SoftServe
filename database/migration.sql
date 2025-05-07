-- Movies Cinema API Database Schema

-- Drop tables if they exist (in the correct order to maintain referential integrity)
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS movies;
DROP TABLE IF EXISTS users;

-- Create Movies table
CREATE TABLE movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    poster_url VARCHAR(255),
    genre VARCHAR(100) COMMENT 'Movie genre',
    year INT COMMENT 'Release year',
    rating DECIMAL(3,1) COMMENT 'Movie rating (0-10)',
    actors TEXT COMMENT 'List of main actors',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Sessions (Showtimes) table
CREATE TABLE sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    hall VARCHAR(50) NOT NULL COMMENT 'Theater hall identifier',
    price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
);

-- Create Bookings (Tickets) table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    user_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL COMMENT 'Format like "A1", "B5", etc.',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (session_id) REFERENCES sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_seat_booking (session_id, seat_number) COMMENT 'Ensures a seat can only be booked once per session'
);

-- Create Favorites table
CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, movie_id) COMMENT 'Ensures a movie can only be added once to favorites per user'
);

-- Create Admin user
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@example.com', '$2y$10$FnPWANpRxJvlEw.jJPQ1veZDQR3pJZ7D0MTPxNe/NKN.EtqQMPAuG', 'admin'); -- Password: admin123

-- Insert sample movies
INSERT INTO movies (title, description, duration, poster_url, genre, year, rating, actors) VALUES
('The Shawshank Redemption', 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.', 142, 'https://example.com/posters/shawshank.jpg', 'Drama', 1994, 9.3, 'Tim Robbins, Morgan Freeman'),
('The Godfather', 'The aging patriarch of an organized crime dynasty transfers control of his clandestine empire to his reluctant son.', 175, 'https://example.com/posters/godfather.jpg', 'Crime', 1972, 9.2, 'Marlon Brando, Al Pacino'),
('The Dark Knight', 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.', 152, 'https://example.com/posters/darkknight.jpg', 'Action', 2008, 9.0, 'Christian Bale, Heath Ledger'),
('Inception', 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.', 148, 'https://example.com/posters/inception.jpg', 'Sci-Fi', 2010, 8.8, 'Leonardo DiCaprio, Joseph Gordon-Levitt');

-- Insert sample sessions
INSERT INTO sessions (movie_id, start_time, hall, price) VALUES
(1, '2023-08-15 12:00:00', 'Hall 1', 10.00),
(1, '2023-08-15 15:30:00', 'Hall 2', 12.00),
(2, '2023-08-15 14:00:00', 'Hall 3', 10.00),
(3, '2023-08-15 19:00:00', 'Hall 1', 15.00),
(4, '2023-08-15 20:30:00', 'Hall 2', 15.00);

-- Insert sample user
INSERT INTO users (username, email, password, role) VALUES
('john_doe', 'john@example.com', '$2y$10$9K3.H3QBKNbvL7J2XcREB.XYnYuQBrCXfGF9CgQhQrHVNO02mEPzS', 'user'); -- Password: password123

-- Insert sample bookings
INSERT INTO bookings (session_id, user_id, seat_number) VALUES
(1, 2, 'A1'),
(1, 2, 'A2'),
(3, 2, 'B5');

-- Insert sample favorites
INSERT INTO favorites (user_id, movie_id) VALUES
(2, 1),
(2, 3); 