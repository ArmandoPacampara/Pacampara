CREATE DATABASE moviease_db;
USE moviease_db;

CREATE TABLE users (
    user_ID INT AUTO_INCREMENT PRIMARY KEY,
    role_ID INT NOT NULL,
    user_Name VARCHAR(100) NOT NULL,
    user_Email VARCHAR(100) UNIQUE NOT NULL,
    user_contact VARCHAR(15),
    user_Password VARCHAR(255) NOT NULL,
    email_Recovery VARCHAR(255) NOT NULL,
    user_avatar VARCHAR(255) DEFAULT 'account_icon.png'
);


CREATE TABLE roles (
    role_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_Role ENUM('Admin', 'Customer', 'Staff') NOT NULL
);

CREATE TABLE movies (
    movie_ID INT AUTO_INCREMENT PRIMARY KEY,
    movie_Name VARCHAR(255) NOT NULL,
    movie_Hours TIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    movie_Class ENUM('G', 'PG', 'SPG', 'R13', 'R16', 'R18') NOT NULL,
    genre ENUM('Action', 'Comedy', 'Drama', 'Horror', 'Romance', 'Sci-Fi', 'Thriller', 'Animation') NOT NULL,
    movie_Status ENUM('Now Showing', 'Coming Soon') DEFAULT 'Coming Soon',
    movie_trailer VARCHAR(255),
    movie_poster VARCHAR(255) DEFAULT 'default_poster.jpg',
    movie_description TEXT
);


CREATE TABLE cinemas (
    cinema_ID INT AUTO_INCREMENT PRIMARY KEY,
    cinema_Name VARCHAR(100) NOT NULL,
    cinema_Address VARCHAR(255) NOT NULL,
    cinema_Contact VARCHAR(20),
    cinema_Logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cinema_movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cinema_ID INT NOT NULL,
    movie_ID INT NOT NULL,
    showtime DATETIME,
    ticket_price DECIMAL(10,2),

    FOREIGN KEY (cinema_ID) 
        REFERENCES cinemas(cinema_ID) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE,

    FOREIGN KEY (movie_ID) 
        REFERENCES movies(movie_ID) 
        ON DELETE CASCADE 
        ON UPDATE CASCADE
);


CREATE TABLE seats (
    seat_ID INT AUTO_INCREMENT PRIMARY KEY,
    Seat_number varchar(3) NOT NULL,
    cinema_id INT NOT NULL,
    schedule DATETIME NOT NULL,
    status ENUM('Available', 'NA') DEFAULT 'Available',
    FOREIGN KEY (cinema_id) REFERENCES cinemas(cinema_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE booking (
    ticket_ID INT AUTO_INCREMENT PRIMARY KEY,
    user_ID INT NOT NULL,
    movie_ID INT NOT NULL,
    seat_ID INT NOT NULL,
    date_booked DATE NOT NULL,
    schedule DATETIME NOT NULL,
    ticket_Quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('Booked', 'Cancelled', 'Completed') DEFAULT 'Booked',
    FOREIGN KEY (user_ID) REFERENCES users(user_ID)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (movie_ID) REFERENCES movies(movie_ID)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (seat_ID) REFERENCES seats(seat_ID)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE calendar (
    calendar_ID INT AUTO_INCREMENT PRIMARY KEY,
    movie_ID INT NOT NULL,
    schedule DATETIME NOT NULL,
    weather VARCHAR(100),
    FOREIGN KEY (movie_ID) REFERENCES movies(movie_ID)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE recommendations (
  recommendation_ID INT AUTO_INCREMENT PRIMARY KEY,
  user_ID INT,
  movie_ID INT,
  reason VARCHAR(255), -- e.g., "Movies you might like", "Popular this week"
  recommended_Date DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_ID) REFERENCES users(user_ID),
  FOREIGN KEY (movie_ID) REFERENCES movies(movie_ID)
);


ALTER TABLE users
ADD CONSTRAINT fk_user_role
FOREIGN KEY (role_ID) REFERENCES roles(role_ID)
ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE recommendations
DROP FOREIGN KEY recommendations_ibfk_1;

ALTER TABLE recommendations
ADD CONSTRAINT recommendations_ibfk_1
FOREIGN KEY (user_ID) REFERENCES users(user_ID)
ON DELETE CASCADE;

ALTER TABLE recommendations
DROP FOREIGN KEY recommendations_ibfk_2;

ALTER TABLE recommendations
ADD CONSTRAINT recommendations_ibfk_2
FOREIGN KEY (movie_ID) REFERENCES movies(movie_ID)
ON DELETE CASCADE;

ALTER TABLE calendar
DROP FOREIGN KEY calendar_ibfk_1;


ALTER TABLE calendar
ADD CONSTRAINT calendar_ibfk_1
FOREIGN KEY (movie_ID) REFERENCES movies(movie_ID)
ON DELETE CASCADE;


ALTER TABLE movies MODIFY movie_Status VARCHAR(50);
