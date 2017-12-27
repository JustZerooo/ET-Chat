CREATE TABLE ###prefix###etchat_messages
(
  etchat_id serial8 NOT NULL,
  etchat_user_fid int8 NOT NULL,
  etchat_user varchar,
  etchat_text text,
  etchat_text_css text,
  etchat_timestamp int8,
  etchat_fid_room int4 NOT NULL DEFAULT 1,
  etchat_privat int4 DEFAULT 0,
  etchat_user_ip varchar DEFAULT NULL,
  CONSTRAINT etchat_messages_pkey PRIMARY KEY (etchat_id)
)
WITHOUT OIDS;
-- limit --

CREATE TABLE ###prefix###etchat_rooms (
  etchat_id_room serial8 NOT NULL,
  etchat_roomname varchar NOT NULL,
  etchat_room_goup int4 DEFAULT 0,
  etchat_room_pw varchar DEFAULT NULL,
  etchat_room_message varchar DEFAULT NULL,
  CONSTRAINT etchat_rooms_pkey PRIMARY KEY (etchat_id_room)
)
WITHOUT OIDS;
-- limit --
INSERT INTO ###prefix###etchat_rooms (etchat_roomname, etchat_room_goup, etchat_room_message) VALUES ('Lobby', 0, 'Hallo and welcome to ET-Chat v3.0.7 (connected to PostgreSQL).\r\nYou are now in the Lobby-Room and this entrance message can be modified by the Admin of that Chat.<hr />Hallo und willkommen zu ET-Chat v3.0.7 (angeschlossen an PostgreSQL).\r\nDu befindest dich nun in dem Raum Lobby und diese Eingangsnachricht kann vom Administrator verändert oder abgeschaltet werden.');
-- limit --
INSERT INTO ###prefix###etchat_rooms (etchat_roomname, etchat_room_goup, etchat_room_message) VALUES ('Raum for all', 0, 'All chat user are allowed to enter this Room.<hr />Alle Chatbenutzer dürfen diesen Raum betretten.');
-- limit --
INSERT INTO ###prefix###etchat_rooms (etchat_roomname, etchat_room_goup) VALUES ('Room for admins', 1);
-- limit --
INSERT INTO ###prefix###etchat_rooms (etchat_roomname, etchat_room_goup, etchat_room_pw, etchat_room_message) VALUES ('Room with password', 3, 'test', 'Dieser Passwort ist ja besonders schwer... ;-)');
-- limit --

CREATE TABLE ###prefix###etchat_useronline
(
  etchat_onlineid serial8 NOT NULL,
  etchat_onlineuser_fid int8 NOT NULL,
  etchat_onlinetimestamp int8,
  etchat_onlineip varchar,
  etchat_fid_room int4 NOT NULL DEFAULT 1,
  etchat_user_online_room_goup int4 NOT NULL,
  etchat_user_online_room_name varchar,
  etchat_user_online_user_name varchar,
  etchat_user_online_user_priv varchar,
  etchat_user_online_user_sex varchar NOT NULL,
  etchat_user_online_user_status_img varchar default NULL,
  etchat_user_online_user_status_text varchar default NULL,
  CONSTRAINT etchat_useronline_pkey PRIMARY KEY (etchat_onlineid)
)
WITHOUT OIDS;
-- limit --

CREATE TABLE ###prefix###etchat_user (
  etchat_user_id serial8 NOT NULL,
  etchat_username varchar NOT NULL,
  etchat_userpw varchar default NULL,
  etchat_userprivilegien varchar NOT NULL default 'gast',
  etchat_usersex varchar NOT NULL default 'n',
  etchat_reg_timestamp timestamp NOT NULL DEFAULT '1980-01-01 00:00:00',
  etchat_reg_ip varchar DEFAULT NULL,
  CONSTRAINT etchat_user_pkey PRIMARY KEY (etchat_user_id)
)
WITHOUT OIDS;
-- limit --
INSERT INTO ###prefix###etchat_user (etchat_username, etchat_userpw, etchat_userprivilegien) VALUES ('<u>System</u>', NULL, 'system');
-- limit --
INSERT INTO ###prefix###etchat_user (etchat_username, etchat_userpw, etchat_userprivilegien) VALUES ('Admin', '21232f297a57a5a743894a0e4a801fc3', 'admin');
-- limit --

