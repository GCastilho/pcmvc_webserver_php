CREATE DATABASE telemetryProject;
use telemetryProject;

CREATE TABLE Aluno (
	RA bigint NOT NULL UNIQUE,
	Api_Key varchar(30) NOT NULL,
	PRIMARY KEY(RA)
);

CREATE TABLE Telemetry (
	RA bigint NOT NULL,
	timestamp int(10) NOT NULL,
	Latitude varchar(11) NOT NULL,
	Longitude varchar(11) NOT NULL,
	windVelocity float(5),

	FOREIGN KEY(RA)	REFERENCES Aluno(RA)
);

CREATE TABLE Professor (
	ID int NOT NULL,
	Username varchar(10) NOT NULL,
	Salt varchar(32) NOT NULL,	/*$better_token = md5(uniqid(rand(), true));*/
	Password_Hash varchar(128) NOT NULL,
	Nome varchar(50) NOT NULL,
	Acess_Level tinyint NOT NULL,

	PRIMARY KEY(ID)
);