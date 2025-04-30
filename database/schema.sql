-- Create the database
CREATE DATABASE IF NOT EXISTS cinema_db;
USE cinema_db;

-- Create movies table
CREATE TABLE IF NOT EXISTS movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT NOT NULL,
    poster_url VARCHAR(255)
);

-- Create sessions table
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    hall VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (movie_id) REFERENCES movies(id)
);

-- Create bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    seat_number INT NOT NULL,
    FOREIGN KEY (session_id) REFERENCES sessions(id)
);

-- Add some sample data
INSERT INTO movies (title, description, duration, poster_url) VALUES
('Interstellar', 'A sci-fi epic about space and time travel.', 169, 'https://example.com/posters/interstellar.jpg'),
('Inception', 'A thief who steals corporate secrets through the use of dream-sharing technology.', 148, 'https://example.com/posters/inception.jpg');

INSERT INTO sessions (movie_id, start_time, hall, price) VALUES
(1, '2025-04-28 15:00:00', 'Hall 1', 350.00),
(1, '2025-04-28 18:00:00', 'Hall 2', 350.00),
(2, '2025-04-28 16:00:00', 'Hall 1', 300.00);

-- Add indexes for better performance
CREATE INDEX idx_movie_id ON sessions(movie_id);
CREATE INDEX idx_session_id ON bookings(session_id);
CREATE INDEX idx_seat_number ON bookings(seat_number); 