USE [master]
GO
/****** Object:  Database [BlockchainDB]    Script Date: 28/05/2025 13:47:19 ******/
CREATE DATABASE [BlockchainDB]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'BlockchainDB', FILENAME = N'/var/opt/mssql/data/BlockchainDB.mdf' , SIZE = 8192KB , MAXSIZE = UNLIMITED, FILEGROWTH = 65536KB )
 LOG ON 
( NAME = N'BlockchainDB_log', FILENAME = N'/var/opt/mssql/data/BlockchainDB_log.ldf' , SIZE = 73728KB , MAXSIZE = 2048GB , FILEGROWTH = 65536KB )
 WITH CATALOG_COLLATION = DATABASE_DEFAULT, LEDGER = OFF
GO
ALTER DATABASE [BlockchainDB] SET COMPATIBILITY_LEVEL = 160
GO
IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [BlockchainDB].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO
ALTER DATABASE [BlockchainDB] SET ANSI_NULL_DEFAULT OFF 
GO
ALTER DATABASE [BlockchainDB] SET ANSI_NULLS OFF 
GO
ALTER DATABASE [BlockchainDB] SET ANSI_PADDING OFF 
GO
ALTER DATABASE [BlockchainDB] SET ANSI_WARNINGS OFF 
GO
ALTER DATABASE [BlockchainDB] SET ARITHABORT OFF 
GO
ALTER DATABASE [BlockchainDB] SET AUTO_CLOSE OFF 
GO
ALTER DATABASE [BlockchainDB] SET AUTO_SHRINK OFF 
GO
ALTER DATABASE [BlockchainDB] SET AUTO_UPDATE_STATISTICS ON 
GO
ALTER DATABASE [BlockchainDB] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO
ALTER DATABASE [BlockchainDB] SET CURSOR_DEFAULT  GLOBAL 
GO
ALTER DATABASE [BlockchainDB] SET CONCAT_NULL_YIELDS_NULL OFF 
GO
ALTER DATABASE [BlockchainDB] SET NUMERIC_ROUNDABORT OFF 
GO
ALTER DATABASE [BlockchainDB] SET QUOTED_IDENTIFIER OFF 
GO
ALTER DATABASE [BlockchainDB] SET RECURSIVE_TRIGGERS OFF 
GO
ALTER DATABASE [BlockchainDB] SET  ENABLE_BROKER 
GO
ALTER DATABASE [BlockchainDB] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO
ALTER DATABASE [BlockchainDB] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO
ALTER DATABASE [BlockchainDB] SET TRUSTWORTHY OFF 
GO
ALTER DATABASE [BlockchainDB] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO
ALTER DATABASE [BlockchainDB] SET PARAMETERIZATION SIMPLE 
GO
ALTER DATABASE [BlockchainDB] SET READ_COMMITTED_SNAPSHOT OFF 
GO
ALTER DATABASE [BlockchainDB] SET HONOR_BROKER_PRIORITY OFF 
GO
ALTER DATABASE [BlockchainDB] SET RECOVERY FULL 
GO
ALTER DATABASE [BlockchainDB] SET  MULTI_USER 
GO
ALTER DATABASE [BlockchainDB] SET PAGE_VERIFY CHECKSUM  
GO
ALTER DATABASE [BlockchainDB] SET DB_CHAINING OFF 
GO
ALTER DATABASE [BlockchainDB] SET FILESTREAM( NON_TRANSACTED_ACCESS = OFF ) 
GO
ALTER DATABASE [BlockchainDB] SET TARGET_RECOVERY_TIME = 60 SECONDS 
GO
ALTER DATABASE [BlockchainDB] SET DELAYED_DURABILITY = DISABLED 
GO
ALTER DATABASE [BlockchainDB] SET ACCELERATED_DATABASE_RECOVERY = OFF  
GO
EXEC sys.sp_db_vardecimal_storage_format N'BlockchainDB', N'ON'
GO
ALTER DATABASE [BlockchainDB] SET QUERY_STORE = ON
GO
ALTER DATABASE [BlockchainDB] SET QUERY_STORE (OPERATION_MODE = READ_WRITE, CLEANUP_POLICY = (STALE_QUERY_THRESHOLD_DAYS = 30), DATA_FLUSH_INTERVAL_SECONDS = 900, INTERVAL_LENGTH_MINUTES = 60, MAX_STORAGE_SIZE_MB = 1000, QUERY_CAPTURE_MODE = AUTO, SIZE_BASED_CLEANUP_MODE = AUTO, MAX_PLANS_PER_QUERY = 200)
GO
USE [BlockchainDB]
GO
/****** Object:  UserDefinedFunction [dbo].[CalculateHash]    Script Date: 28/05/2025 13:47:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE   FUNCTION [dbo].[CalculateHash](@BlockID INT)
RETURNS NVARCHAR(32)
AS
BEGIN
    DECLARE @ConcatString NVARCHAR(MAX);
    DECLARE @Hash NVARCHAR(32);

    SELECT @ConcatString = COALESCE(@ConcatString, '') + 
        CAST(BlockID AS NVARCHAR) + 
        CAST(Timestamp AS NVARCHAR) + 
        ISNULL(PreviousHash, '')
    FROM Blocks
    WHERE BlockID = @BlockID;

    -- Using HASHBYTES (MD5)
    SET @Hash = CONVERT(NVARCHAR(32), HASHBYTES('MD5', @ConcatString), 2);

    RETURN @Hash;
END;
GO
/****** Object:  Table [dbo].[Blocks]    Script Date: 28/05/2025 13:47:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Blocks](
	[BlockID] [int] IDENTITY(1,1) NOT NULL,
	[Timestamp] [datetime] NULL,
	[PreviousHash] [nvarchar](32) NULL,
	[Hash] [nvarchar](32) NULL,
PRIMARY KEY CLUSTERED 
(
	[BlockID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[BlockTransactions]    Script Date: 28/05/2025 13:47:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[BlockTransactions](
	[BlockID] [int] NOT NULL,
	[TransactionID] [int] NOT NULL,
PRIMARY KEY CLUSTERED 
(
	[BlockID] ASC,
	[TransactionID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
ALTER TABLE [dbo].[Blocks] ADD  DEFAULT (getdate()) FOR [Timestamp]
GO
ALTER TABLE [dbo].[BlockTransactions]  WITH CHECK ADD FOREIGN KEY([BlockID])
REFERENCES [dbo].[Blocks] ([BlockID])
GO
/****** Object:  StoredProcedure [dbo].[AddBlock]    Script Date: 28/05/2025 13:47:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Add a block
CREATE PROCEDURE [dbo].[AddBlock]
    @PreviousHash NVARCHAR(32),
	@Hash NVARCHAR(32)
AS
BEGIN
	--DECLARE @PreviousHash NVARCHAR(32);
	--SET @PreviousHash = (SELECT TOP 1 Blocks.Hash FROM Blocks ORDER BY BlockID DESC);

    INSERT INTO Blocks (PreviousHash)
    VALUES (@PreviousHash);
	
    DECLARE @BlockID INT = SCOPE_IDENTITY();
    --DECLARE @Hash NVARCHAR(32) = dbo.CalculateHash(@BlockID);

    UPDATE Blocks
    SET Hash = @Hash
    WHERE BlockID = @BlockID;
END;
GO
/****** Object:  StoredProcedure [dbo].[AddTransaction]    Script Date: 28/05/2025 13:47:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Add a transaction and link it to a block
CREATE PROCEDURE [dbo].[AddTransaction]
    @Sender NVARCHAR(50),
    @Receiver NVARCHAR(50),
    @Amount DECIMAL(18, 2),
    @BlockID INT
AS
BEGIN
    INSERT INTO PP_DDBB.dbo.Transactions (SenderID, ReceiverID, Amount)
    VALUES (@Sender, @Receiver, @Amount);

    DECLARE @TransactionID INT = SCOPE_IDENTITY();
	--DECLARE @BlockID INT;
	--SET @BlockID = (SELECT TOP 1 Blocks.BlockID FROM Blocks ORDER BY BlockID DESC);

    INSERT INTO BlockTransactions (BlockID, TransactionID)
    VALUES (@BlockID, @TransactionID);
END;
GO
/****** Object:  StoredProcedure [dbo].[BlockExists]    Script Date: 28/05/2025 13:47:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE PROCEDURE [dbo].[BlockExists]
    @BlockID INT
AS
BEGIN
	DECLARE @Exists BIT;
    IF EXISTS (SELECT 1 FROM Blocks WHERE BlockID = @BlockID)
        SET @Exists = 1;
    ELSE
        SET @Exists = 0;
	SELECT @Exists;
END;
GO
/****** Object:  StoredProcedure [dbo].[GetBlockChainXML]    Script Date: 28/05/2025 13:47:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

-- Get all transactions and blocks in XML format
CREATE PROCEDURE [dbo].[GetBlockChainXML]
AS
BEGIN
    SET NOCOUNT ON;
    
    SELECT 
        (SELECT 
            B.BlockID, 
			B.Timestamp,
            B.PreviousHash, 
            B.Hash,
            (SELECT 
                T.TransactionID, 
                T.Sender, 
                T.Receiver, 
                T.Amount
             FROM PP_DDBB.dbo.Transactions T
             INNER JOIN BlockTransactions BT ON T.TransactionID = BT.TransactionID
             WHERE BT.BlockID = B.BlockID
             FOR XML PATH('Transaction'), TYPE)
         FROM Blocks B
         FOR XML PATH('Block'), ROOT('Blockchain')) AS BlockchainXML;
END;
GO
/****** Object:  StoredProcedure [dbo].[sp_blockchain_add_block]    Script Date: 28/05/2025 13:47:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE PROCEDURE [dbo].[sp_blockchain_add_block]
	@TransactionID INT,
    @SenderID INT,
    @ReceiverID INT,
    @Amount DECIMAL(18, 2),
    @ret INT OUTPUT
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @BlockID INT;
    DECLARE @PreviousHash NVARCHAR(32);
    DECLARE @TransactionJSON NVARCHAR(MAX);
    DECLARE @NewHash NVARCHAR(32);
    DECLARE @Timestamp DATETIME = CONVERT([datetime],switchoffset(sysdatetimeoffset(),'+02:00'));

    BEGIN TRAN;

    BEGIN TRY

        -- Paso 1: Verificar si es el primer bloque (bloque génesis)
        IF NOT EXISTS (SELECT 1 FROM Blocks)
        BEGIN
            INSERT INTO Blocks (Timestamp, PreviousHash, Hash) VALUES (@Timestamp, NULL, 'GENESIS_HASH');
        END

        -- Paso 2: Obtener último hash
        SELECT TOP 1 @PreviousHash = Hash FROM Blocks ORDER BY BlockID DESC;

        -- Paso 3: Insertar nuevo bloque con PreviousHash
        INSERT INTO Blocks (PreviousHash) VALUES (@PreviousHash);

        SET @BlockID = SCOPE_IDENTITY();

        -- Paso 4: Armar JSON de transacción
        SET @TransactionJSON = (
            SELECT @SenderID AS Sender_ID, @ReceiverID AS Receiver_ID, @Amount AS Amount
            FOR JSON PATH, WITHOUT_ARRAY_WRAPPER
        );

        -- Paso 5: Calcular hash del bloque
        SET @NewHash = CONVERT(NVARCHAR(32), HASHBYTES('MD5', 
            CAST(@BlockID AS NVARCHAR) + 
            CONVERT(NVARCHAR, @Timestamp, 121) + 
            @TransactionJSON + 
            ISNULL(@PreviousHash, '')), 2);

        -- Paso 6: Actualizar el bloque
        UPDATE Blocks
        SET Timestamp = @Timestamp, Hash = @NewHash
        WHERE BlockID = @BlockID;
		
		IF NOT EXISTS (SELECT 1 FROM PP_DDBB.dbo.Transactions WHERE TransactionID = @TransactionID)
		BEGIN
			RAISERROR('ERROR: El TransactionID no existe en PP_DDBB.dbo.Transactions', 16, 1);
			ROLLBACK;
			SET @ret = -1;
			RETURN;
		END

        -- Vincular transacción al bloque
        INSERT INTO BlockTransactions (BlockID, TransactionID)
        VALUES (@BlockID, @TransactionID);

		COMMIT;

		SET @ret = 0;

    END TRY
	BEGIN CATCH
		ROLLBACK;

		-- Mostrar mensaje de error
		DECLARE @ErrMsg NVARCHAR(MAX) = ERROR_MESSAGE();
		RAISERROR('sp_blockchain_add_block ERROR: %s', 16, 1, @ErrMsg);

		SET @ret = -1;
	END CATCH
	END;
GO
/****** Object:  StoredProcedure [dbo].[sp_xml_error_messageINCOMPLETO]    Script Date: 28/05/2025 13:47:19 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE PROCEDURE [dbo].[sp_xml_error_messageINCOMPLETO]
    @RETURN INT,
    @XmlResponse XML OUTPUT,
	@Action NVARCHAR (32)
AS
BEGIN
    DECLARE @ERROR_CODE INT;
    SET @ERROR_CODE = @RETURN;

    DECLARE @ERROR_MESSAGE NVARCHAR(200);
    DECLARE @CURRENT_TIME DATETIME = GETDATE();
    DECLARE @SERVER_ID NVARCHAR(50) = @@SERVERNAME;
    DECLARE @EXECUTION_TIME NVARCHAR(50) = CAST(DATEDIFF(MILLISECOND, @CURRENT_TIME, GETDATE()) AS NVARCHAR(50)) + ' ms';
    DECLARE @URL NVARCHAR(100) = 'www.ws.mybizum.com';
    DECLARE @METHOD_NAME NVARCHAR(50) = @Action;

    -- Recupera el mensaje de error de la tabla USER_ERRORS
    SELECT @ERROR_MESSAGE = ERROR_MESSAGE
    FROM PP_DDBB.dbo.USER_ERRORS
    WHERE ERROR_CODE = @ERROR_CODE;

    -- Construcción del XML según el modelo proporcionado
    SET @XmlResponse = (
        SELECT
            -- Sección <head>
            (
                SELECT
                    @SERVER_ID AS 'server_id',
                    CONVERT(NVARCHAR(20), @CURRENT_TIME, 120) AS 'server_time',
                    @EXECUTION_TIME AS 'execution_time',
                    @URL AS 'url',
                    (
                        SELECT
                            @METHOD_NAME AS 'name',
                            (
                                SELECT
                                    'RETURN' AS 'name',
                                    @ERROR_CODE AS 'value'
                                FOR XML PATH('parameter'), TYPE
                            ) AS 'parameters'
                        FOR XML PATH('webmethod'), TYPE
                    ),
                    (
                        -- Sección <errors> (si hay error o no)
                        SELECT
                            @ERROR_CODE AS 'num_error',
                            @ERROR_MESSAGE AS 'message_error',
                            CASE
                                WHEN @ERROR_CODE = 0 THEN 'INFO'
                                ELSE 'ERROR'
                            END AS 'severity',
                            @ERROR_MESSAGE AS 'user_message'
                        FOR XML PATH('error'), TYPE
                    ) AS 'errors'
                FOR XML PATH('head'), TYPE
            ),
            -- Sección <body>
            (
                SELECT
                    CASE
                        WHEN @ERROR_CODE = 0 THEN 'Operation completed successfully'
                        ELSE 'Operation failed'
                    END AS 'response_data'
                FOR XML PATH('body'), TYPE
            )
        FOR XML PATH('ws_response'), TYPE
    );

END
GO
USE [master]
GO
ALTER DATABASE [BlockchainDB] SET  READ_WRITE 
GO