

--
-- MySQL BDELive Database
-- Created by : Mohamed-Amine Boudhib
-- For the BDELive website (could be found at this URL : https://bdelivesae.alwaysdata.net/)
-- Created for a university project. All rights reserved.
-- For any questions, please contact me by mail : mohamed-amine.boudhib@etu.univ-amu.fr
--

--
-- Name : USERS
-- Function : User management table 
-- Attributes : user_id, last_name, user_status, first_name, email, password, registration_date
--

CREATE TABLE USERS (
    user_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each user of the website. Auto-incremented each time.
    last_name VARCHAR(100) NOT NULL, -- User last name.
    user_status VARCHAR(50) NOT NULL, --  Student's year of study or a teaching staff (if he is not a student)
    first_name VARCHAR(100) NOT NULL, -- User first name
    email VARCHAR(100) UNIQUE NOT NULL, -- User e-mail
    password VARCHAR(255) NOT NULL, -- User hashed password (Hashed by BCRYPT)
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP, -- User registration date (at server timestamp)
    CONSTRAINT chk_user_status CHECK (user_status IN ('BUT 1', 'BUT 2', 'BUT 3', 'Personnel Enseignant')), -- Checking the user year if he is a student, or if he is a teaching staff.
    CONSTRAINT chk_email CHECK (email LIKE '%_@%_.__%') -- Checking email format.
);

--
-- Name : PASSWORD_RESET_TOKEN
-- Function : Token management table (for password reset) 
-- Attributes : id, user_id, token, expires_at, created_at, is_used, 
--

CREATE TABLE PASSWORD_RESET_TOKEN (
    id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each token. Generated randomly and converted in Hexadecimal.
    user_id INT UNSIGNED NOT NULL, -- Linked user of the token.
    token VARCHAR(255) UNIQUE NOT NULL, -- token generated randomly and converted in Hexadecimal.
    expires_at TIMESTAMP NOT NULL, -- Token expiration date (at server timestamp). Each 3 hours, the token is deleted.
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Token creation date (at server timestamp). 
    is_used BOOLEAN DEFAULT FALSE, -- Token usage status. If the token is used, it is set to TRUE.
    
    FOREIGN KEY (user_id) 
        REFERENCES USERS(user_id) -- Reference to the USERS table.
        ON DELETE CASCADE
);