ALTER TABLE `admins` 

CHANGE `type` `type` 
CHAR(1) 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci 
NULL 
DEFAULT 'O', 

CHANGE `status` `status` 
CHAR(1) 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci 
NULL 
DEFAULT 'A';