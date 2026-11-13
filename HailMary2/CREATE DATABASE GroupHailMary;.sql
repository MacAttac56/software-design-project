/*
De'Jon Mack 
9/28/26 
v#00610825
Sql Database
*/
CREATE DATABASE GroupHailMary;


USE GroupHailMary;


CREATE TABLE Schools (
    School_ID INT PRIMARY KEY AUTO_INCREMENT,
    School_Name VARCHAR(100) NOT NULL,
    City VARCHAR(50) NOT NULL,
    State VARCHAR(50) NOT NULL,
    Domain VARCHAR(100) NOT NULL UNIQUE
);


/* Dummy data*/
INSERT INTO Schools
(School_ID, School_Name, City, State, Domain)
VALUES
(1, 'Virginia State University', 'Petersburg', 'VA', 'vsu.edu'),
(2, 'Virginia Commonwealth University', 'Richmond', 'VA', 'vcu.edu'),
(3, 'Virginia Tech', 'Blacksburg', 'VA', 'vt.edu'),
(4, 'Old Dominion University', 'Norfolk', 'VA', 'odu.edu'),
(5, 'George Mason University', 'Fairfax', 'VA', 'gmu.edu');


CREATE TABLE Students (
    Student_ID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL,
    School_ID INT NOT NULL,
    School_Email VARCHAR(150) NOT NULL UNIQUE,
    Phone_Number VARCHAR(20) NOT NULL,
    Email_Verified BOOLEAN DEFAULT FALSE,
    Phone_Verified BOOLEAN DEFAULT FALSE,


    FOREIGN KEY (School_ID)
        REFERENCES Schools(School_ID)
);


/* Dummy data*/


INSERT INTO Students
(Student_ID, Name, School_ID, School_Email, Phone_Number, Email_Verified, Phone_Verified)
VALUES
(1, 'DeJon Mack', 1, 'dmack@vsu.edu', '804-555-1001', TRUE, TRUE),
(2, 'Christian Davis', 1, 'cdavis@vsu.edu', '804-555-1002', TRUE, TRUE),
(3, 'Marcus Johnson', 2, 'mjohnson@vcu.edu', '804-555-1003', TRUE, TRUE),
(4, 'Jasmine Williams', 2, 'jwilliams@vcu.edu', '804-555-1004', TRUE, FALSE),
(5, 'Tyler Brown', 3, 'tbrown@vt.edu', '540-555-1005', TRUE, TRUE),
(6, 'Aaliyah Smith', 4, 'asmith@odu.edu', '757-555-1006', TRUE, TRUE),
(7, 'Jordan Wilson', 5, 'jwilson@gmu.edu', '703-555-1007', FALSE, TRUE),
(8, 'Kevin Thompson', 1, 'kthompson@vsu.edu', '804-555-1008', TRUE, TRUE);


CREATE TABLE Conversations (
    Conversation_ID INT PRIMARY KEY AUTO_INCREMENT,
    Created_Date DATETIME DEFAULT CURRENT_TIMESTAMP
);


/* Dummy data*/


INSERT INTO Conversations
(Conversation_ID, Created_Date)
VALUES
(1, '2026-09-20 10:30:00'),
(2, '2026-09-20 14:15:00'),
(3, '2026-09-21 09:45:00'),
(4, '2026-09-22 11:20:00'),
(5, '2026-09-23 16:30:00');


CREATE TABLE Conversation_Participants (
    Conversation_ID INT NOT NULL,
    Student_ID INT NOT NULL,


    PRIMARY KEY (Conversation_ID, Student_ID),


    FOREIGN KEY (Conversation_ID)
        REFERENCES Conversations(Conversation_ID),


    FOREIGN KEY (Student_ID)
        REFERENCES Students(Student_ID)
);


/* Dummy data*/


INSERT INTO Conversation_Participants
(Conversation_ID, Student_ID)
VALUES
(1, 1),
(1, 2),


(2, 1),
(2, 3),


(3, 2),
(3, 4),


(4, 5),
(4, 6),


(5, 1),
(5, 6);


CREATE TABLE Messages (
    Message_ID INT PRIMARY KEY AUTO_INCREMENT,
    Conversation_ID INT NOT NULL,
    Sender_ID INT NOT NULL,
    Message_Text TEXT NOT NULL,
    Sent_Date DATETIME DEFAULT CURRENT_TIMESTAMP,


    FOREIGN KEY (Conversation_ID)
        REFERENCES Conversations(Conversation_ID),


    FOREIGN KEY (Sender_ID)
        REFERENCES Students(Student_ID)
);


