-- Migration: dialogue scenarios under topics
-- Date: 2026-03-21
-- Flow supported:
--   Topic (already exists) -> many Scenario containers -> many Dialogues per Scenario

START TRANSACTION;

-- 1) Create scenario container table under each topic
CREATE TABLE IF NOT EXISTS dialogue_scenarios (
  id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  topic_id INT(10) UNSIGNED NOT NULL,
  scenario_name VARCHAR(200) NOT NULL,
  sort_order INT(10) UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_dialogue_scenarios_topic_id (topic_id),
  UNIQUE KEY uq_dialogue_scenarios_topic_name (topic_id, scenario_name),
  CONSTRAINT fk_dialogue_scenarios_topic
    FOREIGN KEY (topic_id) REFERENCES topics(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Add scenario reference and per-scenario line ordering to dialogues
ALTER TABLE dialogues
  ADD COLUMN scenario_id INT(10) UNSIGNED NULL AFTER topic_id,
  ADD COLUMN line_no INT(10) UNSIGNED NULL AFTER scenario_id,
  ADD KEY idx_dialogues_scenario_id (scenario_id);

-- 3) Backfill scenario containers from existing dialogue.topic_id
--    One default scenario is created per topic that already has dialogues.
INSERT INTO dialogue_scenarios (topic_id, scenario_name, sort_order)
SELECT d.topic_id, 'Scenario 1', 1
FROM dialogues d
LEFT JOIN dialogue_scenarios ds
  ON ds.topic_id = d.topic_id
 AND ds.scenario_name = 'Scenario 1'
WHERE ds.id IS NULL
GROUP BY d.topic_id;

-- 4) Map existing dialogues to the default scenario in their topic
UPDATE dialogues d
JOIN dialogue_scenarios ds
  ON ds.topic_id = d.topic_id
 AND ds.scenario_name = 'Scenario 1'
SET d.scenario_id = ds.id
WHERE d.scenario_id IS NULL;

-- 5) Backfill line_no sequence per scenario
SET @prev_scenario := NULL;
SET @seq := 0;

UPDATE dialogues d
JOIN (
    SELECT id,
           scenario_id,
           (@seq := IF(@prev_scenario = scenario_id, @seq + 1, 1)) AS new_line_no,
           (@prev_scenario := scenario_id) AS _scenario_marker
    FROM dialogues
    ORDER BY scenario_id, id
) x ON x.id = d.id
SET d.line_no = x.new_line_no;

-- 6) Enforce integrity for new flow
ALTER TABLE dialogues
  MODIFY COLUMN scenario_id INT(10) UNSIGNED NOT NULL,
  MODIFY COLUMN line_no INT(10) UNSIGNED NOT NULL,
  ADD CONSTRAINT fk_dialogues_scenario
    FOREIGN KEY (scenario_id) REFERENCES dialogue_scenarios(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  ADD UNIQUE KEY uq_dialogues_scenario_line (scenario_id, line_no);

COMMIT;
