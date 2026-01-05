/* =========================
   CREATE DATABASE
========================= */
CREATE DATABASE IF NOT EXISTS credit_tracker;
USE credit_tracker;

/* =========================
   STUDENTS TABLE
========================= */
CREATE TABLE students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  reg_no VARCHAR(20) UNIQUE NOT NULL,
  department VARCHAR(20) NOT NULL,
  regulation VARCHAR(10) NOT NULL,
  entry_type ENUM('REGULAR','LATERAL') DEFAULT 'REGULAR',
  password VARCHAR(255) NOT NULL,
  reset_otp VARCHAR(6),
  otp_expiry DATETIME
);

/* =========================
   CURRICULUM TABLE
========================= */
CREATE TABLE curriculum (
  id INT AUTO_INCREMENT PRIMARY KEY,
  department VARCHAR(20) NOT NULL,
  regulation VARCHAR(10) NOT NULL,
  course_code VARCHAR(20) NOT NULL,
  course_title VARCHAR(255) NOT NULL,
  category VARCHAR(10) NOT NULL,
  credits INT NOT NULL,
  semester INT DEFAULT 0
);

/* =========================
   STUDENT PROGRESS TABLE
========================= */
CREATE TABLE progress (
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  completed BOOLEAN DEFAULT 0,
  grade VARCHAR(2),
  PRIMARY KEY (student_id, subject_id),
  FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (subject_id) REFERENCES curriculum(id) ON DELETE CASCADE
);

/* =========================
   CREDIT REQUIREMENTS TABLE
========================= */
CREATE TABLE credit_requirements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  department VARCHAR(20) NOT NULL,
  regulation VARCHAR(10) NOT NULL,
  entry_type ENUM('REGULAR','LATERAL') NOT NULL,
  category VARCHAR(10) NOT NULL,
  required_credits INT NOT NULL
);

/* =========================
   DEPARTMENTS MASTER
========================= */
CREATE TABLE departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  dept_code VARCHAR(10) UNIQUE NOT NULL,
  dept_name VARCHAR(100) NOT NULL
);

INSERT INTO departments (dept_code, dept_name) VALUES
('CSE','Computer Science and Engineering'),
('AIML','Artificial Intelligence & Machine Learning'),
('IT','Information Technology'),
('AIDS','Artificial Intelligence and Data Science'),
('AGRI','Agricultural Engineering'),
('BME','Bio Medical Engineering'),
('CIVIL','Civil Engineering'),
('CHEM','Chemical Engineering'),
('IOT','Internet of Things Engineering'),
('CYBER','Cyber Security'),
('ECE','Electronics and Communication Engineering'),
('EEE','Electrical and Electronics Engineering'),
('MECH','Mechanical Engineering');

/* =========================
   REGULATIONS MASTER
========================= */
CREATE TABLE regulations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  regulation_code VARCHAR(10) UNIQUE NOT NULL
);

INSERT INTO regulations (regulation_code) VALUES
('R2019'),
('R2024');

/* =========================
   CREDIT REQUIREMENTS
   AIML – R2024 – REGULAR
========================= */
INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AIML','R2024','REGULAR','HS',14),
('AIML','R2024','REGULAR','BS',27),
('AIML','R2024','REGULAR','ES',28),
('AIML','R2024','REGULAR','PC',51),
('AIML','R2024','REGULAR','PE',16),
('AIML','R2024','REGULAR','OE',12),
('AIML','R2024','REGULAR','EEC',16),
('AIML','R2024','REGULAR','MC',4);

/* =========================
   AIML – R2019 – REGULAR
========================= */
INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AIML','R2019','REGULAR','HS',14),
('AIML','R2019','REGULAR','BS',25),
('AIML','R2019','REGULAR','ES',28),
('AIML','R2019','REGULAR','PC',56),
('AIML','R2019','REGULAR','PE',16),
('AIML','R2019','REGULAR','OE',12),
('AIML','R2019','REGULAR','EEC',16),
('AIML','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AIML','R2019','LATERAL','HS',3),
('AIML','R2019','LATERAL','BS',8),
('AIML','R2019','LATERAL','ES',12),
('AIML','R2019','LATERAL','PC',56),
('AIML','R2019','LATERAL','PE',15),
('AIML','R2019','LATERAL','OE',12),
('AIML','R2019','LATERAL','EEC',16),
('AIML','R2019','LATERAL','MC',3);