/* Dummy data*/


INSERT INTO Messages
(Message_ID, Conversation_ID, Sender_ID, Message_Text, Sent_Date)
VALUES
(1, 1, 1, 'Is the database textbook still available?', '2026-09-20 10:35:00'),


(2, 1, 2, 'Yes, it is still available.', '2026-09-20 10:37:00'),


(3, 1, 1, 'Would you take $25 for it?', '2026-09-20 10:40:00'),


(4, 1, 2, 'Sure, $25 works for me.', '2026-09-20 10:42:00'),


(5, 2, 3, 'Are the basketball shoes still available?', '2026-09-20 14:20:00'),


(6, 2, 1, 'Yes, they are.', '2026-09-20 14:25:00'),


(7, 3, 2, 'Is the calculator available?', '2026-09-21 09:50:00'),


(8, 3, 4, 'Yes, I can meet on campus.', '2026-09-21 09:55:00'),


(9, 4, 5, 'Would you be willing to trade for the Xbox controller?', '2026-09-22 11:25:00'),


(10, 4, 6, 'I might be interested. What do you have?', '2026-09-22 11:30:00');


CREATE TABLE Categories (
    Category_ID INT PRIMARY KEY AUTO_INCREMENT,
    Description VARCHAR(100) NOT NULL
);


/* Dummy data*/


INSERT INTO Categories
(Category_ID, Description)
VALUES
(1, 'Electronics'),
(2, 'Textbooks'),
(3, 'Clothing'),
(4, 'Furniture'),
(5, 'School Supplies'),
(6, 'Gaming'),
(7, 'Other');


CREATE TABLE Listings (
    Listing_ID INT PRIMARY KEY AUTO_INCREMENT,
    Seller_ID INT NOT NULL,
    Title VARCHAR(150) NOT NULL,
    Description TEXT,
    Price DECIMAL(10,2) NOT NULL,
    Category_ID INT NOT NULL,
    `Condition` VARCHAR(50),
    Status VARCHAR(30) DEFAULT 'Active',
    Created_Date DATETIME DEFAULT CURRENT_TIMESTAMP,


    FOREIGN KEY (Seller_ID)
        REFERENCES Students(Student_ID),


    FOREIGN KEY (Category_ID)
        REFERENCES Categories(Category_ID)
);


/* Dummy data*/


INSERT INTO Listings
(Listing_ID, Seller_ID, Title, Description, Price, Category_ID, `Condition`, Status, Created_Date)
VALUES
(1, 1, 'TI-84 Calculator',
 'TI-84 calculator in good working condition.',
 45.00, 1, 'Good', 'Active', '2026-09-18 10:30:00'),


(2, 2, 'Database Systems Textbook',
 'Used textbook for database systems classes.',
 30.00, 2, 'Good', 'Active', '2026-09-18 14:20:00'),


(3, 3, 'Nike Basketball Shoes',
 'Size 11 Nike basketball shoes.',
 55.00, 3, 'Like New', 'Active', '2026-09-19 09:15:00'),


(4, 4, 'Mini Refrigerator',
 'Small refrigerator suitable for a dorm room.',
 75.00, 4, 'Good', 'Active', '2026-09-19 12:45:00'),


(5, 5, 'Laptop Backpack',
 'Black backpack with multiple laptop compartments.',
 20.00, 5, 'Like New', 'Sold', '2026-09-20 11:00:00'),


(6, 6, 'Xbox Controller',
 'Xbox controller in working condition.',
 35.00, 6, 'Good', 'Active', '2026-09-20 15:30:00'),


(7, 8, 'Java Programming Book',
 'Java programming textbook with some notes inside.',
 25.00, 2, 'Good', 'Active', '2026-09-21 10:10:00'),


(8, 1, 'Desk Lamp',
 'LED desk lamp that works well for studying.',
 15.00, 4, 'Like New', 'Active', '2026-09-21 16:00:00'),


(9, 3, 'Wireless Headphones',
 'Wireless headphones with charging case.',
 40.00, 1, 'Good', 'Active', '2026-09-22 13:25:00'),


