-- #! sqlite
-- #{ skyblockspm

-- #{ player

-- # { init
CREATE TABLE IF NOT EXISTS skyblockspm_player
(
    uuid VARCHAR(32) PRIMARY KEY,
    name VARCHAR(32),
    skyblock STRING DEFAULT ''
    );
-- # }

-- # { load
-- #    :uuid string
SELECT *
FROM skyblockspm_player
WHERE uuid=:uuid;
-- # }

-- # { create
-- #   :uuid string
-- #   :name string
-- #   :skyblock string
INSERT INTO skyblockspm_player (uuid, name, skyblock)
VALUES (:uuid, :name, :skyblock);
-- # }

-- # { update
-- #    :uuid string
-- #    :name string
-- #    :skyblock string
UPDATE skyblockspm_player
SET skyblock=:skyblock,
    name=:name
WHERE uuid=:uuid;
-- # }

-- # }

-- # { sb

-- # { init
CREATE TABLE IF NOT EXISTS skyblockspm_sb
(
    uuid VARCHAR(32) PRIMARY KEY,
    name VARCHAR(32),
    leader VARCHAR(32),
    members STRING,
    world STRING,
    settings STRING,
    spawn STRING
    );
-- # }

-- # { load
-- #    :uuid string
SELECT *
FROM skyblockspm_sb
WHERE uuid=:uuid;
-- # }

-- # { create
-- #   :uuid string
-- #   :name string
-- #   :leader string
-- #   :members string
-- #   :world string
-- #   :settings string
-- #   :spawn string
INSERT INTO skyblockspm_sb (uuid, name, leader, members, world, settings, spawn)
VALUES (:uuid, :name, :leader, :members, :world, :settings, :spawn);
-- # }

-- # { delete
-- #   :uuid string
DELETE
FROM skyblockspm_sb
WHERE uuid=:uuid
-- # }

-- # { update
-- #    :uuid string
-- #    :name string
-- #    :leader string
-- #    :members string
-- #    :world string
-- #    :settings string
-- #    :spawn string
UPDATE skyblockspm_sb
SET name=:name,
    leader=:leader,
    members=:members,
    world=:world,
    settings=:settings,
    spawn=:spawn
WHERE uuid=:uuid;
-- # }

-- # }

-- # }
