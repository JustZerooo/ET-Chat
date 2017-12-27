--
-- Tabellenstruktur für Tabelle 'etchat_blacklist'
--

DROP TABLE IF EXISTS ###prefix###etchat_blacklist;
-- limit --
CREATE TABLE  ###prefix###etchat_blacklist (
  etchat_blacklist_id int(8) NOT NULL auto_increment,
  etchat_blacklist_ip varchar(255) NOT NULL,
  etchat_blacklist_userid int(8) NOT NULL,
  etchat_blacklist_time int(30) NOT NULL,
  PRIMARY KEY  (etchat_blacklist_id)
);
-- limit --

--
-- Daten für Tabelle 'etchat_blacklist'
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'etchat_messages'
--

DROP TABLE IF EXISTS ###prefix###etchat_messages;
-- limit --
CREATE TABLE  ###prefix###etchat_messages (
  etchat_id bigint(20) unsigned NOT NULL auto_increment,
  etchat_user_fid int(8) NOT NULL,
  etchat_text text NOT NULL,
  etchat_text_css text NOT NULL,
  etchat_timestamp bigint(20) NOT NULL,
  etchat_fid_room int(11) NOT NULL default '1',
  etchat_privat int(8) default '0',
  etchat_user_ip varchar(20) DEFAULT NULL,
  PRIMARY KEY  (etchat_id)
);
-- limit --
--
-- Daten für Tabelle 'etchat_messages'
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'etchat_rooms'
--

DROP TABLE IF EXISTS ###prefix###etchat_rooms;
-- limit --
CREATE TABLE  ###prefix###etchat_rooms (
  etchat_id_room int(3) unsigned NOT NULL auto_increment,
  etchat_roomname varchar(100) NOT NULL,
  etchat_room_goup int(6) NOT NULL default '0',
  etchat_room_pw varchar(50) default NULL,
  etchat_room_message text,
  PRIMARY KEY  (etchat_id_room)
);
-- limit --
--
-- Daten für Tabelle 'etchat_rooms'
--

INSERT INTO ###prefix###etchat_rooms (etchat_id_room, etchat_roomname, etchat_room_goup, etchat_room_pw, etchat_room_message) VALUES
(1, 'Lobby', 0, NULL, 'Hallo and welcome to ET-Chat v3.0.7 (connected to MySQL).\r\nYou are now in the Lobby-Room and this entrance message can be modified by the Admin of that Chat.<hr />Hallo und willkommen zu ET-Chat v3.0.7 (angeschlossen an MySQL).\r\nDu befindest dich nun in dem Raum Lobby und diese Eingangsnachricht kann vom Administrator verÃ¤ndert oder abgeschaltet werden.'),
(2, 'Room for all', 0, NULL, 'All chat user are allowed to enter this Room.<hr />Alle Chatbenutzer dÃ¼rfen diesen Raum betretten.'),
(3, 'Room for admins', 1, NULL, NULL),
(4, 'Room password protected', 3, 'test', 'Dieser Passwort ist ja besonders schwer... ;-)');
-- limit --
-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'etchat_smileys'
--

DROP TABLE IF EXISTS ###prefix###etchat_smileys;
-- limit --
CREATE TABLE  ###prefix###etchat_smileys (
  etchat_smileys_id int(5) NOT NULL auto_increment,
  etchat_smileys_sign varchar(20) NOT NULL,
  etchat_smileys_img varchar(100) NOT NULL,
  PRIMARY KEY  (etchat_smileys_id)
);
-- limit --
--
-- Daten für Tabelle 'etchat_smileys'
--
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
-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'etchat_user'
--

DROP TABLE IF EXISTS ###prefix###etchat_user;
-- limit --
CREATE TABLE  ###prefix###etchat_user (
  etchat_user_id int(8) NOT NULL auto_increment,
  etchat_username varchar(200) NOT NULL,
  etchat_userpw varchar(40) default NULL,
  etchat_userprivilegien varchar(15) NOT NULL default 'gast',
  etchat_usersex varchar(1) NOT NULL default 'n',
  etchat_reg_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  etchat_reg_ip varchar(20) DEFAULT NULL,
  PRIMARY KEY  (etchat_user_id)
);
-- limit --
--
-- Daten für Tabelle 'etchat_user'
--

INSERT INTO ###prefix###etchat_user (etchat_user_id, etchat_username, etchat_userpw, etchat_userprivilegien) VALUES
(1, '<i>System</i>', NULL, 'system'),
(2, 'Admin', '21232f297a57a5a743894a0e4a801fc3', 'admin');
-- limit --
-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle 'etchat_useronline'
--

DROP TABLE IF EXISTS ###prefix###etchat_useronline;
-- limit --
CREATE TABLE  ###prefix###etchat_useronline (
  etchat_onlineid bigint(20) unsigned NOT NULL auto_increment,
  etchat_onlineuser_fid int(8) NOT NULL,
  etchat_onlinetimestamp bigint(20) NOT NULL,
  etchat_onlineip varchar(255) NOT NULL,
  etchat_fid_room int(11) NOT NULL default '1',
  etchat_user_online_room_goup int(6) NOT NULL,
  etchat_user_online_room_name varchar(100) NOT NULL,
  etchat_user_online_user_name varchar(200) NOT NULL,
  etchat_user_online_user_priv varchar(20) NOT NULL,
  etchat_user_online_user_sex varchar(1) NOT NULL,
  etchat_user_online_user_status_img varchar(200) default NULL,
  etchat_user_online_user_status_text varchar(200) default NULL,
  PRIMARY KEY  (etchat_onlineid)
);
-- limit --

--
-- Daten für Tabelle 'etchat_useronline'
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `etchat_config`
--
DROP TABLE IF EXISTS ###prefix###etchat_config;
-- limit --
CREATE TABLE  ###prefix###etchat_config (
  etchat_config_id int(11) NOT NULL auto_increment,
  etchat_config_reloadsequenz int(11) NOT NULL,
  etchat_config_messages_im_chat int(11) NOT NULL,
  etchat_config_style varchar(100) NOT NULL,
  etchat_config_loeschen_nach int(11) NOT NULL,
  etchat_config_lang varchar(30) NOT NULL,
  PRIMARY KEY  (etchat_config_id)
);
-- limit --
--
-- Daten für Tabelle 'etchat_config'
--

INSERT INTO ###prefix###etchat_config (etchat_config_id, etchat_config_reloadsequenz, etchat_config_messages_im_chat, etchat_config_style, etchat_config_loeschen_nach, etchat_config_lang) VALUES
(1, 4000, 22, 'etchat_white', 1, 'lang_de.xml');

-- limit --
DROP TABLE IF EXISTS ###prefix###etchat_kick_user;
-- limit --
CREATE TABLE ###prefix###etchat_kick_user (
  id int(11) NOT NULL auto_increment,
  etchat_kicked_user_id int(11) NOT NULL,
  etchat_kicked_user_time int(11) NOT NULL,
  PRIMARY KEY  (id)
)