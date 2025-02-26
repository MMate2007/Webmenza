CREATE TABLE `groups` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`)
);
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `groupId` INT UNSIGNED NULL DEFAULT NULL,
    `registered` BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`name`, `groupId`),
    FOREIGN KEY (`groupId`) REFERENCES `groups`(`id`) ON DELETE
    SET NULL ON UPDATE CASCADE
);
CREATE TABLE `menu` (
    `date` DATE NOT NULL,
    `id` INT UNSIGNED NOT NULL,
    `description` TEXT NOT NULL,
    PRIMARY KEY (`date`, `id`)
);
CREATE TABLE `choices` (
    `userId` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `menuId` INT UNSIGNED NULL,
    PRIMARY KEY (`userId`, `date`),
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`date`, `menuId`) REFERENCES `menu`(`date`, `id`) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE `passkeys` (
    `id` VARCHAR(255) NOT NULL,
    `userId` INT UNSIGNED NOT NULL,
    `publicKey` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE `deadlines` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `from` DATE NOT NULL,
    `to` DATE NOT NULL,
    `start` DATE NOT NULL,
    `end` DATE NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY (`from`, `to`),
    CHECK (
        `from` <= `to`
        AND `start` <= `end`
    )
);
CREATE TABLE `modifications` (
    `userId` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `value` INT UNSIGNED NULL,
    `approved` BOOLEAN NULL DEFAULT NULL,
    `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    UNIQUE KEY (`userId`, `date`),
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`userId`, `date`) REFERENCES `choices`(`userId`, `date`) ON DELETE CASCADE ON UPDATE CASCADE
);
CREATE TABLE `notificationsubscriptions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `userId` INT UNSIGNED NOT NULL,
    `data` JSON NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`userId`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
);
INSERT INTO `groups` (`id`, `name`)
VALUES (1, '_admin'),
    (2, '_manager');
INSERT INTO `users` (`id`, `name`, `password`, `groupId`)
VALUES (
        1,
        'admin',
        '$2y$12$08MLP8SCR6d5.xOqI5xam.or4/Ljovwp7GbHPbO7SDB.rwUZkEeOq',
        1
    );