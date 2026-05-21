USE paw_db;
INSERT INTO user (username, password, roles, created_at) VALUES ('testuser', '$2y$13$YzXjg0ET1Z404ERtPkqRBOBjL6gK11Dvq2ySqpbq9UW1mSdvXzYXy', '["ROLE_USER"]', NOW());
