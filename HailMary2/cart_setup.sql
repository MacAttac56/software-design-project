USE GroupHailMary;

CREATE TABLE IF NOT EXISTS Cart (
    Cart_ID INT AUTO_INCREMENT PRIMARY KEY,
    Student_ID INT NOT NULL,
    Listing_ID INT NOT NULL,
    Added_At TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (Student_ID)
        REFERENCES Students(Student_ID),

    FOREIGN KEY (Listing_ID)
        REFERENCES Listings(Listing_ID)
);