CREATE TABLE interactions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  interaction_id TEXT NOT NULL,
  protocol TEXT NOT NULL,
  source_ip TEXT NOT NULL,
  request_data TEXT NOT NULL,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_interaction_id ON interactions (interaction_id);
CREATE INDEX idx_timestamp ON interactions (timestamp);
