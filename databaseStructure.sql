CREATE DATABASE telemetryProject;
use telemetryProject;

CREATE TABLE Credential (
	RA bigint NOT NULL UNIQUE,
	Api_Key varchar(30) NOT NULL,
	PRIMARY KEY (RA)
);

CREATE TABLE Telemetry (
	RA bigint NOT NULL,
	timestamp int(10) NOT NULL,
	Latitude varchar(11) NOT NULL,
	Longitude varchar(11) NOT NULL,
	windVelocity float(5),

	FOREIGN KEY(RA)	REFERENCES credentials(RA)
);