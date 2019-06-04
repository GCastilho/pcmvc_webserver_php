CREATE DATABASE telemetryProject;
use telemetryProject;

CREATE TABLE api_credential (
	matricula bigint NOT NULL UNIQUE,
	api_key varchar(30) NOT NULL,
	PRIMARY KEY(matricula)
);

CREATE TABLE person (
	matricula int NOT NULL,
	username varchar(10) NOT NULL,
	salt varchar(32) NOT NULL,	/*$better_token = md5(uniqid(matriculand(), true));*/
	password_hash varchar(128) NOT NULL,
	nome varchar(50) NOT NULL,
	acess_level tinyint NOT NULL,

	PRIMARY KEY(matricula)
);

CREATE TABLE telemetry (
	matricula bigint NOT NULL,
	timestamp int(10) NOT NULL,
	latitude varchar(11) NOT NULL,
	longitude varchar(11) NOT NULL,
	altura float(5) NOT NULL,
	wind_velocity float(5),

	FOREIGN KEY(matricula) REFERENCES api_credential(matricula)
);