CREATE TABLE matches (
char1 TEXT,
char2 TEXT,
player1 TEXT,
player2 TEXT,
winner TEXT,
event TEXT,
event_part NUMERIC,
sort_order NUMERIC,
published NUMERIC,
yt_id TEXT,
start NUMERIC
);

CREATE TABLE videos (
yt_id TEXT,
title TEXT,
event TEXT,
event_part NUMERIC,
content TEXT,
published NUMERIC,
state TEXT
);

CREATE INDEX state_idx ON videos(state ASC);
CREATE INDEX yt_id_idx ON videos(yt_id ASC);
