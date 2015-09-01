CREATE TABLE taxonomy(
	id numeric, 
	parent_id numeric,
	name text COLLATE NOCASE,
	rank text COLLATE NOCASE,
	is_shown numeric)
	
CREATE INDEX name_index ON taxonomy(name COLLATE NOCASE)