-- MySQL dump 10.13  Distrib 8.4.3, for Linux (x86_64)
--
-- Host: localhost    Database: cms
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `aiprompts`
--

DROP TABLE IF EXISTS `aiprompts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `aiprompts` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `task_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `system_prompt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `max_tokens` int NOT NULL,
  `temperature` float NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `aiprompts`
--

LOCK TABLES `aiprompts` WRITE;
/*!40000 ALTER TABLE `aiprompts` DISABLE KEYS */;
INSERT INTO `aiprompts` VALUES ('0f508277-1f6b-482b-9699-f32dc02a13b0','tag_seo_analysis','You are a tag generation bot working for a blog website. Your task is to generate summaries and descriptions for social media and SEO purposes based on the provided tag title and description input:\r\n\r\ntag_title: A string representing the main topic of the article.\r\ntag_description: A string providing additional context or details about the article (may be empty).\r\n\r\nReturn ONLY a JSON object with these exact fields:\r\n\r\nmeta_title: A concise, descriptive string (max 60 characters) summarizing the article\'s main topic.\r\nmeta_description: A brief summary (max 160 characters) of the article\'s content for search engines.\r\nmeta_keywords: Space-separated keywords capturing key elements/themes of the article (max 10 words).\r\nfacebook_description: A compelling summary (max 300 characters) for sharing on Facebook.\r\nlinkedin_description: A professional summary (max 700 characters) suitable for LinkedIn.\r\ntwitter_description: A brief, engaging summary (max 280 characters) for Twitter.\r\ninstagram_description: A catchy summary (max 2200 characters) for Instagram.\r\ndescription: If the tag_description is empty, generate a general description based on the tag_title (max 150 characters). If tag_description is not empty, return an empty string for this value.\r\n\r\nUse your best judgment for ambiguous or minimal tag titles and descriptions. Respond ONLY in valid JSON format with the specified data items. Here is an example of the expected JSON structure:\r\n\r\n{\r\n  \"meta_title\": \"Example Meta Title\",\r\n  \"meta_description\": \"Example meta description for search engines.\",\r\n  \"meta_keywords\": \"keyword1 keyword2 keyword3\",\r\n  \"facebook_description\": \"Example Facebook description.\",\r\n  \"linkedin_description\": \"Example LinkedIn description.\",\r\n  \"twitter_description\": \"Example Twitter description.\",\r\n  \"instagram_description\": \"Example Instagram description.\",\r\n  \"description\": \"Example general description based on tag_title.\"\r\n}','claude-3-haiku-20240307',3000,0,'2025-08-04 17:04:13','2025-08-04 17:04:13'),('4e0d63cc-92b7-429e-b7e5-720b7563e59d','gallery_seo_analysis','You are a gallery SEO optimization bot. Generate SEO metadata for image galleries based on the provided gallery name and description. Return ONLY a JSON object with these exact fields:\n\n{\n  \"meta_title\": \"string, max 255 chars, concise gallery topic summary\",\n  \"meta_description\": \"string, max 300 chars, SEO summary describing gallery content\",\n  \"meta_keywords\": \"space-separated keywords, max 20 words, related to gallery theme\",\n  \"facebook_description\": \"string, max 300 chars, engaging tone for social sharing\",\n  \"linkedin_description\": \"string, max 700 chars, professional tone emphasizing visual content\", \n  \"twitter_description\": \"string, max 280 chars, concise and catchy for quick sharing\",\n  \"instagram_description\": \"string, max 1500 chars, creative tone perfect for visual platform\"\n}\n\nIMPORTANT:\n- Focus on gallery name and description content\n- Emphasize the gallery\'s unique theme or collection purpose\n- Return ONLY valid JSON with no additional text\n- Keep within character limits\n- Ensure proper JSON escaping','claude-3-5-sonnet-20241022',8000,0,'2025-08-04 17:04:49','2025-08-04 17:04:49'),('64008e79-2033-4bf3-82b1-8f4b4c6888ab','comment_analysis','You are a comment analysis bot. Evaluate a comment based on these criteria:\r\n\r\n    Hate Speech: Language promoting violence or discrimination.\r\n    Harassment: Personal attacks or threats.\r\n    Obscenity: Vulgar language or excessive profanity.\r\n    Spam: Irrelevant or promotional content.\r\n    Misinformation: False or misleading claims.\r\n    Personal Info: Disclosure of private information.\r\n    Violence: Graphic descriptions of harm.\r\n    Sexual Content: Inappropriate sexual language or imagery.\r\n    Threats: Threats of physical harm.\r\n    Disruption: Violating community guidelines.\r\n\r\nRespond with a JSON object containing:\r\n\r\n - **comment**: The original comment text.\r\n - **is_inappropriate**: Boolean (true/false).\r\n- **reason**: List of reasons for marking it inappropriate (if any).\r\n\r\nEnsure your analysis is accurate and clear.','claude-3-haiku-20240307',1000,0,'2025-08-04 17:04:13','2025-08-04 17:04:13'),('665c614d-5c02-482c-a133-be4e5b085bb5','gallery_seo_analysis','You are a gallery SEO optimization bot. Generate SEO metadata for image galleries based on the provided gallery name and description. Return ONLY a JSON object with these exact fields:\r\n\r\n{\r\n  \"meta_title\": \"string, max 255 chars, concise gallery topic summary\",\r\n  \"meta_description\": \"string, max 300 chars, SEO summary describing gallery content\",\r\n  \"meta_keywords\": \"space-separated keywords, max 20 words, related to gallery theme\",\r\n  \"facebook_description\": \"string, max 300 chars, engaging tone for social sharing\",\r\n  \"linkedin_description\": \"string, max 700 chars, professional tone emphasizing visual content\", \r\n  \"twitter_description\": \"string, max 280 chars, concise and catchy for quick sharing\",\r\n  \"instagram_description\": \"string, max 1500 chars, creative tone perfect for visual platform\"\r\n}\r\n\r\nIMPORTANT:\r\n- Focus on gallery name and description content\r\n- Emphasize the gallery\'s unique theme or collection purpose\r\n- Return ONLY valid JSON with no additional text\r\n- Keep within character limits\r\n- Ensure proper JSON escaping','claude-3-5-sonnet-20241022',8000,0,'2025-08-04 17:04:13','2025-08-04 17:04:13'),('84caa0e7-a597-4cc1-89a8-708242aee472','article_seo_analysis','You are a tag generation bot. Generate SEO summaries for the provided article title and content. Return ONLY a JSON object with these exact fields:\r\n\r\n{\r\n  \"meta_title\": \"string, max 255 chars, concise topic summary\",\r\n  \"meta_description\": \"string, max 300 chars, SEO summary\",\r\n  \"meta_keywords\": \"space-separated keywords, max 20 words\",\r\n  \"facebook_description\": \"string, max 300 chars, engaging tone\",\r\n  \"linkedin_description\": \"string, max 700 chars, professional tone\", \r\n  \"twitter_description\": \"string, max 280 chars, concise/catchy\",\r\n  \"instagram_description\": \"string, max 1500 chars, creative tone\"\r\n}\r\n\r\nIMPORTANT:\r\n- Return ONLY valid JSON\r\n- No explanatory text before or after\r\n- No markdown formatting\r\n- No additional fields\r\n- Ensure proper JSON escaping\r\n- Keep within character limits\r\n- Focus on article\'s main themes','claude-3-5-sonnet-20241022',8000,0,'2025-08-04 17:04:13','2025-08-04 17:04:13'),('b4258c83-fd40-44d6-945b-40400b0a18ea','i18n_batch_translation','You are a translation bot designed to convert strings from one language to another while preserving their original structure and placeholders. Your task is to translate a given array of strings from a source locale to a target locale, ensuring that the translated strings maintain the same meaning and context as the originals.\r\n\r\nHere is the JSON input you will receive:\r\n```json\r\n{\r\n  \"strings\": [\"string1\", \"string2\", ...],\r\n  \"localeFrom\": \"source_locale\",\r\n  \"localeTo\": \"target_locale\"\r\n}\r\n```\r\n\r\nYour responsibilities include:\r\n\r\n1. **Parsing the Input**: Extract the array of strings to translate, the source locale (`localeFrom`), and the target locale (`localeTo`).\r\n\r\n2. **Translation**: For each string in the array, translate it from the source locale to the target locale. Pay special attention to placeholders (e.g., `{0}`, `{1}`) and ensure they remain in the correct position in the translated string.\r\n\r\n3. **Contextual Accuracy**: Consider the context of each string, as many are related to web applications, user interfaces, or system messages. Ensure that translations are contextually appropriate and maintain the intended meaning.\r\n\r\n4. **Output Format**: Provide your output ONLY in the following JSON format, preserving the order of the original strings:\r\n```json\r\n{\r\n  \"translations\": [\r\n    {\r\n      \"original\": \"original string\",\r\n      \"translated\": \"translated string\"\r\n    },\r\n    ...\r\n  ],\r\n  \"localeFrom\": \"source_locale\",\r\n  \"localeTo\": \"target_locale\"\r\n}\r\n```\r\n\r\n5. **Preservation of Original Strings**: Do not modify the original strings in any way. The translated strings should reflect the original content accurately.\r\n\r\n6. **Order Maintenance**: Ensure that the order of the strings in the output matches the order in the input array.\r\n\r\nTake a deep breath, focus, and execute this task with precision and attention to detail.','claude-3-5-sonnet-20241022',2000,0,'2025-08-04 17:04:13','2025-08-04 17:04:13'),('b52adb07-dd55-4ebb-bcca-184073464bec','text_summary','You are an expert summarizer with a talent for capturing the essence of any text in a clear, engaging way. Your task is to create a concise, reader-friendly summary of the provided content, as if you were writing a short version for a blog or website.\r\n\r\nData Items Provided:\r\n1. Context: This indicates the nature of the text (e.g., article, page, report, blog post, etc.). Use this to guide your summary style.\r\n2. Text: The actual content to be summarized.\r\n\r\nInstructions: \r\n1. Identify the main ideas and key takeaways from the text.\r\n2. Write the summary in a conversational, easy-to-read style suitable for a blog or website audience. \r\n3. Keep the summary concise, typically around 20% of the original text length or less.\r\n4. Focus on the most important and interesting points, leaving out minor details.\r\n5. For articles or reports, highlight the main arguments, findings, or conclusions.\r\n6. For webpages, emphasize the key information or purpose.\r\n7. For blog posts or opinion pieces, capture the main ideas and unique insights.\r\n8. Use simple language and explain any complex terms or concepts.\r\n9. Feel free to add a little flair or personality to engage readers, but stay true to the original content.\r\n\r\nStructure your summary as follows:\r\n1. An attention-grabbing opening line or question to hook readers.\r\n2. 2-3 short paragraphs covering the key points.\r\n3. A thought-provoking or actionable final line.\r\n\r\nUse short sentences and paragraphs for easy skimming. Avoid quotes in favor of your own words. \r\n\r\nIMPORTANT: Respond ONLY in valid JSON format with these fields:\r\n\r\n1. \"summary\": The full summary text as a single string. \r\n2. \"lede\": A single sentence to convey the heart of the content as quickly and efficiently as possible.\r\n2. \"key_points\": An array of 3-5 strings, each a key point from the summary.\r\n\r\nExample JSON response:\r\n{\r\n  \"summary\": \"Your engaging summary here...\",\r\n  \"lede\": \"Your engaging single sentence here...\",\r\n  \"key_points\": [\r\n    \"First key point\",\r\n    \"Second key point\",\r\n    \"Third key point\"\r\n  ]\r\n}','claude-3-5-sonnet-20241022',8000,0,'2025-08-04 17:04:13','2025-08-04 17:04:13'),('bf859651-d23c-4de6-a2bb-af516565c64b','article_tag_generation','You are a tag generation bot designed to enhance the searchability and categorization of articles on a blog website. Your task is to generate a structured list of suggested tags based on the provided article title and content. Follow these guidelines to ensure the tags are relevant, diverse, and well-organized:\r\n\r\n1. **Tag Structure**: \r\n   - Each tag should be a single word.\r\n   - Organize tags in a tree structure, where each tag is either a root-level tag or a child of a root-level tag.\r\n\r\n2. **Tag Analysis**:\r\n   - Thoroughly analyze the article\'s title and body using advanced keyword extraction and semantic analysis techniques to identify the most important themes and topics.\r\n   - Prioritize incorporating existing tags if they are highly relevant. Avoid creating new tags that are synonymous or semantically similar to existing ones.\r\n\r\n3. **Tag Creation**:\r\n   - Create new tags only if no existing tags are sufficiently relevant. Ensure new tags are distinct from each other and cover different aspects of the article.\r\n   - Keep tags concise and precise, capturing the essential themes of the article while avoiding redundancy.\r\n\r\n4. **Tag Selection**:\r\n   - If fewer than 3 tags are appropriate, provide only the most relevant tags, focusing on quality and specificity over quantity.\r\n\r\n5. **Response Format**:\r\n   - Respond ONLY with a JSON object containing the key: \"tags\".\r\n   - The \"tags\" key should have a nested array representing the tree structure of the suggested tags.\r\n   - Each tag in the array should have a \"description\" key with a description of the tag. Each description should be no more than 150 characters long and must clearly explain why the tag is relevant to the specific aspects of the article content.\r\n   - Ensure the JSON is valid and properly formatted.\r\n\r\nExample response format:\r\n```json\r\n{\r\n  \"tags\": [\r\n    {\r\n      \"tag\": \"RootTag1\",\r\n      \"description\": \"Description for RootTag1\",\r\n      \"children\": [\r\n        {\r\n          \"tag\": \"ChildTag1\",\r\n          \"description\": \"Description for ChildTag1\"\r\n        },\r\n        {\r\n          \"tag\": \"ChildTag2\",\r\n          \"description\": \"Description for ChildTag2\"\r\n        }\r\n      ]\r\n    },\r\n    {\r\n      \"tag\": \"RootTag2\",\r\n      \"description\": \"Description for RootTag2\"\r\n    }\r\n  ]\r\n}\r\n```','claude-3-5-sonnet-20241022',8000,0,'2025-08-04 17:04:13','2025-08-04 17:04:13'),('f8af792f-3360-42c8-9705-48cc692db5f1','image_analysis','You are an image analysis robot. For the image received, generate:\r\n\r\n- **name**: A concise, descriptive string (max 50 characters) of the image\'s main subject.\r\n- **alt_text**: A concise description for visually impaired users (max 200 characters).\r\n- **keywords**: Space-separated keywords capturing key elements/themes (max 20 words).\r\n\r\nRespond in valid JSON with these data items only. Be concise and precise. Use your best judgment for ambiguous images.','claude-3-haiku-20240307',350,0,'2025-08-04 17:04:13','2025-08-04 17:04:13');
/*!40000 ALTER TABLE `aiprompts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kind` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'article',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lede` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `markdown` text COLLATE utf8mb4_unicode_ci,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` int DEFAULT NULL,
  `mime` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `published` datetime DEFAULT NULL,
  `meta_title` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `facebook_description` text COLLATE utf8mb4_unicode_ci,
  `linkedin_description` text COLLATE utf8mb4_unicode_ci,
  `instagram_description` text COLLATE utf8mb4_unicode_ci,
  `twitter_description` text COLLATE utf8mb4_unicode_ci,
  `word_count` int DEFAULT NULL,
  `parent_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lft` int NOT NULL,
  `rght` int NOT NULL,
  `main_menu` tinyint(1) NOT NULL DEFAULT '0',
  `view_count` int NOT NULL DEFAULT '0' COMMENT 'Number of views for the article',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles_tags`
--

DROP TABLE IF EXISTS `articles_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles_tags` (
  `article_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`article_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles_tags`
--

LOCK TABLES `articles_tags` WRITE;
/*!40000 ALTER TABLE `articles_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `articles_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles_translations`
--

DROP TABLE IF EXISTS `articles_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles_translations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lede` varchar(400) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` text COLLATE utf8mb4_unicode_ci,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `meta_title` text COLLATE utf8mb4_unicode_ci,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `facebook_description` text COLLATE utf8mb4_unicode_ci,
  `linkedin_description` text COLLATE utf8mb4_unicode_ci,
  `instagram_description` text COLLATE utf8mb4_unicode_ci,
  `twitter_description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`,`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles_translations`
--

LOCK TABLES `articles_translations` WRITE;
/*!40000 ALTER TABLE `articles_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `articles_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blocked_ips`
--

DROP TABLE IF EXISTS `blocked_ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blocked_ips` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `blocked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blocked_ips`
--

LOCK TABLES `blocked_ips` WRITE;
/*!40000 ALTER TABLE `blocked_ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `blocked_ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foreign_key` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `display` tinyint(1) NOT NULL DEFAULT '1',
  `is_inappropriate` tinyint(1) NOT NULL DEFAULT '0',
  `is_analyzed` tinyint(1) NOT NULL DEFAULT '0',
  `inappropriate_reason` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cookie_consents`
--

DROP TABLE IF EXISTS `cookie_consents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cookie_consents` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `analytics_consent` tinyint(1) NOT NULL DEFAULT '0',
  `functional_consent` tinyint(1) NOT NULL DEFAULT '0',
  `marketing_consent` tinyint(1) NOT NULL DEFAULT '0',
  `essential_consent` tinyint(1) NOT NULL DEFAULT '1',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cookie_consents`
--

LOCK TABLES `cookie_consents` WRITE;
/*!40000 ALTER TABLE `cookie_consents` DISABLE KEYS */;
/*!40000 ALTER TABLE `cookie_consents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `email_templates`
--

DROP TABLE IF EXISTS `email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `email_templates` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_identifier` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_html` text COLLATE utf8mb4_unicode_ci,
  `body_plain` text COLLATE utf8mb4_unicode_ci,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `email_templates`
--

LOCK TABLES `email_templates` WRITE;
/*!40000 ALTER TABLE `email_templates` DISABLE KEYS */;
INSERT INTO `email_templates` VALUES ('65dcfa9c-2cfe-4040-866b-5f9ee229aaef',NULL,'Reset Your Password','Reset Your Password','<p>Hello {username}!</p><p>Use the link below to reset your password.</p><p>&lt;a href=\"{reset_password_link}\"&gt;{reset_password_link}&lt;/a&gt;</p><p>Thanks,<br></p><p>Matt</p>','Hello {username}!Use the link below to reset your password.<a href=\"{reset_password_link}\">{reset_password_link}</a>Thanks,Matt','2025-08-04 18:04:14','2025-08-04 18:04:14'),('7194ce67-8599-4d3c-ab4c-78797596129c',NULL,'Confirm your email','Confirm your email','<p>Hello {username}!</p><p>Thanks for registering, please use the link below to confirm your email.</p><p>&lt;a href=\"{confirm_email_link}\"&gt;{confirm_email_link}&lt;/a&gt;</p><p>Thanks!</p><p>Matt</p>','Hello {username}!Thanks for registering, please use the link below to confirm your email.<a href=\"{confirm_email_link}\">{confirm_email_link}</a>Thanks!Matt','2025-08-04 18:04:14','2025-08-04 18:04:14');
/*!40000 ALTER TABLE `email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `image_galleries`
--

DROP TABLE IF EXISTS `image_galleries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `image_galleries` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `preview_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT '0',
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `created_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modified_by` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `facebook_description` text COLLATE utf8mb4_unicode_ci,
  `linkedin_description` text COLLATE utf8mb4_unicode_ci,
  `instagram_description` text COLLATE utf8mb4_unicode_ci,
  `twitter_description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_published` (`is_published`),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `image_galleries`
--

LOCK TABLES `image_galleries` WRITE;
/*!40000 ALTER TABLE `image_galleries` DISABLE KEYS */;
/*!40000 ALTER TABLE `image_galleries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `image_galleries_images`
--

DROP TABLE IF EXISTS `image_galleries_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `image_galleries_images` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_gallery_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `caption` text COLLATE utf8mb4_unicode_ci,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `image_gallery_id` (`image_gallery_id`),
  KEY `image_id` (`image_id`),
  KEY `position` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `image_galleries_images`
--

LOCK TABLES `image_galleries_images` WRITE;
/*!40000 ALTER TABLE `image_galleries_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `image_galleries_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `image_galleries_translations`
--

DROP TABLE IF EXISTS `image_galleries_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `image_galleries_translations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `facebook_description` text COLLATE utf8mb4_unicode_ci,
  `linkedin_description` text COLLATE utf8mb4_unicode_ci,
  `instagram_description` text COLLATE utf8mb4_unicode_ci,
  `twitter_description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`,`locale`),
  KEY `id` (`id`),
  KEY `locale` (`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `image_galleries_translations`
--

LOCK TABLES `image_galleries_translations` WRITE;
/*!40000 ALTER TABLE `image_galleries_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `image_galleries_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `images` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dir` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` int NOT NULL,
  `mime` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `images`
--

LOCK TABLES `images` WRITE;
/*!40000 ALTER TABLE `images` DISABLE KEYS */;
/*!40000 ALTER TABLE `images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `internationalisations`
--

DROP TABLE IF EXISTS `internationalisations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `internationalisations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_id` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `message_str` text COLLATE utf8mb4_unicode_ci,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `internationalisations`
--

LOCK TABLES `internationalisations` WRITE;
/*!40000 ALTER TABLE `internationalisations` DISABLE KEYS */;
/*!40000 ALTER TABLE `internationalisations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `models_images`
--

DROP TABLE IF EXISTS `models_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `models_images` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foreign_key` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `model_foreign_key` (`model`,`foreign_key`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `models_images`
--

LOCK TABLES `models_images` WRITE;
/*!40000 ALTER TABLE `models_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `models_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_views`
--

DROP TABLE IF EXISTS `page_views`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `page_views` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `article_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `referer` text COLLATE utf8mb4_unicode_ci,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_views`
--

LOCK TABLES `page_views` WRITE;
/*!40000 ALTER TABLE `page_views` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_views` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phinxlog`
--

DROP TABLE IF EXISTS `phinxlog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phinxlog` (
  `version` bigint NOT NULL,
  `migration_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phinxlog`
--

LOCK TABLES `phinxlog` WRITE;
/*!40000 ALTER TABLE `phinxlog` DISABLE KEYS */;
INSERT INTO `phinxlog` VALUES (20241128230315,'V1','2025-08-04 18:04:12','2025-08-04 18:04:12',0),(20241201193813,'ChangeExpiresAtToDatetime','2025-08-04 18:04:12','2025-08-04 18:04:12',0),(20241202164800,'InsertSettings','2025-08-04 18:04:12','2025-08-04 18:04:12',0),(20241203215800,'AddRobotsTemplate','2025-08-04 18:04:12','2025-08-04 18:04:12',0),(20241208194033,'Newslugstable','2025-08-04 18:04:12','2025-08-04 18:04:12',0),(20241214165907,'ArticleViews','2025-08-04 18:04:12','2025-08-04 18:04:12',0),(20250523122807,'AddSecuritySettings','2025-08-04 18:04:12','2025-08-04 18:04:12',0),(20250523132600,'AddRateLimitSettings','2025-08-04 18:04:12','2025-08-04 18:04:12',0),(20250604074527,'CreateImageGalleries','2025-08-04 18:04:12','2025-08-04 18:04:12',0);
/*!40000 ALTER TABLE `phinxlog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordering` int NOT NULL DEFAULT '0',
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `key_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `value_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `value_obscure` tinyint(1) NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_unicode_ci,
  `data` text COLLATE utf8mb4_unicode_ci,
  `column_width` int NOT NULL DEFAULT '2',
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('06c172ea-7b3f-433e-bf4b-5c6c00e9af00',18,'Translations','pt_PT','0','bool',0,'Enable translations in Portuguese',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('0803028e-58ab-4c97-adaa-30630009f175',2,'RateLimit','numberOfRequests','30','numeric',0,'The maximum number of requests allowed per minute for sensitive routes such as login and registration.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('080e1a14-c97f-47db-8e69-43caa2020076',5,'Translations','el_GR','0','bool',0,'Enable translations in Greek',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('09fbdfb8-2c3b-4779-ae39-a08f6f8a6511',0,'Anthropic','apiKey','your-api-key-here','text',1,'This field is used to store your Anthropic API key, which grants access to a range of AI-powered features and services provided by Anthropic. These features are designed to enhance your content management system and streamline various tasks. Some of the key functionalities include auto tagging, SEO text generation, image alt text & keyword generation.',NULL,12,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('0c4ca92a-6ed0-4cb9-bf5e-68dbe9ab968c',2,'SEO','siteMetaDescription','Default site meta description','textarea',0,'The site meta description is a brief summary of your website\'s content and purpose. It appears in search engine results below the page title and URL, providing potential visitors with a snapshot of what your site offers. Craft a compelling and informative description to encourage clicks and improve search engine optimization (SEO).',NULL,4,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('1173652b-d5d5-42df-a8b3-85a0b64ce342',0,'Editing','editor','trumbowyg','select',0,'Chose your default editor for posts and pages content. Trumbowyg is good for HTML whilst Markdown-It supports Markdown.','{\n  \"trumbowyg\": \"Trumbowyg\",\n  \"markdownit\": \"Markdown-It\"\n}',2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('152fd57e-a743-4f64-88ad-9ffbb13f2e3c',2,'Translations','cs_CZ','0','bool',0,'Enable translations in Czech',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('1709282a-af2f-44e1-8a27-4fbe70457639',4,'ImageSizes','medium','300','numeric',0,'The width for the medium image size.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('19053887-b32f-4be1-b35c-d15ae580c6ca',8,'AI','imageAnalysis','0','bool',0,'Enable or disable the automatic image analysis feature to enhance your content\'s accessibility. When activated, the system will examine each images to generate relevant keywords and descriptive alt text. This functionality ensures that images are appropriately tagged, improving SEO and providing a better experience for users who rely on screen readers.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('1e1b5fba-8f2b-4fc1-ab73-94bdbfcbbf6a',1,'SEO','siteMetakeywords','Default site meta keywords','textarea',0,'Metakeywords are a set of keywords or phrases that describe the content of your website. These keywords are used by search engines to index your site and improve its visibility in search results. Enter relevant and specific keywords that accurately represent the topics and themes of your site content.',NULL,4,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('1f7a2597-b828-48e4-a440-80f0148e82f6',10,'Translations','hr_HR','0','bool',0,'Enable translations in Croatian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('24b2621c-72d2-487e-837d-04cb3afe0426',21,'Translations','sk_SK','0','bool',0,'Enable translations in Slovak',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('27b4e21e-4074-43dc-971f-f56ffa318842',3,'Translations','da_DK','0','bool',0,'Enable translations in Danish',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('288de94f-6447-44f2-85e3-0fb05ac6651c',16,'RateLimit','registerNumberOfRequests','5','numeric',0,'Maximum registration requests allowed within the time window.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('2b0bdd7e-b0ee-49d7-9731-a3d3bdc01623',10,'RateLimit','loginNumberOfRequests','5','numeric',0,'Maximum login attempts allowed within the time window.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('2d292f78-ccfb-4bf2-8380-505df7869dc5',25,'Translations','uk_UA','0','bool',0,'Enable translations in Ukrainian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('30404fbd-aab7-4fa5-8337-3dabe169890b',6,'AI','articleTags','0','bool',0,'Automatically generate relevant tags for your articles and pages based on their content. When you save an article or page, the system will analyze the text and create tags that best represent the main topics and keywords. These tags will then be automatically linked to the corresponding article or page, making it easier for readers to find related content on your website.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('32b089e2-c5c0-4797-abdb-e5a6e02c6963',3,'SEO','siteStrapline','Welcome to Willow CMS','textarea',0,'The site strapline is a brief, catchy phrase or slogan that complements your site name. It provides additional context or a memorable tagline that encapsulates the essence of your website. This strapline is often displayed alongside the site name in headers or footers.',NULL,4,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('33062288-086b-4eb6-8179-4e186127dfa9',1,'Users','registrationEnabled','0','bool',0,'Turn this on to enable users to register accounts on the site.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('33fd3570-8737-412b-be03-2bd49c608e18',5,'AI','tagSEO','0','bool',0,'Optimize your tags for search engines and social media by automatically generating SEO metadata. When enabled, the system will create a meta title, meta description, meta keywords, and tailored descriptions for Facebook, LinkedIn, Instagram, and Twitter.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('3555d02e-8a68-4e8d-a191-5c82706782c0',6,'Translations','es_ES','0','bool',0,'Enable translations in Spanish',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('370ac655-c3b5-4418-8d40-9693dd653fa1',0,'i18n','provider','google','select',0,'This setting is used for updating the built-in translations for the Willow CMS interface. Options include Google or Anthropic, with Google generally providing better translations. For auto translation of your website content, see the Translations section to enable languages.','{\n  \"google\": \"Google\",\n  \"anthropic\": \"Anthropic\"\n}',2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('371d5998-4a67-4411-9004-144e1532a92b',4,'SEO','robots','User-agent: *\nAllow: /{LANG}/\nAllow: /{LANG}/articles/*\nAllow: /{LANG}/pages/*\nAllow: /{LANG}/sitemap.xml\n\nDisallow: /admin/\nDisallow: /{LANG}/users/login\nDisallow: /{LANG}/users/register\nDisallow: /{LANG}/users/forgot-password\nDisallow: /{LANG}/users/reset-password/*\nDisallow: /{LANG}/users/confirm-email/*\nDisallow: /{LANG}/users/edit/*\nDisallow: /{LANG}/cookie-consents/edit\n\n# Prevent indexing of non-existent listing pages\nDisallow: /{LANG}/articles$\nDisallow: /{LANG}/pages$\n\nSitemap: /{LANG}/sitemap.xml','textarea',0,'The template for robots.txt file. Use {LANG} as a placeholder for the language code. This template will be used to generate the robots.txt file content.',NULL,4,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('3a3617d2-580d-49e0-a1bc-f6a901670e30',2,'SitePages','mainMenuShow','root','select',0,'Should the main menu show all root pages or only selected pages?','{\n  \"root\": \"Top Level Pages\",\n  \"selected\": \"Selected Pages\"\n}',2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('3c31b25a-474a-45a8-a9b5-f91877240142',17,'RateLimit','registerNumberOfSeconds','300','numeric',0,'Time window in seconds for registration rate limiting (300 = 5 minutes).',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('46214e17-f7c0-4951-a968-3dedaf55629b',1,'AI','enabled','0','bool',0,'Harness the power of artificial intelligence to enhance your content creation process. By enabling AI features, you gain access to a range of powerful tools, such as automatic article summarization, SEO metadata generation, and multilingual translation.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('48533bab-fc61-40ff-87c1-80e28cbc7a4c',15,'Translations','nl_NL','0','bool',0,'Enable translations in Dutch',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('4c554061-e88a-4a5c-b74e-9eb460136e5e',8,'Translations','fi_FI','0','bool',0,'Enable translations in Finnish',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('4e53cc65-994f-48a1-bfca-973fae25a816',6,'SEO','siteName','Willow CMS','text',0,'This field represents the official name of your website. It is typically displayed in the title bar of web browsers and is used in various places throughout the site to identify your brand or organization. Ensure that the name is concise and accurately reflects the purpose or identity of your site.',NULL,4,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('4fb6e581-b095-49c5-bc39-a347d3c39777',7,'ImageSizes','teeny','50','numeric',0,'The width for the teeny image size.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('57615bb1-6ffe-4c5c-9bd3-5ad3541666fb',2,'ImageSizes','extraLarge','500','numeric',0,'The width for the extra-large image size.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('588cb1b6-f065-4aca-957d-ff8c5d4921e3',12,'RateLimit','adminNumberOfRequests','40','numeric',0,'Maximum admin area requests allowed within the time window.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('612e3e42-0f18-4e93-aff5-33051297b400',11,'RateLimit','loginNumberOfSeconds','60','numeric',0,'Time window in seconds for login rate limiting.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('61c31ba9-da6a-494d-b284-311af74bccae',2,'Comments','pagesEnabled','0','bool',0,'Turn this on to enable logged in users to comment on your pages.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('63a5e369-74b8-43ef-8d15-90ff15c124be',19,'Translations','ro_RO','0','bool',0,'Enable translations in Romanian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('64469f31-9621-49f7-9ccf-ce24dbad02df',1,'ImageSizes','massive','800','numeric',0,'The width for the massive image size.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('66d746c9-ef5a-4a8e-b5f0-30038895c006',24,'Translations','tr_TR','0','bool',0,'Enable translations in Turkish',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('675643e4-c4f2-4675-86b9-5a978ff583a9',4,'Security','enableRateLimiting','1','bool',0,'Enable rate limiting for IP addresses. When enabled, the system will track request frequency and temporarily block IPs that exceed the configured limits.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('67f99f8a-aa73-4b08-aea6-20104f6d16c8',3,'SitePages','mainTagMenuShow','root','select',0,'Should the main tag menu show all root tags or only selected tags?','{\n  \"root\": \"Top Level Tags\",\n  \"selected\": \"Selected Tags\"\n}',2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('6c253f26-ac05-434f-a43e-48c820f9098e',0,'i18n','locale','en_GB','select',0,'This setting determines the default language for the admin area, allowing users to select languages such as French or German.','{\n  \"de_DE\": \"German\",\n  \"fr_FR\": \"French\",\n  \"es_ES\": \"Spanish\",\n  \"it_IT\": \"Italian\",\n  \"pt_PT\": \"Portuguese\",\n  \"nl_NL\": \"Dutch\",\n  \"pl_PL\": \"Polish\",\n  \"ru_RU\": \"Russian\",\n  \"sv_SE\": \"Swedish\",\n  \"da_DK\": \"Danish\",\n  \"fi_FI\": \"Finnish\",\n  \"no_NO\": \"Norwegian\",\n  \"el_GR\": \"Greek\",\n  \"tr_TR\": \"Turkish\",\n  \"cs_CZ\": \"Czech\",\n  \"hu_HU\": \"Hungarian\",\n  \"ro_RO\": \"Romanian\",\n  \"sk_SK\": \"Slovak\",\n  \"sl_SI\": \"Slovenian\",\n  \"bg_BG\": \"Bulgarian\",\n  \"hr_HR\": \"Croatian\",\n  \"et_EE\": \"Estonian\",\n  \"lv_LV\": \"Latvian\",\n  \"lt_LT\": \"Lithuanian\",\n  \"uk_UA\": \"Ukrainian\",\n  \"en_GB\": \"British English\"\n}',2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('6ca96b89-6fc5-450a-9368-e7f6217656dd',1,'SitePages','privacyPolicy','None','select-page',0,'Choose which page to show as your site Privacy Policy.','',2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('6da3b8b3-9451-488a-bbe8-05f81d54fc8f',16,'Translations','no_NO','0','bool',0,'Enable translations in Norwegian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('731916e5-90a7-4d37-a5f6-5370d735caa6',13,'RateLimit','adminNumberOfSeconds','60','numeric',0,'Time window in seconds for admin area rate limiting.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('75acf40c-6218-4e10-8f75-0c9f5e3f6acd',13,'Translations','lt_LT','0','bool',0,'Enable translations in Lithuanian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('77b442d0-a9fa-4487-96a4-dba0a6921c66',22,'Translations','sl_SI','0','bool',0,'Enable translations in Slovenian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('7a4796ff-1bc4-4313-af36-d6a30dac921e',2,'Security','trustedProxies','','textarea',0,'List of trusted proxy IP addresses (one per line). Only requests from these IPs will have their forwarded headers honored when trustProxy is enabled. Leave empty to trust all proxies (not recommended for production).',NULL,6,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('7e4f672a-672d-4fa9-af9a-e0cb69c85ce7',201,'AI','galleryTranslations','0','bool',0,'Enable automatic translation of image galleries to all enabled languages.',NULL,2,'2025-08-04 17:04:49','2025-08-04 17:04:49'),('8386f7d7-4921-4e16-a342-f00ed2115e3c',17,'Translations','pl_PL','0','bool',0,'Enable translations in Polish',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('887189dd-f78c-4e04-958c-3b58cf5f1d8d',4,'Google','youtubeApiKey','your-api-key-here','text',1,'This field is used to store your YouTube API key, which is required to access your videos to insert into post and page content.',NULL,12,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('8a314d98-93a5-4cdb-af40-d0731eabe3ad',1,'RateLimit','numberOfSeconds','60','numeric',0,'This field complements the \"Rate Limit: Number Of Requests\" setting by specifying the time window in which the request limit is enforced. It determines the duration, in seconds, for which the rate limit is applied. For example, if you set the \"Rate Limit: Number Of Requests\" to 100 and the \"Rate Limit: Number Of Seconds\" to 60, it means that an IP address can make a maximum of 100 requests within a 60-second window. If an IP address exceeds this limit within the specified time frame, they will be blocked for a certain period to prevent further requests and protect your server from potential abuse or overload.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('8af23070-7f41-45bd-8c1d-72b9097d7160',20,'Security','suspiciousRequestThreshold','3','numeric',0,'Number of suspicious requests before blocking an IP.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('8afb8fd7-da1e-4216-aa2e-7a1997334aa4',1,'Translations','bg_BG','0','bool',0,'Enable translations in Bulgarian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('8b1707cf-8530-44de-912f-2d50b00c0bc3',0,'Blog','articleDisplayMode','summary','select',0,'This setting controls if articles on the blog index show their Summary or Body text.','{\n  \"summary\": \"Summary\",\n  \"lede\": \"Lede\",\n  \"body\": \"Body\"\n}',2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('8f98c11b-5483-4e2c-9d67-98feb524df40',0,'Email','reply_email','noreply@example.com','text',0,'The \"Reply Email\" field allows you to specify the email address that will be used as the \"Reply-To\" address for outgoing emails sent from Willow CMS. When a recipient receives an email from your website and chooses to reply to it, their response will be directed to the email address specified in this field.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('915ab6d2-c414-46ba-b34b-5c1b567ad1af',3,'AI','tagTranslations','0','bool',0,'Automatically translate your tags into any of the 25 languages enabled in the translations settings. When you publish a page or article, the system will generate high-quality translations.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('9abf7787-8d37-45de-95e8-d86ec855c39c',0,'PagesAndArticles','additionalImages','0','bool',0,'Enable additional image uploads on your Articles and Pages.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('a8913506-1303-4aea-9101-28966aead4ad',4,'AI','articleSEO','0','bool',0,'Optimize your articles and pages for search engines and social media by automatically generating SEO metadata. When enabled, the system will create a meta title, meta description, meta keywords, and tailored descriptions for Facebook, LinkedIn, Instagram, and Twitter.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('acfdf494-95e1-4375-8ea1-373560eb4b23',200,'AI','gallerySEO','0','bool',0,'Enable AI-powered SEO field generation for image galleries.',NULL,2,'2025-08-04 17:04:49','2025-08-04 17:04:49'),('b773de6d-92e4-459b-9730-af85a9316704',20,'Translations','ru_RU','0','bool',0,'Enable translations in Russian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('b8cfe7cf-dbeb-4561-aced-7dfa90eadb2a',1,'Google','tagManagerHead','<!-- Google tag (gtag.js) -->\n<script async src=\"https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX\"></script>\n<script>\n  window.dataLayer = window.dataLayer || [];\n  function gtag(){dataLayer.push(arguments);}\n  gtag(\'js\', new Date());\n  gtag(\'config\', \'G-XXXXXXXXXX\');\n</script>','textarea',1,'The Google Tag Manager <head> tag is a JavaScript snippet placed in the <head> section that loads the GTM container and enables tag management without direct code modifications.',NULL,8,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('bec10113-eebe-4296-b2ef-01d928bb59f8',2,'AI','articleTranslations','0','bool',0,'Automatically translate your articles into any of the 25 languages enabled in the translations settings. When you publish a page or article, the system will generate high-quality translations.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('bfb555d0-42fc-47d5-b939-02bbc2da662b',3,'Security','blockOnNoIp','1','bool',0,'Block requests when the client IP address cannot be determined. Recommended for production environments to prevent IP detection bypass.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('c068eeb0-8066-49df-8061-7e9c680ca43e',14,'Translations','lv_LV','0','bool',0,'Enable translations in Latvian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('c11d230c-4c3b-46d1-ac96-d2edeb3707a2',3,'ImageSizes','large','400','numeric',0,'The width for the large image size.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('c5285f19-1ffe-48bc-8900-36c5990d0b44',8,'ImageSizes','micro','10','numeric',0,'The width for the micro image size.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('c680ae32-4562-47a4-b572-4fb377f89450',23,'Translations','sv_SE','0','bool',0,'Enable translations in Swedish',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('c803f9fa-040f-4d00-a052-6ec8ee978956',200,'AI','gallerySEO','0','bool',0,'Enable AI-powered SEO field generation for image galleries.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('cb2f4d90-a058-4e67-b197-0952eb9d900c',12,'Translations','it_IT','0','bool',0,'Enable translations in Italian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('cbc516c8-f472-4761-89bc-d50aedfe7e0e',4,'Translations','de_DE','0','bool',0,'Enable translations in German',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('cd60a9fd-8790-4702-9670-3e197f6f0b48',15,'RateLimit','passwordResetNumberOfSeconds','300','numeric',0,'Time window in seconds for password reset rate limiting (300 = 5 minutes).',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('cf20e2e0-036a-489c-92a7-c6ef99eb90f1',21,'Security','suspiciousWindowHours','24','numeric',0,'Time window in hours for counting suspicious requests.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('d4573510-7958-4644-9d73-8aa4a82f7e66',201,'AI','galleryTranslations','0','bool',0,'Enable automatic translation of image galleries to all enabled languages.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('d88b080e-00d3-49f2-a91c-a36b8213a08c',1,'Security','trustProxy','0','bool',0,'Enable this setting if Willow CMS is deployed behind a proxy or load balancer that modifies request headers. When enabled, the application will trust the `X-Forwarded-For` and `X-Real-IP` headers to determine the original client IP address. Use this setting with caution, as it can expose Willow CMS to IP spoofing if untrusted proxies are allowed.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('dc7384d2-3053-4966-9f1d-f66585fb5f04',6,'ImageSizes','tiny','100','numeric',0,'The width for the tiny image size.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('e09b1f36-8f07-423b-a9f3-21314875e85b',1,'Comments','articlesEnabled','0','bool',0,'Turn this on to enable logged in users to comment on your articles.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('e67e0104-8286-4d41-bac9-92b35b4b2fac',5,'ImageSizes','small','200','numeric',0,'The width for the small image size.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('e9c839e4-b133-4578-bf2a-95b682baaeea',22,'Security','suspiciousBlockHours','24','numeric',0,'How long to block IPs that exceed the suspicious request threshold (in hours).',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('ec8d3ad6-43d8-45a4-b953-18fc2c36d763',5,'Google','youtubeChannelId','your-api-key-here','text',1,'This field is used to store your YouTube Channel ID, which is required to allow you to filter videos to just your own.',NULL,12,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('f0ca7a1a-a69e-4c50-83f9-624ed2b7af8d',3,'Google','translateApiKey','your-api-key-here','text',1,'This field is used to store your Google API key, which is required to access and utilize the Google Cloud Translation API. The Google Cloud Translation API allows you to integrate machine translation capabilities into your content management system, enabling automatic translation of your website content into different languages.',NULL,12,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('f6fc4453-f958-46dd-801d-1e1a74397312',7,'AI','articleSummaries','0','bool',0,'Automatically generate concise and compelling summaries for your articles and pages. When enabled, the system will analyze the content and create a brief synopsis that captures the key points. These summaries will appear on the article index page and other areas where a short overview is preferable to displaying the full text.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('f87bd766-318a-4a6a-9766-a28ff805f153',9,'Translations','fr_FR','0','bool',0,'Enable translations in French',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('f898dae2-51ea-48b0-8c43-491727bf5c99',14,'RateLimit','passwordResetNumberOfRequests','3','numeric',0,'Maximum password reset requests allowed within the time window.',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('fa821901-e21f-43af-bb29-c2cd2dd052d0',7,'Translations','et_EE','0','bool',0,'Enable translations in Estonian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12'),('fbcfeee5-16a2-4d89-9449-7bd175bd0b54',11,'Translations','hu_HU','0','bool',0,'Enable translations in Hungarian',NULL,2,'2025-08-04 17:04:12','2025-08-04 17:04:12');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `slugs`
--

DROP TABLE IF EXISTS `slugs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `slugs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `foreign_key` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`),
  KEY `idx_slugs_lookup` (`model`,`slug`),
  KEY `idx_slugs_foreign` (`model`,`foreign_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `slugs`
--

LOCK TABLES `slugs` WRITE;
/*!40000 ALTER TABLE `slugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `slugs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_logs`
--

DROP TABLE IF EXISTS `system_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `system_logs` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `context` text COLLATE utf8mb4_unicode_ci,
  `created` datetime NOT NULL,
  `group_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_logs`
--

LOCK TABLES `system_logs` WRITE;
/*!40000 ALTER TABLE `system_logs` DISABLE KEYS */;
INSERT INTO `system_logs` VALUES ('26bf6194-658d-4524-8807-9b19ffdfcdaa','info','User created successfully: admin (ID: 7f65ab2a-660c-4bd1-a101-784a268dc48d)','{\"scope\":[\"user_management\",\"user_creation\"]}','2025-08-04 18:04:13','general'),('cffdd6a9-5911-4cd9-a2bc-9d997eded92c','info','Attempting to create user with data: {\"username\":\"admin\",\"confirm_password\":\"password\",\"email\":\"admin@test.com\",\"is_admin\":true,\"active\":1}','{\"scope\":[\"user_management\",\"user_creation\"]}','2025-08-04 18:04:13','general');
/*!40000 ALTER TABLE `system_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` int DEFAULT NULL,
  `mime` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `facebook_description` text COLLATE utf8mb4_unicode_ci,
  `linkedin_description` text COLLATE utf8mb4_unicode_ci,
  `instagram_description` text COLLATE utf8mb4_unicode_ci,
  `twitter_description` text COLLATE utf8mb4_unicode_ci,
  `parent_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `main_menu` tinyint(1) NOT NULL DEFAULT '0',
  `lft` int NOT NULL,
  `rght` int NOT NULL,
  `modified` datetime DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags_translations`
--

DROP TABLE IF EXISTS `tags_translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags_translations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `locale` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `meta_title` text COLLATE utf8mb4_unicode_ci,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `meta_keywords` text COLLATE utf8mb4_unicode_ci,
  `facebook_description` text COLLATE utf8mb4_unicode_ci,
  `linkedin_description` text COLLATE utf8mb4_unicode_ci,
  `instagram_description` text COLLATE utf8mb4_unicode_ci,
  `twitter_description` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`,`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags_translations`
--

LOCK TABLES `tags_translations` WRITE;
/*!40000 ALTER TABLE `tags_translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `tags_translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_account_confirmations`
--

DROP TABLE IF EXISTS `user_account_confirmations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_account_confirmations` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `confirmation_code` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `confirmation_code` (`confirmation_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_account_confirmations`
--

LOCK TABLES `user_account_confirmations` WRITE;
/*!40000 ALTER TABLE `user_account_confirmations` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_account_confirmations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` int DEFAULT NULL,
  `mime` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('7f65ab2a-660c-4bd1-a101-784a268dc48d',1,'admin@test.com','$2y$10$n4s1BHYgKFUEIwwGoEEetOobpf40Vm0S1EVRe5fxoKn/UFrym/U5e',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-04 18:04:13','2025-08-04 18:04:13','admin',1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'cms'
--

--
-- Dumping routines for database 'cms'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-04 17:07:41
