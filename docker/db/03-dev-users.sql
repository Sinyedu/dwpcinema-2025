-- LOCAL DEVELOPMENT ONLY. Never load this into a real database.
--   admin@example.com / admin12345   (admin portal: /admin_login.php)
--   user@example.com  / user12345    (regular user)
USE dwpcinemaDB;
INSERT INTO `User` (firstName, lastName, userEmail, isAdmin, role, passwordHash, isActive) VALUES
  ('Dev', 'Admin', 'admin@example.com', 1, 'admin', '$2y$12$MX2ozx9UZBmxpWr5v7fdMej.55DoFHeWuwlCIJZBx4SBNnli15/N.', 1),
  ('Dev', 'User',  'user@example.com',  0, 'user',  '$2y$12$cJF24TsFceUTGdUSzyZ0yu1.R25z3h2625oB6gyeovrrOanWJ5Ddu', 1);
