-- Create student_answers table to track which questions students have answered
-- This enables category-level progress tracking

CREATE TABLE IF NOT EXISTS student_answers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    quiz_id INT NOT NULL,
    subject_name VARCHAR(50) NOT NULL,
    category VARCHAR(255),
    level INT NOT NULL,
    is_correct BOOLEAN NOT NULL,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Foreign keys
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizes(id) ON DELETE CASCADE,
    
    -- Indexes for performance
    INDEX idx_user_subject (user_id, subject_name),
    INDEX idx_user_category (user_id, subject_name, category),
    INDEX idx_answered_at (answered_at),
    
    -- Prevent duplicate answers (same user answering same question multiple times)
    UNIQUE KEY unique_user_quiz (user_id, quiz_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add some sample data for testing (optional - remove if not needed)
-- This assumes you have existing users and quizzes
-- INSERT INTO student_answers (user_id, quiz_id, subject_name, category, level, is_correct)
-- SELECT 
--     u.id as user_id,
--     q.id as quiz_id,
--     q.subject_name,
--     q.category,
--     q.level,
--     FLOOR(RAND() * 2) as is_correct
-- FROM users u
-- CROSS JOIN quizes q
-- WHERE q.category IS NOT NULL
-- LIMIT 100;
