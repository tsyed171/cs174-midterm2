CREATE TABLE cs174_mid2_credentials (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255),
  username VARCHAR(255),
  password VARCHAR(255)
);

CREATE TABLE cs174_mid2_content (
  id INT,
  thread VARCHAR(512),
  content VARCHAR(10000)
);