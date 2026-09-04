-- Run this only when upgrading an existing installation created before the extended article fields.
ALTER TABLE articles ADD COLUMN subcategory VARCHAR(100) NULL AFTER category;
ALTER TABLE articles ADD COLUMN focus_keyword VARCHAR(190) NULL;
ALTER TABLE articles ADD COLUMN meta_title VARCHAR(255) NULL;
ALTER TABLE articles ADD COLUMN meta_description TEXT NULL;
ALTER TABLE articles ADD COLUMN canonical_url VARCHAR(500) NULL;
ALTER TABLE articles ADD COLUMN schema_type VARCHAR(80) NOT NULL DEFAULT 'Article';
ALTER TABLE articles ADD COLUMN faq_json JSON NULL;
ALTER TABLE creators ADD COLUMN social_links_json JSON NULL;
ALTER TABLE creators ADD COLUMN video_ids_json JSON NULL;
CREATE TABLE IF NOT EXISTS article_categories(article_id INT NOT NULL,category_id INT NOT NULL,PRIMARY KEY(article_id,category_id),CONSTRAINT fk_ac_article FOREIGN KEY(article_id) REFERENCES articles(id) ON DELETE CASCADE,CONSTRAINT fk_ac_category FOREIGN KEY(category_id) REFERENCES categories(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS redirects(id INT AUTO_INCREMENT PRIMARY KEY,source_path VARCHAR(500) UNIQUE NOT NULL,target_url VARCHAR(500) NOT NULL,status_code SMALLINT UNSIGNED NOT NULL DEFAULT 301,active TINYINT NOT NULL DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(active)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