CREATE TABLE ###prefix###etchat_blacklist (
  etchat_blacklist_id serial8 NOT NULL,
  etchat_blacklist_ip varchar NOT NULL,
  etchat_blacklist_userid int8 NOT NULL,
  etchat_blacklist_time int8 NOT NULL,
  CONSTRAINT etchat_blacklist_pkey PRIMARY KEY (etchat_blacklist_id)
)
WITHOUT OIDS;
-- limit --

CREATE TABLE ###prefix###etchat_config (
  etchat_config_id serial8 NOT NULL,
  etchat_config_reloadsequenz int8 NOT NULL,
  etchat_config_messages_im_chat int4 NOT NULL,
  etchat_config_style varchar NOT NULL,
  etchat_config_loeschen_nach int4 NOT NULL,
  etchat_config_lang varchar NOT NULL,
  CONSTRAINT etchat_config_pkey PRIMARY KEY (etchat_config_id )
)
WITHOUT OIDS;
-- limit --
INSERT INTO ###prefix###etchat_config (etchat_config_reloadsequenz, etchat_config_messages_im_chat, etchat_config_style, etchat_config_loeschen_nach, etchat_config_lang) VALUES (4000, 22, 'etchat_white', 1, 'lang_de.xml');
-- limit --


CREATE TABLE ###prefix###etchat_kick_user (
  id serial8 NOT NULL,
  etchat_kicked_user_id int8 NOT NULL,
  etchat_kicked_user_time int8 NOT NULL,
  CONSTRAINT etchat_kick_user_pkey PRIMARY KEY ( id )
)
WITHOUT OIDS;
-- limit --

CREATE TABLE ###prefix###etchat_smileys (
  etchat_smileys_id serial8 NOT NULL,
  etchat_smileys_sign varchar NOT NULL,
  etchat_smileys_img varchar NOT NULL,
  CONSTRAINT etchat_smileys_pkey PRIMARY KEY (etchat_smileys_id)
)
WITHOUT OIDS;
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':angry:', 'smilies/angry.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':-]', 'smilies/approve.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':-}', 'smilies/blushing.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':charming:', 'smilies/charming.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':-P', 'smilies/cheeky.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':cheesy:', 'smilies/cheesy.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES ('8-)', 'smilies/cool.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':*(', 'smilies/cry.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES ('x-(', 'smilies/dead.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':dissap:', 'smilies/dissappointed.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':embar:', 'smilies/embarassed.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':evil:', 'smilies/evil.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':goofy:', 'smilies/goofy.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':-D', 'smilies/grin.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':-?', 'smilies/huh.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':idea:', 'smilies/idea.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':-L', 'smilies/laugh.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':lips:', 'smilies/lips.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':-x', 'smilies/lipsrsealed.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':mad:', 'smilies/mad.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':-K', 'smilies/ok.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':rolleyes:', 'smilies/rolleyes.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':-(', 'smilies/sad.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':-O', 'smilies/shocked.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':shy:', 'smilies/shy.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':smartass:', 'smilies/smartass.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':smarty:', 'smilies/smarty.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':-)', 'smilies/smiley.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':tongue:', 'smilies/tongue.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':undecided:', 'smilies/undecided.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':veryangry:', 'smilies/veryangry.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (';-)', 'smilies/wink.gif');
-- limit --
INSERT INTO ###prefix###etchat_smileys (etchat_smileys_sign, etchat_smileys_img) VALUES (':worried:', 'smilies/worried.gif');
-- limit --
