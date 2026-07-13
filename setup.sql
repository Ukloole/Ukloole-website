-- ============================================================
--  UKLOOLE — MySQL Database Setup
--  Run this in phpMyAdmin or via MySQL CLI:
--  mysql -u USERNAME -p DATABASE_NAME < setup.sql
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Users (admin accounts)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin: admin / admin123
INSERT IGNORE INTO `users` (`username`,`password`,`role`) VALUES
('admin', SHA2(CONCAT('admin123','ukloole_salt_2025'), 256), 'admin');

-- Quotes
CREATE TABLE IF NOT EXISTS `quotes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `service` varchar(255) DEFAULT NULL,
  `message` text,
  `status` enum('new','in_progress','closed') DEFAULT 'new',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subscribers
CREATE TABLE IF NOT EXISTS `subscribers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `status` enum('active','unsubscribed') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Testimonials
CREATE TABLE IF NOT EXISTS `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `testimonial` text NOT NULL,
  `rating` tinyint(1) DEFAULT 5,
  `image` varchar(500) DEFAULT NULL,
  `published` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample testimonials
INSERT IGNORE INTO `testimonials` (`client_name`,`company_name`,`testimonial`,`rating`,`published`) VALUES
('Adaeze Okafor','ShopNaija','Ukloole transformed our customer service. Response times dropped from hours to minutes and our satisfaction scores went through the roof!',5,1),
('Emeka Nwosu','TechBridge Lagos','Best decision we made for our startup. Professional agents, great communication, zero stress.',5,1),
('Fatima Al-Hassan','Fatima Skincare','My customers now get instant replies even at midnight. Sales have increased by 40% since we started.',5,1);

-- Blog Posts
CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `slug` varchar(500) NOT NULL,
  `excerpt` text,
  `content` longtext,
  `category` varchar(255) DEFAULT NULL,
  `tags` varchar(500) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL,
  `author` varchar(255) DEFAULT 'Ukloole',
  `published` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Support Tickets
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Free Tools Directory
CREATE TABLE IF NOT EXISTS `tools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `url` varchar(500) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample tools
INSERT IGNORE INTO `tools` (`name`,`description`,`url`,`category`,`featured`) VALUES
('Tidio','Free live chat and chatbot for your website','https://www.tidio.com','Live Chat',1),
('Freshdesk','Free helpdesk ticketing system','https://freshdesk.com','Helpdesk',1),
('HubSpot CRM','Free CRM to manage customers and deals','https://www.hubspot.com/products/crm','CRM',1),
('WhatsApp Business','Free business messaging app','https://business.whatsapp.com','Messaging',0),
('Notion','All-in-one workspace for notes and docs','https://www.notion.so','Productivity',0),
('Canva','Free graphic design tool','https://www.canva.com','Design',0);

-- Jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT 'Remote',
  `type` enum('full_time','part_time','contract','remote') DEFAULT 'remote',
  `description` text,
  `requirements` text,
  `salary_range` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Applications
CREATE TABLE IF NOT EXISTS `applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `job_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `cover_letter` text,
  `resume_url` varchar(500) DEFAULT NULL,
  `status` enum('new','reviewing','shortlisted','rejected') DEFAULT 'new',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `job_id` (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SELECT 'Database setup complete! Default login: admin / admin123' AS result;
