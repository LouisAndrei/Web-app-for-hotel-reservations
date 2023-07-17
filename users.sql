SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


-- Structura tabelului pentru tabelul `users`


CREATE TABLE `users` (
  `username` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



INSERT INTO `users` (`username`, `email`, `password`) VALUES
('Louis', 'ripeanu.louis.j9w@student.ucv.ro', 'c53e6ed443ea6fcd4fe0923945359e38'),
('LouisAndrei', 'louis@andrei', '202cb962ac59075b964b07152d234b70');
COMMIT;

