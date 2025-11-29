CREATE DATABASE TUBES;

USE Tubes;
GO

DROP TABLE IF EXISTS Tonton;
DROP TABLE IF EXISTS Komen;
DROP TABLE IF EXISTS Subscribe;
DROP TABLE IF EXISTS Videos;
DROP TABLE IF EXISTS [Admin];
DROP TABLE IF EXISTS Channel;
DROP TABLE IF EXISTS Users;
DROP TABLE IF EXISTS Roles;
GO

CREATE TABLE Users (
    idUser INT IDENTITY(1,1) PRIMARY KEY,
    Username NVARCHAR(100) NOT NULL,
    Email NVARCHAR(100) UNIQUE NOT NULL,
    Pass NVARCHAR(255) NOT NULL,
    fotoProfil VARCHAR(255) DEFAULT 'Assets/NoProfile.jpg'
);

CREATE TABLE Channel (
    idChannel INT PRIMARY KEY IDENTITY(1,1),
    namaChannel VARCHAR(100) NOT NULL,
    deskripsi VARCHAR(500),
    fotoProfil VARCHAR(255) DEFAULT 'Assets/NoProfile.jpg',
    channelType TINYINT NOT NULL
);

CREATE TABLE Videos (
    idVideo INT PRIMARY KEY IDENTITY(1,1),
    title VARCHAR(100) NOT NULL,
    path VARCHAR(255) NOT NULL,
    uploaded_at DATETIME NOT NULL DEFAULT GETDATE(),
    description VARCHAR(500),
    thumbnail VARCHAR(255),
    idChannel INT NOT NULL,
	isActive bit NOT NULL DEFAULT 1,
    CONSTRAINT FK_Videos_Channel FOREIGN KEY (idChannel) REFERENCES Channel(idChannel)
);

CREATE TABLE Roles (
    idRole INT IDENTITY(1,1) PRIMARY KEY,
    RoleName NVARCHAR(50) NOT NULL UNIQUE
);


CREATE TABLE Admin (
    idUser INT NOT NULL UNIQUE,
    idChannel INT NOT NULL,
    idRole INT NOT NULL,
    IsActive TINYINT NOT NULL DEFAULT 1,
    CreatedAt DATETIME NOT NULL DEFAULT GETDATE(),
    PRIMARY KEY (idUser, idChannel),
    FOREIGN KEY (idUser) REFERENCES Users(idUser),
    FOREIGN KEY (idChannel) REFERENCES Channel(idChannel),
    FOREIGN KEY (idRole) REFERENCES Roles(idRole)
);


CREATE TABLE Komen (
    idKomen INT PRIMARY KEY IDENTITY(1,1),
    idVideo INT NOT NULL FOREIGN KEY REFERENCES Videos(idVideo),
    idUser INT NOT NULL FOREIGN KEY REFERENCES Users(idUser),
    tanggal DATETIME NOT NULL DEFAULT GETDATE(),
    komen NVARCHAR(4000) NOT NULL ,
    isActive BIT NOT NULL
);
CREATE TABLE Tonton (
    idVideo INT NOT NULL,
    idUser INT NOT NULL,
    tanggal DATETIME NOT NULL DEFAULT GETDATE(),
    lamaMenonton INT NOT NULL DEFAULT 0,
    jumlahTonton INT NOT NULL DEFAULT 1,
    likeDislike TINYINT NULL,
    PRIMARY KEY (idVideo, idUser),
    FOREIGN KEY (idVideo) REFERENCES Videos(idVideo),
    FOREIGN KEY (idUser) REFERENCES Users(idUser)
);

CREATE TABLE Subscribe (
    idChannel INT NOT NULL,
    idUser INT NOT NULL,
    tanggalSubscribe DATETIME NOT NULL DEFAULT GETDATE(),
    isActive BIT NOT NULL,
    PRIMARY KEY (idChannel, idUser),
    FOREIGN KEY (idChannel) REFERENCES Channel(idChannel),
    FOREIGN KEY (idUser) REFERENCES Users(idUser)
);

--Index Bitmap
-- Channel: channelType
CREATE NONCLUSTERED INDEX IX_Channel_channelType
ON Channel(channelType);