(10, 6, 'Gaming Mouse',
 'USB gaming mouse with adjustable DPI.',
 25.00, 6, 'Like New', 'Active', '2026-09-23 17:40:00');


CREATE TABLE Listing_Images (
    Listing_Image_ID INT PRIMARY KEY AUTO_INCREMENT,
    Listing_ID INT NOT NULL,
    Image_URL VARCHAR(500) NOT NULL,
    Sort_Order INT DEFAULT 1,


    FOREIGN KEY (Listing_ID)
        REFERENCES Listings(Listing_ID)
);


/* Dummy data*/


/*INSERT INTO Listing_Images
(Listing_Image_ID, Listing_ID, Image_URL, Sort_Order)
VALUES
(1, 1, 'calculator.jpg', 1),
(2, 2, 'database_textbook.jpg', 1),
(3, 3, 'basketball_shoes.jpg', 1),
(4, 4, 'mini_fridge.jpg', 1),
(5, 5, 'backpack.jpg', 1),
(6, 6, 'xbox_controller.jpg', 1),
(7, 7, 'java_book.jpg', 1),
(8, 8, 'desk_lamp.jpg', 1),
(9, 9, 'headphones.jpg', 1),
(10, 10, 'gaming_mouse.jpg', 1); */
CREATE TABLE Reports (
    Report_ID INT PRIMARY KEY AUTO_INCREMENT,
    Reporter_ID INT NOT NULL,
    Reported_User_ID INT NOT NULL,
    Reason TEXT NOT NULL,
    Status VARCHAR(30) DEFAULT 'Pending',
    Created_Date DATETIME DEFAULT CURRENT_TIMESTAMP,


    FOREIGN KEY (Reporter_ID)
        REFERENCES Students(Student_ID),


    FOREIGN KEY (Reported_User_ID)
        REFERENCES Students(Student_ID)
);


/* Dummy data*/


INSERT INTO Reports
(Report_ID, Reporter_ID, Reported_User_ID, Reason, Status, Created_Date)
VALUES
(1, 1, 3, 'Listing description did not match the item.', 'Pending', '2026-09-21 12:00:00'),


(2, 4, 2, 'User did not show up for the agreed meeting.', 'Resolved', '2026-09-21 15:30:00'),


(3, 6, 5, 'User attempted to sell an item that was not available.', 'Pending', '2026-09-22 13:45:00'),


(4, 2, 7, 'Suspicious listing information.', 'Under Review', '2026-09-23 09:20:00'),


(5, 8, 3, 'User provided misleading pricing information.', 'Resolved', '2026-09-23 14:10:00');


/* Select Quieres*/


/* Find verified students */
SELECT
    Student_ID,
    Name,
    School_Email,
    Phone_Number
FROM Students
WHERE Email_Verified = TRUE
AND Phone_Verified = TRUE;


/*Find items under $50*/
SELECT
    Listing_ID,
    Title,
    Price,
    `Condition`,
    Status
FROM Listings
WHERE Price <= 50
AND Status = 'Active'
ORDER BY Price ASC;


/*Show listings and sellers*/
SELECT
    Listings.Listing_ID,
    Listings.Title,
    Listings.Price,
    Students.Name AS Seller
FROM Listings
JOIN Students
    ON Listings.Seller_ID = Students.Student_ID
WHERE Listings.Status = 'Active'
ORDER BY Listings.Price ASC;


/*Show listings from vsu*/
SELECT
    Listings.Title,
    Listings.Price,
    Students.Name AS Seller,
    Schools.School_Name
FROM Listings
JOIN Students
    ON Listings.Seller_ID = Students.Student_ID
JOIN Schools
    ON Students.School_ID = Schools.School_ID
WHERE Schools.School_Name = 'Virginia State University'
AND Listings.Status = 'Active';


/*Find a specific message*/
SELECT
    Messages.Message_ID,
    Students.Name AS Sender,
    Messages.Message_Text,
    Messages.Sent_Date
FROM Messages
JOIN Students
    ON Messages.Sender_ID = Students.Student_ID
WHERE Messages.Conversation_ID = 1
ORDER BY Messages.Sent_Date ASC;


/*Look for specific student listing*/
SELECT
    Listing_ID,
    Title,
    Price,
    `Condition`,
    Status
FROM Listings
WHERE Seller_ID = 1;
