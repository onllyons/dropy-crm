-- Flash Cards analytics: recommended indexes
-- Run once in tenant DB (example: onllyons_en).
-- If an index already exists with another name, skip that ALTER.

-- Words categories and lessons
ALTER TABLE cardsLearnWordsCategoryPag
    ADD INDEX idx_clwcp_url_category (url_category),
    ADD INDEX idx_clwcp_code_name (code_name);

ALTER TABLE cardsLearnWordsPag
    ADD INDEX idx_clwp_group_category (group_category),
    ADD INDEX idx_clwp_url (url),
    ADD INDEX idx_clwp_category (category),
    ADD INDEX idx_clwp_group_level_id (group_category, category, id);

-- Words quiz
ALTER TABLE cardsLearnWordsQuiz
    ADD INDEX idx_clwq_quiz_url (quiz_url);

-- Words history (user progress + lesson joins + period filters)
ALTER TABLE cardsLearnWordsHistory
    ADD INDEX idx_clwh_card_user_end (card_id, user_id, end_time),
    ADD INDEX idx_clwh_user_end_start_id (user_id, end_time, start_time, id);

-- Phrases lessons and content
ALTER TABLE flashcards_question_sentences
    ADD INDEX idx_fqs_url (url);

ALTER TABLE flashcards_question_sentences_content
    ADD INDEX idx_fqsc_url_display (url_display);

-- Phrases history (user progress + lesson joins + period filters)
ALTER TABLE flashcards_question_history
    ADD INDEX idx_fqh_user_card_end (user_id, card_id, end_time),
    ADD INDEX idx_fqh_card_user (card_id, user_id),
    ADD INDEX idx_fqh_user_end_start_id (user_id, end_time, start_time, id);