-- Videos: idChannel, isActive
CREATE NONCLUSTERED INDEX IX_Videos_idChannel_isActive
ON Videos(idChannel, isActive);

-- Admin: idChannel
CREATE NONCLUSTERED INDEX IX_Admin_idChannel
ON Admin(idChannel);

-- Komen: idUser, idVideo, isActive
CREATE NONCLUSTERED INDEX IX_Komen_idUser_idVideo_isActive
ON Komen(idUser, idVideo, isActive);

-- Tonton: idUser, idVideo, likeDislike
CREATE NONCLUSTERED INDEX IX_Tonton_idUser_idVideo_likeDislike
ON Tonton(idUser, idVideo, likeDislike);

-- Subscribe: idChannel, idUser, isActive
CREATE NONCLUSTERED INDEX IX_Subscribe_idChannel_idUser_isActive
ON Subscribe(idChannel, idUser, isActive);

--Dummy data, insert jika perlu
-- Users
INSERT INTO Users (Username, Email, Pass, fotoProfil) VALUES
('guess', 'guess@email.com', 'gues123', 'Assets/NoProfile.jpg'),
('admin', 'admin@mail.com','admin123', 'Assets/NoProfile.jpg'),
('Wombat','wombat@mail.com', 'wombatganteng', 'Assets/Wombat.jpg'),
('Dodo','dodo@mail.com', '123456','Assets/Dodo.webp'),
('Kapi','kapi@mail.com', '24446666','Assets/kapibara.jpg'),
('Kipikapi','kipi.kapi@mail.com', '3456', 'Assets/kapibara.jpg'),
('Kopiki','kopiki@mail.com', '4789', 'Assets/kapibara.jpg'),
('Davin' , 'davin@mail.com', '111111', 'Assets/Dodo.webp'),
('Felix' , 'felix@mail.com', '222222', 'Assets/kapibara.jpg'),
('Philip' , 'philip@mail.com', '333333', 'Assets/Wombat.jpg');


-- Roles
INSERT INTO Roles (RoleName) VALUES 
('Owner'), ('Manager'), ('Editor'), ('Admin'), ('Subtitle Editor'), ('Viewer');

-- Channel
INSERT INTO Channel (namaChannel, deskripsi, fotoProfil, channelType) VALUES 
('Dodo', 'Dodo si petualang', 'Assets/Dodo.webp', 0),
('Wombat', 'Wom Batman', 'Assets/Wombat.jpg', 0),
('Kapibara', 'Kapibara Official', 'Assets/kapibara.jpg', 1);

-- Videos 
INSERT INTO Videos (title, path, description, thumbnail, idChannel) VALUES 
('Seoul', 'Assets/Seoul.mp4', 'Ini Kota Seoul', 'Assets/Seoul.png', 1),
('Paris', 'Assets/Paris.mp4', 'Ini Kota Seoul', 'Assets/Paris.png', 2),
('Seoul', 'Assets/Jogja.mp4', 'Ini Kota Jogja', 'Assets/Jogja.png', 3),
('Move', 'Assets/Move.mp4', 'MOVEE!', 'Assets/Move.png', 1),
('Run', 'Assets/Run.mp4', 'RUNN!', 'Assets/Run.png', 2),
('Birds', 'Assets/IklanSamsung.mp4', 'BIRDS', 'Assets/thumbnailSamsung.png', 3);

-- Komen
INSERT INTO Komen (idVideo, idUser, tanggal, komen, isActive)
VALUES 
(1, 3, GETDATE(), 'Hai aku Wombat', 1),
(2, 4, GETDATE(), 'Hai aku Dodo', 1),
(3, 5, GETDATE(), 'Hai aku Kapibara', 1);

--Admin
INSERT INTO [Admin] (idUser, idChannel, idRole, IsActive, CreatedAt) VALUES 
(4, 1, 1, 1, GETDATE()),
(3, 2, 1, 1, GETDATE()),
(5, 3, 1, 1, GETDATE()),
(6, 3, 2, 1, GETDATE()),
(7, 3, 5, 1, GETDATE());

SELECT * FROM Users
SELECT * FROM Channel
SELECT * FROM Komen
SELECT * FROM [Admin] 
SELECT * FROM Videos 
SELECT * FROM Tonton
SELECT * FROM Roles
SELECT * FROM Subscribe

