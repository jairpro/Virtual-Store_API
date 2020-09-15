SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

INSERT INTO `admins` (`email`, `hash`, `name`, `user`, `type`, `status`) VALUES
('devl@example.com', '$2y$12$UddsCtloH9PcNjdbc93/2uBjgSVVm5fRJoiIMecc6US018SpuTSvi', 'Developer', 'dev', 'D', 'A');
COMMIT;