/* =========================
   AIML – R2024 – LATERAL
========================= */
INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AIML','R2024','LATERAL','HS',3),
('AIML','R2024','LATERAL','BS',20),
('AIML','R2024','LATERAL','ES',12),
('AIML','R2024','LATERAL','PC',43),
('AIML','R2024','LATERAL','PE',16),
('AIML','R2024','LATERAL','OE',12),
('AIML','R2024','LATERAL','EEC',16),
('AIML','R2024','LATERAL','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('CSE','R2019','REGULAR','HS',13),
('CSE','R2019','REGULAR','BS',24),
('CSE','R2019','REGULAR','ES',26),
('CSE','R2019','REGULAR','PC',57),
('CSE','R2019','REGULAR','PE',15),
('CSE','R2019','REGULAR','OE',12),
('CSE','R2019','REGULAR','EEC',16),
('CSE','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('CSE','R2019','LATERAL','HS',3),
('CSE','R2019','LATERAL','BS',8),
('CSE','R2019','LATERAL','ES',12),
('CSE','R2019','LATERAL','PC',57),
('CSE','R2019','LATERAL','PE',15),
('CSE','R2019','LATERAL','OE',12),
('CSE','R2019','LATERAL','EEC',16),
('CSE','R2019','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('CSE','R2024','REGULAR','HS',13),
('CSE','R2024','REGULAR','BS',25),
('CSE','R2024','REGULAR','ES',25),
('CSE','R2024','REGULAR','PC',56),
('CSE','R2024','REGULAR','PE',16),
('CSE','R2024','REGULAR','OE',12),
('CSE','R2024','REGULAR','EEC',16),
('CSE','R2024','REGULAR','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('CSE','R2024','LATERAL','HS',3),
('CSE','R2024','LATERAL','BS',10),
('CSE','R2024','LATERAL','ES',12),
('CSE','R2024','LATERAL','PC',56),
('CSE','R2024','LATERAL','PE',16),
('CSE','R2024','LATERAL','OE',12),
('CSE','R2024','LATERAL','EEC',16),
('CSE','R2024','LATERAL','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AIDS','R2024','REGULAR','HS',14),
('AIDS','R2024','REGULAR','BS',23),
('AIDS','R2024','REGULAR','ES',28),
('AIDS','R2024','REGULAR','PC',56),
('AIDS','R2024','REGULAR','PE',16),
('AIDS','R2024','REGULAR','OE',12),
('AIDS','R2024','REGULAR','EEC',16),
('AIDS','R2024','REGULAR','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AIDS','R2024','LATERAL','HS',3),
('AIDS','R2024','LATERAL','BS',16),
('AIDS','R2024','LATERAL','ES',15),
('AIDS','R2024','LATERAL','PC',48),
('AIDS','R2024','LATERAL','PE',15),
('AIDS','R2024','LATERAL','OE',12),
('AIDS','R2024','LATERAL','EEC',16),
('AIDS','R2024','LATERAL','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AIDS','R2019','REGULAR','HS',14),
('AIDS','R2019','REGULAR','BS',21),
('AIDS','R2019','REGULAR','ES',28),
('AIDS','R2019','REGULAR','PC',56),
('AIDS','R2019','REGULAR','PE',16),
('AIDS','R2019','REGULAR','OE',12),
('AIDS','R2019','REGULAR','EEC',16),
('AIDS','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AIDS','R2019','LATERAL','HS',3),
('AIDS','R2019','LATERAL','BS',8),
('AIDS','R2019','LATERAL','ES',12),
('AIDS','R2019','LATERAL','PC',56),
('AIDS','R2019','LATERAL','PE',15),
('AIDS','R2019','LATERAL','OE',12),
('AIDS','R2019','LATERAL','EEC',16),
('AIDS','R2019','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('CYBER','R2019','LATERAL','HS',3),
('CYBER','R2019','LATERAL','BS',18),
('CYBER','R2019','LATERAL','ES',12),
('CYBER','R2019','LATERAL','PC',47),
('CYBER','R2019','LATERAL','PE',17),
('CYBER','R2019','LATERAL','OE',12),
('CYBER','R2019','LATERAL','EEC',16),
('CYBER','R2019','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES 
('CYBER','R2019','REGULAR','HS',14),
('CYBER','R2019','REGULAR','BS',25),
('CYBER','R2019','REGULAR','ES',28),
('CYBER','R2019','REGULAR','PC',54),
('CYBER','R2019','REGULAR','PE',17),
('CYBER','R2019','REGULAR','OE',12),
('CYBER','R2019','REGULAR','EEC',16),
('CYBER','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('CYBER','R2024','REGULAR','HS',14),
('CYBER','R2024','REGULAR','BS',25),
('CYBER','R2024','REGULAR','ES',28),
('CYBER','R2024','REGULAR','PC',56),
('CYBER','R2024','REGULAR','PE',16),
('CYBER','R2024','REGULAR','OE',12),
('CYBER','R2024','REGULAR','EEC',16),
('CYBER','R2024','REGULAR','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('CYBER','R2024','LATERAL','HS',3),
('CYBER','R2024','LATERAL','BS',18),
('CYBER','R2024','LATERAL','ES',12),
('CYBER','R2024','LATERAL','PC',47),
('CYBER','R2024','LATERAL','PE',16),
('CYBER','R2024','LATERAL','OE',12),
('CYBER','R2024','LATERAL','EEC',16),
('CYBER','R2024','LATERAL','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('IT','R2019','REGULAR','HS',11),
('IT','R2019','REGULAR','BS',23),
('IT','R2019','REGULAR','ES',25),
('IT','R2019','REGULAR','PC',58),
('IT','R2019','REGULAR','PE',16),
('IT','R2019','REGULAR','OE',12),
('IT','R2019','REGULAR','EEC',16),
('IT','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('IT','R2019','LATERAL','HS',3),
('IT','R2019','LATERAL','BS',8),
('IT','R2019','LATERAL','ES',12),
('IT','R2019','LATERAL','PC',58),
('IT','R2019','LATERAL','PE',16),
('IT','R2019','LATERAL','OE',12),
('IT','R2019','LATERAL','EEC',16),
('IT','R2019','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('IT','R2024','REGULAR','HS',13),
('IT','R2024','REGULAR','BS',25),
('IT','R2024','REGULAR','ES',25),
('IT','R2024','REGULAR','PC',56),
('IT','R2024','REGULAR','PE',16),
('IT','R2024','REGULAR','OE',12),
('IT','R2024','REGULAR','EEC',16),
('IT','R2024','REGULAR','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('IT','R2024','LATERAL','HS',3),
('IT','R2024','LATERAL','BS',10),
('IT','R2024','LATERAL','ES',10),
('IT','R2024','LATERAL','PC',56),
('IT','R2024','LATERAL','PE',16),
('IT','R2024','LATERAL','OE',12),
('IT','R2024','LATERAL','EEC',16),
('IT','R2024','LATERAL','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('IOT','R2024','REGULAR','HS',14),
('IOT','R2024','REGULAR','BS',25),
('IOT','R2024','REGULAR','ES',28),
('IOT','R2024','REGULAR','PC',56),
('IOT','R2024','REGULAR','PE',16),
('IOT','R2024','REGULAR','OE',12),
('IOT','R2024','REGULAR','EEC',16),
('IOT','R2024','REGULAR','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('IOT','R2024','LATERAL','HS',3),
('IOT','R2024','LATERAL','BS',18),
('IOT','R2024','LATERAL','ES',12),
('IOT','R2024','LATERAL','PC',47),
('IOT','R2024','LATERAL','PE',16),
('IOT','R2024','LATERAL','OE',12),
('IOT','R2024','LATERAL','EEC',16),
('IOT','R2024','LATERAL','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('IOT','R2019','REGULAR','HS',14),
('IOT','R2019','REGULAR','BS',25),
('IOT','R2019','REGULAR','ES',28),
('IOT','R2019','REGULAR','PC',56),
('IOT','R2019','REGULAR','PE',16),
('IOT','R2019','REGULAR','OE',12),
('IOT','R2019','REGULAR','EEC',16),
('IOT','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('IOT','R2019','LATERAL','HS',3),
('IOT','R2019','LATERAL','BS',8),
('IOT','R2019','LATERAL','ES',12),
('IOT','R2019','LATERAL','PC',47),
('IOT','R2019','LATERAL','PE',16),
('IOT','R2019','LATERAL','OE',12),
('IOT','R2019','LATERAL','EEC',16),
('IOT','R2019','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('MECH','R2024','LATERAL','HS',4),
('MECH','R2024','LATERAL','BS',6),
('MECH','R2024','LATERAL','ES',17),
('MECH','R2024','LATERAL','PC',55),
('MECH','R2024','LATERAL','PE',18),
('MECH','R2024','LATERAL','OE',12),
('MECH','R2024','LATERAL','EEC',16),
('MECH','R2024','LATERAL','MC',4);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('MECH','R2024','REGULAR','HS',10),
('MECH','R2024','REGULAR','BS',25),
('MECH','R2024','REGULAR','ES',29),
('MECH','R2024','REGULAR','PC',55),
('MECH','R2024','REGULAR','PE',18),
('MECH','R2024','REGULAR','OE',12),
('MECH','R2024','REGULAR','EEC',16),
('MECH','R2024','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('MECH','R2019','LATERAL','HS',3),
('MECH','R2019','LATERAL','BS',4),
('MECH','R2019','LATERAL','ES',12),
('MECH','R2019','LATERAL','PC',54),
('MECH','R2019','LATERAL','PE',21),
('MECH','R2019','LATERAL','OE',12),
('MECH','R2019','LATERAL','EEC',16),
('MECH','R2019','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('MECH','R2019','REGULAR','HS',13),
('MECH','R2019','REGULAR','BS',24),
('MECH','R2019','REGULAR','ES',26),
('MECH','R2019','REGULAR','PC',54),
('MECH','R2019','REGULAR','PE',21),
('MECH','R2019','REGULAR','OE',12),
('MECH','R2019','REGULAR','EEC',16),
('MECH','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('ECE','R2024','LATERAL','HS',4),
('ECE','R2024','LATERAL','BS',4),
('ECE','R2024','LATERAL','ES',16),
('ECE','R2024','LATERAL','PC',61),
('ECE','R2024','LATERAL','PE',15),
('ECE','R2024','LATERAL','OE',12),
('ECE','R2024','LATERAL','EEC',16),
('ECE','R2024','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('ECE','R2024','REGULAR','HS',13),
('ECE','R2024','REGULAR','BS',21),
('ECE','R2024','REGULAR','ES',23),
('ECE','R2024','REGULAR','PC',61),
('ECE','R2024','REGULAR','PE',15),
('ECE','R2024','REGULAR','OE',12),
('ECE','R2024','REGULAR','EEC',16),
('ECE','R2024','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('ECE','R2019','LATERAL','HS',3),
('ECE','R2019','LATERAL','BS',8),
('ECE','R2019','LATERAL','ES',14),
('ECE','R2019','LATERAL','PC',58),
('ECE','R2019','LATERAL','PE',15),
('ECE','R2019','LATERAL','OE',12),
('ECE','R2019','LATERAL','EEC',16),
('ECE','R2019','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('ECE','R2019','REGULAR','HS',13),
('ECE','R2019','REGULAR','BS',24),
('ECE','R2019','REGULAR','ES',28),
('ECE','R2019','REGULAR','PC',58),
('ECE','R2019','REGULAR','PE',15),
('ECE','R2019','REGULAR','OE',12),
('ECE','R2019','REGULAR','EEC',16),
('ECE','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('BME','R2024','LATERAL','HS',0),
('BME','R2024','LATERAL','BS',12),
('BME','R2024','LATERAL','ES',18),
('BME','R2024','LATERAL','PC',57),
('BME','R2024','LATERAL','PE',18),
('BME','R2024','LATERAL','OE',7),
('BME','R2024','LATERAL','EEC',18),
('BME','R2024','LATERAL','MC',2);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('BME','R2024','REGULAR','HS',10),
('BME','R2024','REGULAR','BS',23),
('BME','R2024','REGULAR','ES',30),
('BME','R2024','REGULAR','PC',60),
('BME','R2024','REGULAR','PE',18),
('BME','R2024','REGULAR','OE',7),
('BME','R2024','REGULAR','EEC',18),
('BME','R2024','REGULAR','MC',2);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('BME','R2019','LATERAL','HS',3),
('BME','R2019','LATERAL','BS',11),
('BME','R2019','LATERAL','ES',11),
('BME','R2019','LATERAL','PC',57),
('BME','R2019','LATERAL','PE',12),
('BME','R2019','LATERAL','OE',12),
('BME','R2019','LATERAL','EEC',16),
('BME','R2019','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('BME','R2019','REGULAR','HS',13),
('BME','R2019','REGULAR','BS',27),
('BME','R2019','REGULAR','ES',29),
('BME','R2019','REGULAR','PC',57),
('BME','R2019','REGULAR','PE',12),
('BME','R2019','REGULAR','OE',12),
('BME','R2019','REGULAR','EEC',16),
('BME','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('CHEM','R2024','LATERAL','HS',5),
('CHEM','R2024','LATERAL','BS',6),
('CHEM','R2024','LATERAL','ES',7),
('CHEM','R2024','LATERAL','PC',58),
('CHEM','R2024','LATERAL','PE',18),
('CHEM','R2024','LATERAL','OE',12),
('CHEM','R2024','LATERAL','EEC',16),
('CHEM','R2024','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('CHEM','R2024','REGULAR','HS',13),
('CHEM','R2024','REGULAR','BS',26),
('CHEM','R2024','REGULAR','ES',17),
('CHEM','R2024','REGULAR','PC',58),
('CHEM','R2024','REGULAR','PE',18),
('CHEM','R2024','REGULAR','OE',12),
('CHEM','R2024','REGULAR','EEC',16),
('CHEM','R2024','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('CHEM','R2019','LATERAL','HS',4),
('CHEM','R2019','LATERAL','BS',4),
('CHEM','R2019','LATERAL','ES',14),
('CHEM','R2019','LATERAL','PC',55),
('CHEM','R2019','LATERAL','PE',15),
('CHEM','R2019','LATERAL','OE',12),
('CHEM','R2019','LATERAL','EEC',16),
('CHEM','R2019','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('CHEM','R2019','REGULAR','HS',13),
('CHEM','R2019','REGULAR','BS',28),
('CHEM','R2019','REGULAR','ES',24),
('CHEM','R2019','REGULAR','PC',55),
('CHEM','R2019','REGULAR','PE',15),
('CHEM','R2019','REGULAR','OE',12),
('CHEM','R2019','REGULAR','EEC',16),
('CHEM','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('EEE','R2024','LATERAL','HS',3),
('EEE','R2024','LATERAL','BS',6),
('EEE','R2024','LATERAL','ES',12),
('EEE','R2024','LATERAL','PC',57),
('EEE','R2024','LATERAL','PE',15),
('EEE','R2024','LATERAL','OE',12),
('EEE','R2024','LATERAL','EEC',16),
('EEE','R2024','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('EEE','R2024','REGULAR','HS',11),
('EEE','R2024','REGULAR','BS',25),
('EEE','R2024','REGULAR','ES',26),
('EEE','R2024','REGULAR','PC',57),
('EEE','R2024','REGULAR','PE',15),
('EEE','R2024','REGULAR','OE',12),
('EEE','R2024','REGULAR','EEC',16),
('EEE','R2024','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('EEE','R2019','LATERAL','HS',3),
('EEE','R2019','LATERAL','BS',6),
('EEE','R2019','LATERAL','ES',12),
('EEE','R2019','LATERAL','PC',57),
('EEE','R2019','LATERAL','PE',15),
('EEE','R2019','LATERAL','OE',12),
('EEE','R2019','LATERAL','EEC',16),
('EEE','R2019','LATERAL','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('EEE','R2019','REGULAR','HS',13),
('EEE','R2019','REGULAR','BS',25),
('EEE','R2019','REGULAR','ES',26),
('EEE','R2019','REGULAR','PC',57),
('EEE','R2019','REGULAR','PE',15),
('EEE','R2019','REGULAR','OE',12),
('EEE','R2019','REGULAR','EEC',16),
('EEE','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AGRI','R2024','REGULAR','HS',12),
('AGRI','R2024','REGULAR','BS',23),
('AGRI','R2024','REGULAR','ES',45),
('AGRI','R2024','REGULAR','PC',54),
('AGRI','R2024','REGULAR','PE',9),
('AGRI','R2024','REGULAR','OE',6),
('AGRI','R2024','REGULAR','EEC',33),
('AGRI','R2024','REGULAR','MC',2);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AGRI','R2024','LATERAL','HS',2),
('AGRI','R2024','LATERAL','BS',4),
('AGRI','R2024','LATERAL','ES',27),
('AGRI','R2024','LATERAL','PC',54),
('AGRI','R2024','LATERAL','PE',9),
('AGRI','R2024','LATERAL','OE',6),
('AGRI','R2024','LATERAL','EEC',33),
('AGRI','R2024','LATERAL','MC',2);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AGRI','R2019','REGULAR','HS',9),
('AGRI','R2019','REGULAR','BS',29),
('AGRI','R2019','REGULAR','ES',40),
('AGRI','R2019','REGULAR','PC',65),
('AGRI','R2019','REGULAR','PE',9),
('AGRI','R2019','REGULAR','OE',6),
('AGRI','R2019','REGULAR','EEC',49),
('AGRI','R2019','REGULAR','MC',3);

INSERT INTO credit_requirements
(department, regulation, entry_type, category, required_credits) VALUES
('AGRI','R2019','LATERAL','HS',2),
('AGRI','R2019','LATERAL','BS',5),
('AGRI','R2019','LATERAL','ES',19),
('AGRI','R2019','LATERAL','PC',65),
('AGRI','R2019','LATERAL','PE',9),
('AGRI','R2019','LATERAL','OE',6),
('AGRI','R2019','LATERAL','EEC',49),
('AGRI','R2019','LATERAL','MC',3);



