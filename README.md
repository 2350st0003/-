# BBS Project

## 概要
このリポジトリは、Docker Compose を使って動作する簡易 Web 掲示板サービスです。  
- 投稿者が自由にテキストを投稿可能  
- 投稿には画像も添付可能（5MB以下、自動縮小）  
- 投稿は MySQL データベースに保存  
- XSS / SQLインジェクション対策済み  
- 投稿には自動で連番と投稿日時が付与される  
- レスアンカー機能あり  
- スマートフォンでも閲覧可能なレスポンシブデザイン  

---

## ディレクトリ構成
.

├── Dockerfile
├── docker-compose.yml
├── nginx
│ └── conf.d/default.conf
├── public
│ └── bbsimagetest.php
├── upload
│ └── image/
└── README.md

---

## 起動方法
###  Docker Compose でコンテナを起動
```bash
docker compose up -d
ブラウザでアクセス

データベース設定

データベース名: example_db

テーブル名: bbs_entries

テーブル作成例

CREATE TABLE `bbs_entries` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `body` TEXT NOT NULL,
  `image_filename` TEXT DEFAULT NULL,
  `reply_to` INT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

使用技術

PHP

MySQL 

Nginx

Docker / Docker Compose

HTML + CSS + JavaScript 
