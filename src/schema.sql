--Användare
CREATE TABLE users(
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--Grupper
CREATE TABLE `groups` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);


--Medlemskap
CREATE TABLE memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    role ENUM('member', 'admin') NOT NULL DEFAULT 'member',
    status ENUM('pending', 'approved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (group_id) REFERENCES `groups`(id),
    UNIQUE (user_id, group_id)
);

-- Ämnen (diskussioner) inom en grupp
CREATE TABLE topics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES `groups`(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Inlägg under ett ämne
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    topic_id INT NOT NULL,
    user_id INT NOT NULL,
    body TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (topic_id) REFERENCES topics(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Inbjudningslänkar (för att bjuda in användare till grupper)
CREATE TABLE invites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    created_by INT NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    FOREIGN KEY (group_id) REFERENCES `groups`(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);